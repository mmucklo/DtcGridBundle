<?php

namespace Dtc\GridBundle\Grid\Source;

class FileColumnSource
{
    private $filename;
    private $cacheDir;
    private $columns;
    private $cacheFilename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function getColumns()
    {
        if (null !== $this->columns) {
            return $this->columns ?: null;
        }
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getCacheFilename()
    {
        if ($this->cacheFilename) {
            return $this->cacheFilename;
        }

        return $this->cacheFilename = ColumnSource::createCacheFilename($this->cacheDir, $this->filename);
    }
}
