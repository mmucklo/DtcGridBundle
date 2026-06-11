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
     * @var array|null
     */
    private $cachedSort;

    public function setDebug($flag)
    {
        $this->debug = $flag;
    }

    /**
     * @param string|null $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
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
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $classMetadata
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function getCachedColumnInfo($cacheFilename, $classMetadata)
    {
        if ($this->shouldIncludeColumnCache($classMetadata, $cacheFilename)) {
            $columnInfo = include $cacheFilename;
            if (!isset($columnInfo['columns'])) {
                throw new \Exception("Bad column cache, missing columns: {$cacheFilename}");
            }
            if (!isset($columnInfo['sort'])) {
                throw new \Exception("Bad column cache, missing sort: {$cacheFilename}");
            }
            if ($columnInfo['sort']) {
                self::validateSortInfo($columnInfo['sort'], $columnInfo['columns']);
            }

            return $columnInfo;
        }

        return null;
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

        // Try to include them from the cached file if exists.
        $columnInfo = $this->getCachedColumnInfo($cacheFilename, $classMetadata);

        if (!$columnInfo) {
            $columnInfo = $this->readAndCacheGridAttributes($cacheFilename, $classMetadata, $allowReflection);
        }

        if (!$columnInfo && $reader) {
            $columnInfo = $this->readAndCacheGridAnnotations($cacheFilename, $reader, $classMetadata, $allowReflection);
        }

        if (!$columnInfo && $allowReflection) {
            $columns = self::getReflectionColumns($classMetadata);
            $columnInfo = ['columns' => $columns, 'sort' => []];
        }

        if (!$columnInfo) {
            return null;
        }

        $columnSourceInfo = new ColumnSourceInfo();
        $columnSourceInfo->columns = $columnInfo['columns'];
        $columnSourceInfo->sort = $columnInfo['sort'];
        $columnSourceInfo->idColumn = self::getIdColumn($classMetadata);

        return $columnSourceInfo;
    }

    /**
     * Cached annotation info from the file, if the mtime of the file has not changed (or if not in debug).
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function shouldIncludeColumnCache($metadata, $columnCacheFilename)
    {
        if (!is_file($columnCacheFilename) || !is_readable($columnCacheFilename)) {
            return false;
        }

        // In production, just trust the cache.
        if (!$this->debug) {
            return true;
        }

        // In debug, re-check timestamps so edits to the entity invalidate the
        // cache regardless of how the grid is configured. Previously this was
        // skipped whenever no annotation reader was present, which left PHP 8
        // attribute-only entities serving stale columns until a manual cache
        // clear (attributes can be read with no annotation_reader injected).
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
     * Generates a list of property name and labels based on finding the GridColumn annotation.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata|ClassMetadata $metadata
     *
     * @return array|null Hash of grid annotation results: ['columns' => array, 'sort' => string]
     *
     * @throws \Exception
     */
    private function readAndCacheGridAnnotations($cacheFilename, Reader $reader, $metadata, $allowReflection)
    {
        $reflectionClass = $metadata->getReflectionClass();
        $properties = $reflectionClass->getProperties();

        /** @var Grid $gridAnnotation */
        if (!($gridAnnotation = $reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid'))) {
            return null;
        }

        $columnAnnotations = [];
        foreach ($properties as $property) {
            $annotation = $reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\Column');
            if ($annotation) {
                $columnAnnotations[$property->getName()] = $annotation;
            }
        }

        $columnInfo = $this->buildColumnInfoFromGrid($reflectionClass, $gridAnnotation, $columnAnnotations, $metadata, $allowReflection);

        ColumnUtil::populateCacheFile($cacheFilename, $columnInfo);

        return $this->getCachedColumnInfo($cacheFilename, $metadata);
    }

    /**
     * Read grid configuration from PHP 8 attributes (ReflectionAttribute).
     *
     * @return array|null
     *
     * @throws \Exception
     */
    private function readAndCacheGridAttributes($cacheFilename, $metadata, $allowReflection)
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

        // Actions and sort can't be nested inside #[Grid] — PHP attribute
        // arguments must be constant expressions, so `new ShowAction()` /
        // `new Sort()` aren't allowed there. Read them as separate
        // class-level attributes instead.
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

        $columnAnnotations = [];
        foreach ($reflectionClass->getProperties() as $property) {
            $colAttrs = $property->getAttributes(Column::class);
            if (!empty($colAttrs)) {
                $columnAnnotations[$property->getName()] = $colAttrs[0]->newInstance();
            }
        }

        $columnInfo = $this->buildColumnInfoFromGrid($reflectionClass, $gridAnnotation, $columnAnnotations, $metadata, $allowReflection);

        ColumnUtil::populateCacheFile($cacheFilename, $columnInfo);

        return $this->getCachedColumnInfo($cacheFilename, $metadata);
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
