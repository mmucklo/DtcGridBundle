<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

abstract class AbstractRenderer
{
    /** @var GridSourceInterface */
    protected $gridSource;

    protected $options;

    public function setOptions(array $values)
    {
        $this->options = $values;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function bind(GridSourceInterface $gridSource)
    {
        $this->gridSource = $gridSource;
        $this->afterBind();

        return $this;
    }

    public function getGridOptions()
    {
        return $this->options;
    }

    protected function afterBind()
    {
    }

    abstract public function render();
}
