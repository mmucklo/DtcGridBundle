<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

abstract class AbstractRenderer
{
    /** @var GridSourceInterface */
    protected $gridSource;

    protected $options;

    protected $themeCss;
    protected $themeJs;
    protected $pageDivStyle;

    /**
     * @param array|null $params Will be populated if passed in
     */
    public function getParams(?array &$params = null)
    {
        if (null === $params) {
            $params = [];
        }

        $params['dtc_grid'] = $this;
        $params['dtc_grid_theme_css'] = $this->themeCss;
        $params['dtc_grid_theme_js'] = $this->themeJs;
        $params['dtc_grid_page_div_style'] = $this->pageDivStyle;
        $params['dtc_grid_local_css'] = [];
        $params['dtc_grid_local_js'] = [];

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

    public function getThemeCss()
    {
        return $this->themeCss;
    }

    public function setThemeCss(array $themeCss)
    {
        $this->themeCss = $themeCss;
    }

    public function getThemeJs()
    {
        return $this->themeJs;
    }

    public function setThemeJs(array $themeJs)
    {
        $this->themeJs = $themeJs;
    }

    public function getPageDivStyle()
    {
        return $this->pageDivStyle;
    }

    public function setPageDivStyle($pageDivStyle)
    {
        $this->pageDivStyle = $pageDivStyle;
    }

    abstract public function render();
}
