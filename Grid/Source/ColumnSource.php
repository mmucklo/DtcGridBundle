<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Util\CamelCase;
use Dtc\GridBundle\Util\ColumnUtil;

class ColumnSource
{
    /** @var string|null */
    private $cacheDir;

    /** @var bool */
    private $debug = false;

    public function __construct($cacheDir, $debug)
    {
        $this->debug = $debug;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $classMetadata
     *
     * @return mixed|null
     */
    public static function getIdColumn($classMetadata)
    {
        $identifier = $classMetadata->getIdentifier();

        return isset($identifier[0]) ? $identifier[0] : null;
    }

    /**
     * @return ColumnSourceInfo|null
     *
     * @throws \Exception
     */
    public function getColumnSourceInfo($objectManager, $objectName, $allowReflection, ?Reader $reader = null)
    {
        $metadataFactory = $objectManager->getMetadataFactory();
        $classMetadata = $metadataFactory->getMetadataFor($objectName);
        $reflectionClass = $classMetadata->getReflectionClass();
        $name = $reflectionClass->getName();
        $cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, $name);

        // 1. Fresh cache (timestamp-checked in debug, trusted in production).
        $cached = $this->getCachedColumnInfo($cacheFilename, $classMetadata);
        if (null !== $cached) {
            return $this->toColumnSourceInfo(ColumnUtil::instantiateColumnInfo($cached), $classMetadata);
        }

        // 2. Build from a Grid marker, composing the configuration sources
        //    (attributes take precedence over annotations). See
        //    resolveGridConfig() for the precedence/merge rules.
        $config = $this->resolveGridConfig($reflectionClass, self::configSources($reader));
        if (null !== $config) {
            $built = $this->buildColumnInfoFromGrid($reflectionClass, $config['grid'], $config['columns'], $classMetadata, $allowReflection);
            ColumnUtil::populateCacheFile($cacheFilename, $built);

            return $this->toColumnSourceInfo(ColumnUtil::instantiateColumnInfo($built), $classMetadata);
        }

        // 3. No Grid marker: a timestamp-stale compile-time cache (dtc_grid
        //    YAML grids, written at container build with no request-time
        //    regeneration path) is still served. A stale *runtime* cache is
        //    not resurrected, so removing a Grid marker takes effect.
        $staleCompile = $this->getCachedColumnInfo($cacheFilename, $classMetadata, true);
        if (null !== $staleCompile) {
            return $this->toColumnSourceInfo(ColumnUtil::instantiateColumnInfo($staleCompile), $classMetadata);
        }

        // 4. Reflection columns.
        if ($allowReflection) {
            return $this->toColumnSourceInfo(['columns' => self::getReflectionColumns($classMetadata), 'sort' => []], $classMetadata);
        }

        return null;
    }

