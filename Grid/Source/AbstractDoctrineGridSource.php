<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractDoctrineGridSource extends AbstractGridSource
{
    protected $objectManager;
    protected $idColumn;
    protected $objectName;

    public function __construct(ObjectManager $objectManager, $objectName)
    {
        $this->objectManager = $objectManager;
        $this->objectName = $objectName;
    }

    public function setIdColumn($idColumn)
    {
        $this->idColumn = $idColumn;
    }

    public function hasIdColumn()
    {
        return $this->idColumn ? true : false;
    }
}
