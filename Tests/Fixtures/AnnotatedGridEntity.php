<?php

namespace Dtc\GridBundle\Tests\Fixtures;

use Dtc\GridBundle\Annotation\Column;
use Dtc\GridBundle\Annotation\DeleteAction;
use Dtc\GridBundle\Annotation\Grid;
use Dtc\GridBundle\Annotation\ShowAction;
use Dtc\GridBundle\Annotation\Sort;

/**
 * Fixture exercising the legacy Doctrine annotation reader path: a Grid
 * annotation with nested actions and sort, plus Column annotations on the
 * properties. Guards the pre-existing annotation API now that the annotation
 * classes have constructors (Doctrine instantiates them with the parsed
 * value array as the first argument). Mentioning annotation names with a
 * leading at-sign in this prose would make DocParser treat them as real
 * annotations, hence the wording.
 *
 * @Grid(actions={@ShowAction, @DeleteAction}, sort=@Sort(column="name", direction="ASC"))
 */
class AnnotatedGridEntity
{
    private $id;

    /**
     * @Column(label="Full Name", sortable=true, searchable=true)
     */
    private $name;

    /**
     * @Column(label="Email Address", sortable=true)
     */
    private $email;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
