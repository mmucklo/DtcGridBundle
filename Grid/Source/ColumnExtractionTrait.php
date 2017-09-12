<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
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
            $this->addColumns($annotationColumns);

            return;
        }

        $this->addColumns($this->getReflectionColumns());
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

        if (!$this->debug && $this->annotationColumns !== null) {
            return $this->annotationColumns ?: null;
        }

        // Check mtime of class
        if (is_file($this->annotationCacheFilename)) {
            $result = $this->getCachedAnnotationColumns();
            if ($result !== null) {
                return $result;
            }
        }

        // cache annotation
        $this->populateAndCacheAnnotationColumns();

        return $this->annotationColumns ?: null;
    }

    /**
     * Cached annotation columns from the file, if the mtime of the file has not changed (or if not in debug).
     *
     * @return mixed|null
     */
    protected function getCachedAnnotationColumns()
    {
        if (!$this->debug) {
            return $this->annotationColumns = include $this->annotationCacheFilename;
        }

        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $filename = $reflectionClass->getFileName();
        if ($filename && is_file($filename)) {
            $mtime = filemtime($filename);
            $mtimeAnnotation = filemtime($this->annotationCacheFilename);
            if ($mtime && $mtimeAnnotation && $mtime <= $mtimeAnnotation) {
                return $this->annotationColumns = include $this->annotationCacheFilename;
            }
        }

        return null;
    }

    /**
     * Caches the annotation columns result into a file.
     */
    protected function populateAndCacheAnnotationColumns()
    {
        $annotationColumns = $this->generateAnnotationColumns();
        if ($annotationColumns) {
            $output = "<?php\nreturn array(\n";
            foreach ($annotationColumns as $field => $info) {
                $label = $info['label'];
                $output .= "'$field' => new \Dtc\GridBundle\Grid\Column\GridColumn('$field', '$label'";
                if ($info['sortable']) {
                    $output .= ", null, ['sortable' => true]";
                }
                $output .= "),\n";
            }
            $output .= ");\n";
        } else {
            $output = "<?php\nreturn false;\n";
        }
        file_put_contents($this->annotationCacheFilename, $output);
        $this->annotationColumns = include $this->annotationCacheFilename;
    }

    /**
     * Generates a list of proeprty name and labels based on finding the GridColumn annotation.
     *
     * @return array
     */
    protected function generateAnnotationColumns()
    {
        $metadata = $this->getClassMetadata();
        $reflectionClass = $metadata->getReflectionClass();
        $properties = $reflectionClass->getProperties();

        $gridColumns = [];
        foreach ($properties as $property) {
            /** @var \Dtc\GridBundle\Annotation\GridColumn $annotation */
            $annotation = $this->reader->getPropertyAnnotation($property, 'Dtc\GridBundle\Annotation\GridColumn');
            if ($annotation) {
                $name = $property->getName();
                $label = $annotation->getLabel() ?: $this->fromCamelCase($name);
                $gridColumns[$name] = ['label' => $label, 'sortable' => $annotation->getSortable()];
            }
        }

        return $gridColumns;
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
                if (isset($mapping['strategy']) && $mapping['strategy'] == 'auto') {
                    continue;
                }
            }
            $columns[$field] = new GridColumn($field, $label);
        }

        return $columns;
    }
}
