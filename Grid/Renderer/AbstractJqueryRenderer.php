<?php

namespace Dtc\GridBundle\Grid\Renderer;

abstract class AbstractJqueryRenderer extends TableGridRenderer
{
    protected $jQuery = [];
    protected $purl;

    public function getPurl()
    {
        return $this->purl;
    }

    public function setPurl($purl)
    {
        $this->purl = $purl;
    }

    public function getJQuery()
    {
        return $this->jQuery;
    }

    public function setJQuery(array $jQuery)
    {
        $this->jQuery = $jQuery;
    }

    /**
     * @param array|null $params
     */
    public function getParams(array &$params = null)
    {
        parent::getParams($params);
        $params['dtc_grid_jquery'] = $this->jQuery;
        $params['dtc_grid_purl'] = $this->purl;

        return $params;
    }
}