    /**
     * @param array                                                            $columnInfo    ['columns' => GridColumn[], 'sort' => array]
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $classMetadata
     *
     * @return ColumnSourceInfo
     */
    private function toColumnSourceInfo(array $columnInfo, $classMetadata)
    {
        $columnSourceInfo = new ColumnSourceInfo();
        $columnSourceInfo->columns = $columnInfo['columns'];
        $columnSourceInfo->sort = isset($columnInfo['sort']) ? $columnInfo['sort'] : [];
        $columnSourceInfo->idColumn = self::getIdColumn($classMetadata);

        return $columnSourceInfo;
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $classMetadata
     * @param bool                                                             $staleCompileOnly When true, ignore timestamps but only return a cache whose source is 'compile'
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getCachedColumnInfo($cacheFilename, $classMetadata, $staleCompileOnly = false)
    {
        if (!is_file($cacheFilename) || !is_readable($cacheFilename)) {
            return null;
        }
        if (!$staleCompileOnly && !$this->shouldIncludeColumnCache($classMetadata, $cacheFilename)) {
            return null;
        }

        $columnInfo = include $cacheFilename;
        // Treat anything that isn't a current-format spec as a miss so it is
        // rebuilt rather than fataling: this covers a corrupt/partial file, a
        // pre-8.0 `return false` cache, and any cache predating the 'source'
        // provenance key.
        if (!is_array($columnInfo) || !isset($columnInfo['columns'], $columnInfo['sort'], $columnInfo['source'])) {
            return null;
        }
        // An empty column set is a miss, not a usable grid: fall through to the
        // readers/reflection rather than silently rendering a zero-column grid.
        if (!$columnInfo['columns']) {
            return null;
        }
        // The stale fallback only resurrects compile-time (YAML) caches; a
        // stale runtime cache must not outlive the config that produced it.
        if ($staleCompileOnly && 'compile' !== $columnInfo['source']) {
            return null;
        }
        if ($columnInfo['sort']) {
            self::validateSortList($columnInfo['sort'], $columnInfo['columns']);
        }

        return $columnInfo;
    }

    /**
     * Whether the cached column info is still current. In production any
     * readable cache is trusted; in debug the entity file timestamp is
     * re-checked so edits invalidate the cache regardless of how the grid
     * is configured.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return bool
     */
    private function shouldIncludeColumnCache($metadata, $columnCacheFilename)
    {
        if (!$this->debug) {
            return true;
        }

        return self::checkTimestamps($metadata, $columnCacheFilename);
    }

    /**
     * Check timestamps of the file pointed to by the class metadata, and the columnCacheFilename and see if any
     * are newer (meaning we .
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return bool
     */
    public static function checkTimestamps($metadata, $columnCacheFilename)
    {
        $reflectionClass = $metadata->getReflectionClass();
        $filename = $reflectionClass->getFileName();
        if ($filename && is_file($filename)) {
            $mtime = filemtime($filename);
            if (($currentfileMtime = filemtime(__FILE__)) > $mtime) {
                $mtime = $currentfileMtime;
            }
            $mtimeAnnotation = file_exists($columnCacheFilename) ? filemtime($columnCacheFilename) : null;
            if ($mtime && $mtimeAnnotation && $mtime <= $mtimeAnnotation) {
                return true;
            }
        }

        return false;
    }

    /**
     * The configuration sources to consult, highest precedence first: PHP 8
     * attributes (when available) then Doctrine annotations (when a reader is
     * injected).
     *
     * @return Config\GridConfigSourceInterface[]
     */
    private static function configSources(?Reader $reader)
    {
        $sources = [];
        if (\PHP_VERSION_ID >= 80000) {
            $sources[] = new Config\AttributeConfigSource();
        }
        if ($reader) {
            $sources[] = new Config\AnnotationConfigSource($reader);
        }

        return $sources;
    }

    /**
     * Compose the configuration sources into a single grid config:
     *  - the Grid marker comes from the highest-precedence source that has one;
     *  - class-level actions/sort are filled from the highest-precedence source
     *    that declares them separately, unless the marker already carries them;
     *  - columns are merged per property in declaration order, the
     *    highest-precedence source with a column for that property winning.
     * This keeps any partial migration (mixed attributes/annotations) working
     * without losing columns, actions or sort.
     *
     * @param Config\GridConfigSourceInterface[] $sources highest precedence first
     *
     * @return array{grid: Grid, columns: array<string, Column>}|null null when no source has a Grid marker
     */
    private function resolveGridConfig(\ReflectionClass $reflectionClass, array $sources)
    {
        $grid = null;
        foreach ($sources as $source) {
            if (null !== ($grid = $source->getGrid($reflectionClass))) {
                break;
            }
        }
        if (null === $grid) {
            return null;
        }

        if (null === $grid->actions) {
            foreach ($sources as $source) {
                $actions = $source->getActions($reflectionClass);
                if ($actions) {
                    $grid->actions = $actions;
                    break;
                }
            }
        }
        if (null === $grid->sort && null === $grid->sortMulti) {
            foreach ($sources as $source) {
                $sorts = $source->getSorts($reflectionClass);
                if (1 === count($sorts)) {
                    $grid->sort = $sorts[0];
                    break;
                }
                if (count($sorts) > 1) {
                    $grid->sortMulti = $sorts;
                    break;
                }
            }
        }

        $columns = [];
        foreach ($reflectionClass->getProperties() as $property) {
            foreach ($sources as $source) {
                $column = $source->getColumn($property);
                if (null !== $column) {
                    $columns[$property->getName()] = $column;
                    break;
                }
            }
        }

        return ['grid' => $grid, 'columns' => $columns];
    }

    /**
     * Shared logic: convert Grid + Column annotations/attributes into the column info array.
     *
     * @param array<string, Column> $columnAnnotations keyed by property name
     *
     * @return array ['columns' => array, 'sort' => array]
     */
    private function buildColumnInfoFromGrid(\ReflectionClass $reflectionClass, Grid $gridAnnotation, array $columnAnnotations, $metadata, $allowReflection)
    {
        $actions = $gridAnnotation->actions;
        $sort = $gridAnnotation->sort;
        $sortMulti = $gridAnnotation->sortMulti;

        $gridColumns = [];
        foreach ($columnAnnotations as $name => $annotation) {
            $label = $annotation->label ?: CamelCase::fromCamelCase($name);
            $gridColumns[$name] = ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => [$name, $label]];
            $gridColumns[$name]['arguments'][] = isset($annotation->formatter) ? $annotation->formatter : null;
            if ($annotation->sortable) {
                $gridColumns[$name]['arguments'][] = ['sortable' => true];
            } else {
                $gridColumns[$name]['arguments'][] = [];
            }
            $gridColumns[$name]['arguments'][] = $annotation->searchable;
            $gridColumns[$name]['arguments'][] = $annotation->order;
        }

        if (!$gridColumns && $allowReflection) {
            $gridColumnList = self::getReflectionColumns($metadata);
            /** @var GridColumn $gridColumn */
            foreach ($gridColumnList as $field => $gridColumn) {
                $gridColumns[$field] = ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => [$field, $gridColumn->getLabel(), null, ['sortable' => true], true, null]];
            }
        }

