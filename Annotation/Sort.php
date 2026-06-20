<?php

namespace Dtc\GridBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("ANNOTATION")
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Sort implements Annotation
{
    use ValidatesArguments;

    /**
     * @var string Default sort order
     */
    public $direction = 'ASC';

    /**
     * @var string Column name to sort on by default
     */
    public $column;

    public function __construct($direction = null, $column = null)
    {
        self::assertOneOf($direction, ['ASC', 'DESC'], 'direction');
        self::assertString($column, 'column');
        // Only assign non-null values so the 'ASC' direction default survives
        // an omitted argument.
        if (null !== $direction) {
            $this->direction = $direction;
        }
        if (null !== $column) {
            $this->column = $column;
        }
    }
}
