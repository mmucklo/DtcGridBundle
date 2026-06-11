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
            if ($actions instanceof Action) {
                $actions = [$actions];
            }
            if (!is_array($actions)) {
                throw new \InvalidArgumentException('Grid "actions" must be an Action or an array of Action instances, got '.gettype($actions));
            }
            foreach ($actions as $action) {
                if (!$action instanceof Action) {
                    throw new \InvalidArgumentException('Grid "actions" elements must be Action instances, got '.(is_object($action) ? get_class($action) : gettype($action)));
                }
            }
            $this->actions = $actions;
        }
        if (null !== $sort) {
            if (!$sort instanceof Sort) {
                throw new \InvalidArgumentException('Grid "sort" must be a Sort instance, got '.(is_object($sort) ? get_class($sort) : gettype($sort)));
            }
            $this->sort = $sort;
        }
        if (null !== $sortMulti) {
            if ($sortMulti instanceof Sort) {
                $sortMulti = [$sortMulti];
            }
            if (!is_array($sortMulti)) {
                throw new \InvalidArgumentException('Grid "sortMulti" must be a Sort or an array of Sort instances, got '.gettype($sortMulti));
            }
            foreach ($sortMulti as $sortItem) {
                if (!$sortItem instanceof Sort) {
                    throw new \InvalidArgumentException('Grid "sortMulti" elements must be Sort instances, got '.(is_object($sortItem) ? get_class($sortItem) : gettype($sortItem)));
                }
            }
            $this->sortMulti = $sortMulti;
        }
    }
}
