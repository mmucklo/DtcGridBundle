<?php
namespace Dtc\GridBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigExtension extends \Twig_Extension
{
    public function getFunction() {
        $names = array(
                'format_cell' => 'format_cell',
        );

        $funcs = array();
        foreach ($names as $twig => $local) {
            $funcs[$twig] = new \Twig_Filter_Method($this, $local);
        }

        return $funcs;
    }

    public function getFilters() {
        $names = array(
                'format_cell' => 'format_cell',
        );

        $funcs = array();
        foreach ($names as $twig => $local) {
            $funcs[$twig] = new \Twig_Filter_Method($this, $local);
        }

        return $funcs;
    }

    public function getName()
    {
        return 'dtc_grid';
    }

    public function format_cell($value) {
        if (is_object($value)) {
            if ($value instanceof \DateTime) {
                return $value->format(\DateTime::ISO8601);
            }

            return 'object: ' . get_class($value);
        }
        else if (is_scalar($value)) {
            return $value;
        }
        else if (is_array($value)) {
            return 'array';
        }
    }
}
