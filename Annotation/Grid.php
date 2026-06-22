<?php

namespace Dtc\GridBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Grid implements Annotation
{
    use ValidatesArguments;

    /**
     * @var array<Action>
     */
    public $actions;

    /**
     * @var Sort
     */
    public $sort;

    /**
     * @var array<Sort>
     */
    public $sortMulti;

    /**
     * On PHP 8.0, attribute arguments cannot contain `new`, so actions and
     * sort must be declared as separate class-level attributes
     * (#[ShowAction], #[DeleteAction], #[Action], #[Sort]). On PHP 8.1+,
     * nesting works directly: #[Grid(actions: [new ShowAction()])].
     * The Doctrine annotation reader maps @Grid(actions={...}, sort=@Sort(...))
     * onto these parameters via @NamedArgumentConstructor.
     *
     * @param Action|array<Action>|null $actions
     * @param Sort|null                 $sort
     * @param Sort|array<Sort>|null     $sortMulti
     */
    public function __construct($actions = null, $sort = null, $sortMulti = null)
    {
        if (null !== $actions) {
            $this->actions = self::normalizeList($actions, Action::class, 'actions');
        }
        if (null !== $sort) {
            if (!$sort instanceof Sort) {
                throw new \InvalidArgumentException('Grid "sort" must be a Sort instance, got '.self::describeType($sort));
            }
            $this->sort = $sort;
        }
        if (null !== $sortMulti) {
            $this->sortMulti = self::normalizeList($sortMulti, Sort::class, 'sortMulti');
        }
    }

    /**
     * Normalize a single instance or array of $class into a validated array.
     *
     * @param string $class
     * @param string $param
     *
     * @return array
     */
    private static function normalizeList($value, $class, $param)
    {
        $short = false !== ($pos = strrpos($class, '\\')) ? substr($class, $pos + 1) : $class;
        if ($value instanceof $class) {
            $value = [$value];
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Grid "'.$param.'" must be a '.$short.' or an array of '.$short.' instances, got '.self::describeType($value));
        }
        foreach ($value as $item) {
            if (!$item instanceof $class) {
                throw new \InvalidArgumentException('Grid "'.$param.'" elements must be '.$short.' instances, got '.self::describeType($item));
            }
        }

        return $value;
    }
}
