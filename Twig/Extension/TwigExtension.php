<?php

namespace Dtc\GridBundle\Twig\Extension;

class TwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunction()
    {
        $names = array(
            'format_cell' => 'format_cell',
        );

        $funcs = array();
        foreach ($names as $twig => $local) {
            $funcs[$twig] = new \Twig\TwigFunction($twig, [$this, $local]);
        }

        return $funcs;
    }

    public function getFilters()
    {
        $names = array(
            'format_cell' => 'format_cell',
        );

        $funcs = array();
        foreach ($names as $twig => $local) {
            $funcs[$twig] = new \Twig\TwigFilter($twig, [$this, $local]);
        }

        return $funcs;
    }

    public function getName()
    {
        return 'dtc_grid';
    }

    public function format_cell($value)
    {
        if (is_object($value)) {
            if ($value instanceof \DateTime) {
                return $value->format(\DateTime::ISO8601);
            }

            return 'object: ' . get_class($value);
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_array($value)) {
            return 'array';
        }
    }
}