        // A Grid marker with no data columns (and reflection unavailable) is a
        // configuration error — checked here, before the action column is added,
        // so an actions-only grid can't mask it.
        if (!$gridColumns) {
            throw new \InvalidArgumentException($reflectionClass->getName().' has a Grid annotation or attribute but no Column definitions, and reflection-based columns are not available for it');
        }

        // Truthy, not isset(): an explicit empty actions array must not build
        // a stray, empty action column.
        if ($actions) {
            $field = '\$-action';
            $actionArgs = [$field];
            $actionDefs = [];
            /* @var Action $action */
            foreach ($actions as $action) {
                $actionDef = ['label' => $action->label, 'route' => $action->route, 'button_class' => $action->buttonClass, 'onclick' => $action->onclick];
                if ($action instanceof ShowAction) {
                    $actionDef['action'] = 'show';
                } elseif ($action instanceof DeleteAction) {
                    $actionDef['action'] = 'delete';
                } else {
                    $actionDef['action'] = 'custom';
                }
                $actionDefs[] = $actionDef;
            }
            $actionArgs[] = $actionDefs;

            $gridColumns[$field] = ['class' => '\Dtc\GridBundle\Grid\Column\ActionGridColumn',
                'arguments' => $actionArgs, ];
        }

        $this->sortGridColumns($gridColumns);

        if ($sort) {
            if ($sortMulti) {
                throw new \InvalidArgumentException($reflectionClass->getName().' - '."Can't have sort and sortMulti defined on Grid annotation");
            }
            $sortMulti = [$sort];
        }

