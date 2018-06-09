<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Dtc\GridBundle\Annotation\Action;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Util\CamelCaseTrait;

trait ColumnExtractionTrait
{
    use CamelCaseTrait;

    /** @var Reader|null */
    protected $reader;

    /** @var string|null */
    protected $cacheDir;

    /** @var bool */
    protected $debug = false;

    /** @var string */
    protected $annotationCacheFilename;

    /** @var array|null */
    protected $annotationColumns;

    /**
     * @var array|null
     */
    protected $annotationSort;

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
     * @return ClassMetadataInfo|ClassMetadataInfo
     */
    abstract public function getClassMetadata();

    public function autoDiscoverColumns()
    {
        $annotationColumns = $this->getAnnotationColumns();
        if ($annotationColumns) {
            $this->setColumns($annotationColumns);

            return;
        }

        $this->setColumns($this->getReflectionColumns());
    }

    /**
     * @return array|null
     */
    public function getDefaultSort()
    {
        if (null !== $this->getAnnotationColumns()) {
            return $this->annotationSort;
        }

        return null;
    }

    /**
     * Populates the filename for the annotationCache.
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function populateAnnotationCacheFilename()
    {
        if (isset($this->annotationCacheFilename)) {
            return $this->annotationCacheFilename;
        }
        $directory = $this->cacheDir.'/DtcGridBundle';
        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $name = $reflectionClass->getName();
        $namespace = $reflectionClass->getNamespaceName();
        $namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
        $namespaceDir = $directory.DIRECTORY_SEPARATOR.$namespace;

        $umask = decoct(umask());
        $umask = str_pad($umask, 4, '0', STR_PAD_LEFT);

        // Is there a better way to do this?
        $permissions = '0777';
        $permissions[1] = intval($permissions[1]) - intval($umask[1]);
        $permissions[2] = intval($permissions[2]) - intval($umask[2]);
        $permissions[3] = intval($permissions[3]) - intval($umask[3]);

        if (!is_dir($namespaceDir) && !mkdir($namespaceDir, octdec($permissions), true)) {
            throw new \Exception("Can't create: ".$namespaceDir);
        }

        $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        $this->annotationCacheFilename = $directory.DIRECTORY_SEPARATOR.$name.'.php';

        return $this->annotationCacheFilename;
    }

    /**
     * Attempt to discover columns using the GridColumn annotation.
     *
     * @throws \Exception
     */
    protected function getAnnotationColumns()
    {
        if (!isset($this->reader)) {
            return null;
        }

        if (!isset($this->cacheDir)) {
            return null;
        }

        if (!isset($this->annotationCacheFilename)) {
            $this->populateAnnotationCacheFilename();
        }

        if (!$this->debug && null !== $this->annotationColumns) {
            return $this->annotationColumns ?: null;
        }

        // Check mtime of class
        if (is_file($this->annotationCacheFilename)) {
            $result = $this->tryIncludeAnnotationCache();
            if ($result) {
                return $this->annotationColumns;
            }
        }

        // cache annotation
        $this->populateAndCacheAnnotationColumns();

        return $this->annotationColumns ?: null;
    }

    /**
     * Cached annotation info from the file, if the mtime of the file has not changed (or if not in debug).
     *
     * @return bool
     */
    protected function tryIncludeAnnotationCache()
    {
        if (!$this->debug) {
            $this->includeAnnotationCache();

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
            $mtimeAnnotation = filemtime($this->annotationCacheFilename);
            if ($mtime && $mtimeAnnotation && $mtime <= $mtimeAnnotation) {
                $this->includeAnnotationCache();

                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves the cached annotations from the cache file.
     */
    protected function includeAnnotationCache()
    {
        $annotationInfo = include $this->annotationCacheFilename;
        $this->annotationColumns = $annotationInfo['columns'];
        $this->annotationSort = $annotationInfo['sort'];
        if ($this->annotationSort) {
            $this->validateSortInfo($this->annotationSort, $this->annotationColumns);
        }
    }

    /**
     * Caches the annotation columns result into a file.
     */
    protected function populateAndCacheAnnotationColumns()
    {
        $gridAnnotations = $this->readGridAnnotations();
        $annotationColumns = $gridAnnotations['columns'];

        $sort = $gridAnnotations['sort'];
        if ($annotationColumns) {
            $output = "<?php\nreturn array('columns' => array(\n";
            foreach ($annotationColumns as $field => $info) {
                $class = $info['class'];
                $output .= "'$field' => new $class(";
                $first = true;
                foreach ($info['arguments'] as $argument) {
                    if ($first) {
                        $first = false;
                    } else {
                        $output .= ',';
                    }
                    $output .= var_export($argument, true);
                }
                $output .= '),';
            }
            $output .= "), 'sort' => array(";
            foreach ($sort as $key => $value) {
                $output .= "'$key'".' => ';
                if (null === $value) {
                    $output .= 'null,';
                } else {
                    $output .= "'$value',";
                }
            }
            $output .= "));\n";
        } else {
            $output = "<?php\nreturn false;\n";
        }
        file_put_contents($this->annotationCacheFilename, $output);
        $this->includeAnnotationCache();
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
        if ($gridAnnotation = $this->reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid')) {
            $actions = $gridAnnotation->actions;
            $sort = $gridAnnotation->sort;
        }

        $gridColumns = [];
        foreach ($properties as $property) {
            /** @var \Dtc\GridBundle\Annotation\Column $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\Column');
            if ($annotation) {
                $name = $property->getName();
                $label = $annotation->label ?: $this->fromCamelCase($name);
                $gridColumns[$name] = ['class' => '\Dtc\GridBundle\Grid\Column\GridColumn', 'arguments' => [$name, $label]];
                $gridColumns[$name]['arguments'][] = null;
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
        try {
            $sortInfo = $this->extractSortInfo($sort);
            $this->validateSortInfo($sortInfo, $gridColumns);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException($reflectionClass->getName().' - '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return ['columns' => $gridColumns, 'sort' => $sortInfo];
    }

    /**
     * @param array $sortInfo
     * @param array $gridColumns
     *
     * @throws \InvalidArgumentException
     */
    protected function validateSortInfo(array $sortInfo, array $gridColumns)
    {
        if ($sortInfo['direction']) {
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
            foreach (array_keys($gridColumns) as $name) {
                if ($name === $column) {
                    return;
                }
            }
            throw new \InvalidArgumentException("Grid's sort annotation column '$column' not in list of columns (".implode(', ', array_keys($gridColumns)).')');
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

        $columns = array();
        foreach ($fields as $field) {
            $mapping = $metadata->getFieldMapping($field);
            if (isset($mapping['options']) && isset($mapping['options']['label'])) {
                $label = $mapping['options']['label'];
            } else {
                $label = $this->fromCamelCase($field);
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
    protected function getIdColumn()
    {
        static $identifier = false;
        if (false !== $identifier) {
            return $identifier;
        }

        $metadata = $this->getClassMetadata();
        $identifier = $metadata->getIdentifier();
        $identifier = isset($identifier[0]) ? $identifier[0] : null;

        return $identifier;
    }

    public function hasIdColumn()
    {
        return $this->getIdColumn() ? true : false;
    }
}
