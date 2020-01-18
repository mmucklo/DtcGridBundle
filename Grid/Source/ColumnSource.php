<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Util\CamelCase;
use Dtc\GridBundle\Util\ColumnUtil;
use Exception;
use InvalidArgumentException;

class ColumnSource
{
    /** @var Reader|null */
    protected $reader;

    /** @var string|null */
    protected $cacheDir;

    /** @var bool */
    protected $debug = false;

    /** @var string */
    protected $cacheFilename;

    /** @var array|null */
    protected $cachedColumns;

    protected $objectManager;

    protected $classMetadata;

    protected $objectName;

    protected $columns;

    protected $idColumn;

    public function __construct(ObjectManager $objectManager, $objectName)
    {
        $this->objectManager = $objectManager;
        $this->objectName = $objectName;
    }

    /**
     * @var array|null
     */
    protected $cachedSort;

    public function setDebug($flag)
    {
        $this->debug = $flag;
    }

    /**
     * @param Reader $reader
     */
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string|null $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        if ($this->classMetadata) {
            return $this->classMetadata;
        }
        $metaFactory = $this->objectManager->getMetadataFactory();

        return $this->classMetadata = $metaFactory->getMetadataFor($this->objectName);
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function getColumns()
    {
        if (!$this->columns) {
            $this->autoDiscoverColumns();
        }

        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @throws Exception
     */
    public function autoDiscoverColumns()
    {
        $cachedColumns = $this->getCachedColumns();
        if ($cachedColumns) {
            $this->setColumns($cachedColumns);

            return;
        }

        $this->setColumns($this->getReflectionColumns());
    }

    /**
     * @return array|null
     *
     * @throws Exception
     */
    public function getDefaultSort()
    {
        if (null !== $this->getCachedColumns()) {
            return $this->cachedSort;
        }

        return null;
    }

    /**
     * Populates the filename for the annotationCache.
     *
     * @return string
     *
     * @throws Exception
     */
    protected function populateCacheFilename()
    {
        if (isset($this->cacheFilename)) {
            return $this->cacheFilename;
        }
        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $name = $reflectionClass->getName();

        return $this->cacheFilename = ColumnUtil::createCacheFilename($this->cacheDir, $name);
    }

    /**
     * Attempt to discover columns using the GridColumn annotation.
     *
     * @throws Exception
     */
    public function getCachedColumns()
    {
        if (!isset($this->cacheDir)) {
            return null;
        }

        if (!isset($this->cacheFilename)) {
            $this->populateCacheFilename();
        }

        if (!$this->debug && null !== $this->cachedColumns) {
            return $this->cachedColumns ?: null;
        }

        // Try to include them from the cached file if exists.
        if (is_file($this->cacheFilename)) {
            $result = $this->tryIncludeColumnCache();
            if ($result) {
                return $this->cachedColumns;
            }
        }

        // Try annotations.
        if (isset($this->reader)) {
            $this->populateAndCacheAnnotationColumns();
        }

        return $this->cachedColumns ?: null;
    }

    /**
     * Cached annotation info from the file, if the mtime of the file has not changed (or if not in debug).
     * @return bool
     * @throws Exception
     */
    protected function tryIncludeColumnCache()
    {
        // In production, or if we're sure there's no annotaitons, just include the cache.
        if (!$this->debug || !isset($this->reader)) {
            $this->includeColumnCache();

            return true;
        }

        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $filename = $reflectionClass->getFileName();
        if ($filename && is_file($filename)) {
            $mtime = filemtime($filename);
            if (($currentfileMtime = filemtime(__FILE__)) > $mtime) {
                $mtime = $currentfileMtime;
            }
            $mtimeAnnotation = filemtime($this->cacheFilename);
            if ($mtime && $mtimeAnnotation && $mtime <= $mtimeAnnotation) {
                $this->includeColumnCache();

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $cacheDir
     * @param string $filename
     * @throws Exception
     */
    public static function cacheClassesFromFile($cacheDir, $filename) {
        $classes = ColumnUtil::extractClassesFromFile($filename);
        foreach ($classes as $class => $columnInfo) {
            $filename = ColumnUtil::createCacheFilename($cacheDir, $class);
            ColumnUtil::populateCacheFile($filename, $columnInfo);
        }
    }

    /**
     * Retrieves the cached annotations from the cache file.
     * @throws Exception
     */
    protected function includeColumnCache()
    {
        $columnInfo = include $this->cacheFilename;
        if (!isset($columnInfo['columns'])) {
            throw new Exception("Bad column cache, missing columns: {$this->cacheFilename}");
        }
        if (!isset($columnInfo['sort'])) {
            throw new Exception("Bad column cache, missing sort: {$this->cacheFilename}");
        }
        $this->cachedColumns = $columnInfo['columns'];
        $this->cachedSort = $columnInfo['sort'];
        if ($this->cachedSort) {
            $this->validateSortInfo($this->cachedSort, $this->cachedColumns);
        }
    }

    /**
     * Caches the annotation columns result into a file.
     * @throws Exception
     */
    protected function populateAndCacheAnnotationColumns()
    {
        $gridAnnotations = $this->readGridAnnotations();
        ColumnUtil::populateCacheFile($this->cacheFilename, $gridAnnotations);
        $this->includeColumnCache();
    }

    /**
     * Generates a list of property name and labels based on finding the GridColumn annotation.
     *
     * @return array Hash of grid annotation results: ['columns' => array, 'sort' => string]
     */
    protected function readGridAnnotations()
    {
        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $properties = $reflectionClass->getProperties();

        /** @var Grid $gridAnnotation */
        $sort = null;
        $sortMulti = null;
        if ($gridAnnotation = $this->reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid')) {
            $actions = $gridAnnotation->actions;
            $sort = $gridAnnotation->sort;
            $sortMulti = $gridAnnotation->sortMulti;
        }

        $gridColumns = [];
        foreach ($properties as $property) {
            /** @var Column $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\Column');
            if ($annotation) {
                $name = $property->getName();
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
        }

        // Fall back to default column list if list is not specified
        if (!$gridColumns) {
            $gridColumnList = $this->getReflectionColumns();
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
                $actionDef = ['label' => $action->label, 'route' => $action->route];
                if ($action instanceof ShowAction) {
                    $actionDef['action'] = 'show';
                }
                if ($action instanceof DeleteAction) {
                    $actionDef['action'] = 'delete';
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
                throw new InvalidArgumentException($reflectionClass->getName().' - '. "Can't have sort and sortMulti defined on Grid annotation");
            }
            $sortMulti = [$sort];
        }

        $sortList = [];
        try {
            foreach ($sortMulti as $sortDef) {
                $sortInfo = $this->extractSortInfo($sortDef);
                $this->validateSortInfo($sortInfo, $gridColumns);
                if (isset($sortInfo['column'])) {
                    $sortList[$sortInfo['column']] = $sortInfo['direction'];
                }
            }
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException($reflectionClass->getName().' - '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return ['columns' => $gridColumns, 'sort' => $sortList];
    }

    /**
     * @param array $sortInfo
     * @param array $gridColumns
     *
     * @throws InvalidArgumentException
     */
    protected function validateSortInfo(array $sortInfo, array $gridColumns)
    {
        if (isset($sortInfo['direction'])) {
            switch ($sortInfo['direction']) {
                case 'ASC':
                case 'DESC':
                    break;
                default:
                    throw new InvalidArgumentException("Grid's sort annotation direction '{$sortInfo['direction']}' is invalid");
            }
        }

        if (isset($sortInfo['column'])) {
            $column = $sortInfo['column'];

            if (!isset($sortInfo['direction'])) {
                throw new InvalidArgumentException("Grid's sort annotation column '$column' specified but a sort direction was not");
            }
            if (isset($gridColumns[$column])) {
                return;
            }
            throw new InvalidArgumentException("Grid's sort annotation column '$column' not in list of columns (".implode(', ', array_keys($gridColumns)).')');
        }
    }

    /**
     * @param Sort|null $sortAnnotation
     *
     * @return array
     */
    protected function extractSortInfo($sortAnnotation)
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

    protected function sortGridColumns(array &$columnDefs)
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

            return $order1 > $order2;
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
     */
    protected function getReflectionColumns()
    {
        $metadata = $this->getClassMetadata();
        $fields = $metadata->getFieldNames();
        $identifier = $metadata->getIdentifier();
        $identifier = isset($identifier[0]) ? $identifier[0] : null;

        if (!method_exists($metadata, 'getFieldMapping')) {
            return array();
        }

        $columns = array();
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

    /**
     * @return string|null
     */
    public function getIdColumn()
    {
        if (!isset($this->idColumn)) {
            $metadata = $this->getClassMetadata();
            $identifier = $metadata->getIdentifier();
            $this->idColumn = isset($identifier[0]) ? $identifier[0] : false;
        }

        return $this->idColumn ?: null;
    }

    public function hasIdColumn()
    {
        return $this->getIdColumn() ? true : false;
    }
}
