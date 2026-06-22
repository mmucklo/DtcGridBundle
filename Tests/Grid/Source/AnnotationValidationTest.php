<?php

namespace Dtc\GridBundle\Tests\Grid\Source;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Dtc\GridBundle\Tests\Fixtures\InvalidColumnKeyEntity;
use Dtc\GridBundle\Tests\Fixtures\InvalidColumnTypeEntity;

/**
 * Misconfigured annotations must fail loudly at read time, not silently
 * produce a wrong grid. Guards the validation that the named-argument
 * constructors (and their type checks) provide through the Doctrine reader.
 */
class AnnotationValidationTest extends ColumnSourceTestCase
{
    public function testWrongTypeForSortableThrows()
    {
        // doctrine/annotations 1.13.3+/2.x wrap constructor exceptions in
        // AnnotationException; 1.13.0 lets them propagate raw. The contract
        // is "fails loudly with a clear message", whichever the wrapper.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('must be a bool');
        $this->buildColumnSourceInfo(InvalidColumnTypeEntity::class, false, new AnnotationReader(), false);
    }

    public function testUnknownColumnKeyThrows()
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('does not have a property named "lable"');
        $this->buildColumnSourceInfo(InvalidColumnKeyEntity::class, false, new AnnotationReader(), false);
    }
}
