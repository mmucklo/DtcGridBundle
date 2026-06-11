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

        $columnInfo = $this->getCachedColumnInfo($cacheFilename, $classMetadata);

        // Attributes are consulted before annotations: when a class carries
        // both, the attributes win. Either reader falls back to the other
        // source's column definitions when it finds a Grid marker but no
        // columns of its own kind, so a half-migrated class keeps working.
        $gridConfigured = false;
        if (!$columnInfo) {
            $built = $this->readGridAttributes($classMetadata, $allowReflection, $reader);
            if (null === $built && $reader) {
                $built = $this->readGridAnnotations($reader, $classMetadata, $allowReflection);
            }
            if (null !== $built) {
                $gridConfigured = true;
                if ($built['columns']) {
                    ColumnUtil::populateCacheFile($cacheFilename, $built);
                    $columnInfo = self::instantiateColumnInfo($built);
                }
            }
        }

        // A stale-but-valid cache beats nothing: column caches written at
        // container compile time from dtc_grid YAML files have no
        // request-time regeneration path, so when the entity file is newer
        // than the cache but carries no Grid marker, serve the cached config
        // rather than degrading to reflection columns (it refreshes on the
        // next container rebuild).
        if (!$columnInfo && !$gridConfigured) {
            $columnInfo = $this->getCachedColumnInfo($cacheFilename, $classMetadata, true);
        }

        if (!$columnInfo && !$gridConfigured && $allowReflection) {
            $columns = self::getReflectionColumns($classMetadata);
            $columnInfo = ['columns' => $columns, 'sort' => []];
        }

        if (!$columnInfo) {
            if ($gridConfigured) {
                throw new \InvalidArgumentException($name.' has a Grid annotation or attribute but no Column definitions, and reflection-based columns are not available for it');
            }

            return null;
        }

        $columnSourceInfo = new ColumnSourceInfo();
        $columnSourceInfo->columns = $columnInfo['columns'];
        $columnSourceInfo->sort = $columnInfo['sort'];
        $columnSourceInfo->idColumn = self::getIdColumn($classMetadata);

        return $columnSourceInfo;
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $classMetadata
     * @param bool                                                             $ignoreTimestamps Serve the cache even if the entity file is newer
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getCachedColumnInfo($cacheFilename, $classMetadata, $ignoreTimestamps = false)
    {
        if (!is_file($cacheFilename) || !is_readable($cacheFilename)) {
            return null;
        }
        if (!$ignoreTimestamps && !$this->shouldIncludeColumnCache($classMetadata, $cacheFilename)) {
            return null;
        }

        $columnInfo = include $cacheFilename;
        if (!isset($columnInfo['columns'])) {
            throw new \Exception("Bad column cache, missing columns: {$cacheFilename}");
        }
        if (!isset($columnInfo['sort'])) {
            throw new \Exception("Bad column cache, missing sort: {$cacheFilename}");
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
     * Read grid configuration from PHP 8 attributes (ReflectionAttribute).
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return array|null column info, or null when the class has no #[Grid]
     *
     * @throws \Exception
     */
    private function readGridAttributes($metadata, $allowReflection, ?Reader $reader = null)
    {
        if (\PHP_VERSION_ID < 80000) {
            return null;
        }

        $reflectionClass = $metadata->getReflectionClass();
        $gridAttrs = $reflectionClass->getAttributes(Grid::class);
        if (empty($gridAttrs)) {
            return null;
        }

        $gridAnnotation = $gridAttrs[0]->newInstance();

        // On PHP 8.0 attribute arguments cannot contain `new`, so actions
        // and sort can also be declared as separate class-level attributes.
        // (On PHP 8.1+ nesting them inside #[Grid] works directly.)
        if (null === $gridAnnotation->actions) {
            $actionAttrs = $reflectionClass->getAttributes(Action::class, \ReflectionAttribute::IS_INSTANCEOF);
            if ($actionAttrs) {
                $gridAnnotation->actions = array_map(function ($attr) {
                    return $attr->newInstance();
                }, $actionAttrs);
            }
        }
        if (null === $gridAnnotation->sort && null === $gridAnnotation->sortMulti) {
            $sortAttrs = $reflectionClass->getAttributes(Sort::class);
            if (1 === count($sortAttrs)) {
                $gridAnnotation->sort = $sortAttrs[0]->newInstance();
            } elseif (count($sortAttrs) > 1) {
                $gridAnnotation->sortMulti = array_map(function ($attr) {
                    return $attr->newInstance();
                }, $sortAttrs);
            }
        }

        $columnAnnotations = self::collectAttributeColumns($reflectionClass);
        if (!$columnAnnotations && $reader) {
            // Half-migrated class: #[Grid] at class level but columns still
            // declared as @Column docblock annotations — honor them rather
            // than silently dropping the column configuration.
            $columnAnnotations = self::collectAnnotationColumns($reader, $reflectionClass);
        }

        return $this->buildColumnInfoFromGrid($reflectionClass, $gridAnnotation, $columnAnnotations, $metadata, $allowReflection);
    }

    /**
     * Read grid configuration from Doctrine annotations.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return array|null column info, or null when the class has no @Grid
     *
     * @throws \Exception
     */
    private function readGridAnnotations(Reader $reader, $metadata, $allowReflection)
    {
        $reflectionClass = $metadata->getReflectionClass();

        /** @var Grid $gridAnnotation */
        if (!($gridAnnotation = $reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid'))) {
            return null;
        }

        $columnAnnotations = self::collectAnnotationColumns($reader, $reflectionClass);
        if (!$columnAnnotations && \PHP_VERSION_ID >= 80000) {
            // Half-migrated class, the other direction: @Grid kept at class
            // level while the properties already use #[Column] attributes.
            $columnAnnotations = self::collectAttributeColumns($reflectionClass);
        }

        return $this->buildColumnInfoFromGrid($reflectionClass, $gridAnnotation, $columnAnnotations, $metadata, $allowReflection);
    }

    /**
     * @return array<string, Column> keyed by property name
     */
    private static function collectAttributeColumns(\ReflectionClass $reflectionClass)
    {
        $columns = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $colAttrs = $property->getAttributes(Column::class);
            if (!empty($colAttrs)) {
                $columns[$property->getName()] = $colAttrs[0]->newInstance();
            }
        }

        return $columns;
    }

    /**
     * @return array<string, Column> keyed by property name
     */
    private static function collectAnnotationColumns(Reader $reader, \ReflectionClass $reflectionClass)
    {
        $columns = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $annotation = $reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\Column');
            if ($annotation) {
                $columns[$property->getName()] = $annotation;
            }
        }

        return $columns;
    }

    /**
     * Materialize the cache-file column specs into GridColumn objects.
     *
     * @return array ['columns' => array<GridColumn>, 'sort' => array]
     */
    private static function instantiateColumnInfo(array $columnInfo)
    {
        $columns = [];
        foreach ($columnInfo['columns'] as $field => $info) {
            $class = $info['class'];
            $columns[$field] = new $class(...$info['arguments']);
        }

        return ['columns' => $columns, 'sort' => $columnInfo['sort']];
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

        if (isset($actions)) {
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
        if ($sortMulti && $gridColumns) {
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
     * Validate the cached sort list (column => direction map) against the
     * cached columns.
     *
     * @throws \InvalidArgumentException
     */
    private static function validateSortList(array $sortList, array $gridColumns)
    {
        foreach ($sortList as $column => $direction) {
            if ('ASC' !== $direction && 'DESC' !== $direction) {
                throw new \InvalidArgumentException("Grid sort direction '{$direction}' for column '{$column}' is invalid");
            }
            if (!isset($gridColumns[$column])) {
                throw new \InvalidArgumentException("Grid sort column '{$column}' not in list of columns (".implode(', ', array_keys($gridColumns)).')');
            }
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