        $sortList = [];
        if ($sortMulti) {
            try {
                foreach ($sortMulti as $sortDef) {
                    $sortInfo = self::extractSortInfo($sortDef);
                    self::validateSortInfo($sortInfo, $gridColumns);
                    if (isset($sortInfo['column'])) {
                        $sortList[$sortInfo['column']] = $sortInfo['direction'];
                    }
                }
            } catch (\InvalidArgumentException $exception) {
                throw new \InvalidArgumentException($reflectionClass->getName().' - '.$exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        return ['columns' => $gridColumns, 'sort' => $sortList];
    }

    /**
     * Validate a cached sort list (column => direction map) against the cached
     * columns, reusing the build-time single-entry validator.
     *
     * @throws \InvalidArgumentException
     */
    private static function validateSortList(array $sortList, array $gridColumns)
    {
        foreach ($sortList as $column => $direction) {
            self::validateSortInfo(['column' => $column, 'direction' => $direction], $gridColumns);
        }
    }

    /**
     * Validate a single sort definition (['column' => ..., 'direction' => ...])
     * against the column specs at build time.
     *
     * @throws \InvalidArgumentException
     */
    private static function validateSortInfo(array $sortInfo, array $gridColumns)
    {
        if (isset($sortInfo['direction'])) {
            switch ($sortInfo['direction']) {
                case 'ASC':
                case 'DESC':
                    break;
                default:
                    throw new \InvalidArgumentException("Grid's sort annotation direction '{$sortInfo['direction']}' is invalid");
            }
        }

        if (isset($sortInfo['column'])) {
            $column = $sortInfo['column'];

            if (!isset($sortInfo['direction'])) {
                throw new \InvalidArgumentException("Grid's sort annotation column '$column' specified but a sort direction was not");
            }
            if (isset($gridColumns[$column])) {
                return;
            }
            throw new \InvalidArgumentException("Grid's sort annotation column '$column' not in list of columns (".implode(', ', array_keys($gridColumns)).')');
        }
    }

    /**
     * @param Sort|null $sortAnnotation
     *
     * @return array
     */
    private static function extractSortInfo($sortAnnotation)
    {
        $sortInfo = ['direction' => null, 'column' => null];
        if ($sortAnnotation) {
            $direction = $sortAnnotation->direction;
            $sortInfo['direction'] = $direction;
            $column = $sortAnnotation->column;
            $sortInfo['column'] = $column;
        }

        return $sortInfo;
    }

    private function sortGridColumns(array &$columnDefs)
    {
        $unordered = [];
        $ordered = [];
        foreach ($columnDefs as $name => $columnDef) {
            $columnParts = $columnDef['arguments'];
            if (!isset($columnParts[5]) || null === $columnParts[5]) {
                $unordered[$name] = $columnDef;
                continue;
            }
            $ordered[$name] = $columnDef;
        }

        if (empty($ordered)) {
            return;
        }

        uasort($ordered, function ($columnDef1, $columnDef2) {
            $columnParts1 = $columnDef1['arguments'];
            $columnParts2 = $columnDef2['arguments'];
            $order1 = $columnParts1[5];
            $order2 = $columnParts2[5];

            return $order1 <=> $order2;
        });

        if ($unordered) {
            foreach ($unordered as $name => $columnDef) {
                $ordered[$name] = $columnDef;
            }
        }
        $columnDefs = $ordered;
    }

    /**
     * Generate Columns based on document's Metadata.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     */
    private static function getReflectionColumns($metadata)
    {
        $fields = $metadata->getFieldNames();
        $identifier = $metadata->getIdentifier();
        $identifier = isset($identifier[0]) ? $identifier[0] : null;

        if (!method_exists($metadata, 'getFieldMapping')) {
            return [];
        }

        $columns = [];
        foreach ($fields as $field) {
            $mapping = $metadata->getFieldMapping($field);
            if (isset($mapping['options']) && isset($mapping['options']['label'])) {
                $label = $mapping['options']['label'];
            } else {
                $label = CamelCase::fromCamelCase($field);
            }

            if ($identifier === $field) {
                if (isset($mapping['strategy']) && 'auto' == $mapping['strategy']) {
                    continue;
                }
            }
            $columns[$field] = new GridColumn($field, $label);
        }

        return $columns;
    }
}
