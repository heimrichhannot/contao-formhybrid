<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FormHybrid\Event;


use HeimrichHannot\FormHybrid\DC_Hybrid;

class FormhybridModifyAsyncFormSubmitResponseEvent
{
    const NAME = 'formhybridModifyAsyncFormSubmitResponse';
    /**
     * @var string
     */
    protected $html;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var DC_Hybrid
     */
    protected $dc;

    /**
     * @param string $html
     * @param array $data
     * @param DC_Hybrid $dc
     */
    public function __construct($html, array $data, DC_Hybrid $dc)
    {
        if (!is_string($html)) {
            throw new \InvalidArgumentException("Parameter $html must be of type string.");
        }
        $this->html = $html;
        $this->data = $data;
        $this->dc = $dc;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        if (!is_string($html)) {
            throw new \InvalidArgumentException("Parameter $html must be of type string.");
        }
        $this->html = $html;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return DC_Hybrid
     */
    public function getDc()
    {
        return $this->dc;
    }
}