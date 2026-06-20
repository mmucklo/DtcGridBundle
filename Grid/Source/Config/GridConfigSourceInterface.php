<?php

namespace Dtc\GridBundle\Grid\Source\Config;

/**
 * A single place grid configuration can come from (PHP 8 attributes, Doctrine
 * annotations, ...). The orchestrator composes one or more of these in
 * precedence order, so adding a new configuration source is a matter of
 * implementing this interface rather than threading branches through the
 * resolver.
 */
interface GridConfigSourceInterface
{
    /**
     * The class-level Grid marker declared by this source, or null if none.
     *
     * @return \Dtc\GridBundle\Annotation\Grid|null
     */
    public function getGrid(\ReflectionClass $reflectionClass);

    /**
     * The Column definition this source declares for a single property, or
     * null. Resolving one property at a time lets the orchestrator merge
     * sources per property while preserving declaration order.
     *
     * @return \Dtc\GridBundle\Annotation\Column|null
     */
    public function getColumn(\ReflectionProperty $property);

    /**
     * Class-level actions declared separately from the Grid marker (PHP 8
     * attributes), or null when this source declares none separately.
     *
     * @return \Dtc\GridBundle\Annotation\Action[]|null
     */
    public function getActions(\ReflectionClass $reflectionClass);

    /**
     * Class-level sorts declared separately from the Grid marker.
     *
     * @return \Dtc\GridBundle\Annotation\Sort[]
     */
    public function getSorts(\ReflectionClass $reflectionClass);
}
