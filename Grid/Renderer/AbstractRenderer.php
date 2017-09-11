<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

abstract class AbstractRenderer
{
    /** @var GridSourceInterface */
    protected $gridSource;

    protected $options;

    protected $bootstrapCss;
    protected $bootstrapJs;

    /**
     * @param array|null $params Will be populated if passed in
     */
    public function getParams(array &$params = null)
    {
        if ($params === null) {
            $params = [];
        }
        $params['grid'] = $this;
        $params['bootstrap_css'] = $this->bootstrapCss;
        $params['bootstrap_js'] = $this->bootstrapJs;

        return $params;
    }

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

    /**
     * @return mixed
     */
    public function getBootstrapCss()
    {
        return $this->bootstrapCss;
    }

    /**
     * @param mixed $bootstrapCss
     */
    public function setBootstrapCss($bootstrapCss)
    {
        $this->bootstrapCss = $bootstrapCss;
    }

    /**
     * @return mixed
     */
    public function getBootstrapJs()
    {
        return $this->bootstrapJs;
    }

    /**
     * @param mixed $bootstrapJs
     */
    public function setBootstrapJs($bootstrapJs)
    {
        $this->bootstrapJs = $bootstrapJs;
    }

    abstract public function render();
}
