<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;
use Twig_Template;

class TwigBlockGridColumn extends AbstractGridColumn
{
    protected $template;
    protected $blockName;
    protected $env = array();

    /**
     * Block name.
     *
     * @param string        $field
     * @param string        $label
     * @param Twig_Template $template
     * @param string        $blockName
     */
    public function __construct($field, $label, Twig_Template $template, array $env, $blockName = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->template = $template;
        $this->blockName = $blockName;
        $this->env = $env;

        if (!$this->blockName) {
            $this->blockName = $field;
        }
    }

    /**
     * @return $template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return $blockName
     */
    public function getBlockName()
    {
        return $this->blockName;
    }

    public function format($object, GridSourceInterface $gridSource)
    {
        if ($this->template->hasBlock($this->blockName, [])) {
            $this->env['obj'] = $object;
            $this->env['source'] = $gridSource;

            return $this->template->renderBlock($this->blockName, $this->env);
        } else {
            return 'No Template';
        }
    }
}
