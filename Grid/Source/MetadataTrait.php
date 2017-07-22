<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Util\CamelCaseTrait;

trait MetadataTrait
{
    use CamelCaseTrait;

    /**
     * @return ClassMetadataInfo|ClassMetadataInfo
     */
    abstract public function getClassMetadata();

    public function autoDiscoverColumns()
    {
        $this->setColumns($this->getReflectionColumns());
    }

    /**
     * Generate Columns based on document's Metadata.
     */
    public function getReflectionColumns()
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
