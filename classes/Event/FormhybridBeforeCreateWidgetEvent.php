<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FormHybrid\Event;


use HeimrichHannot\FormHybrid\DC_Hybrid;

class FormhybridBeforeCreateWidgetEvent
{
    const NAME = 'formhybridBeforeCreateWidget';

    /**
     * @var array
     */
    protected $widgetData;
    /**
     * @var string
     */
    protected $widgetClass;
    /**
     * @var array
     */
    protected $dcaFieldData;
    /**
     * @var DC_Hybrid
     */
    protected $formInstance;

    /**
     * FormhybridBeforeCreateWidgetEvent constructor.
     *
     * @param array $widgetData
     * @param string $widgetClass
     * @param array $dcaFieldData
     * @param DC_Hybrid $formInstance
     */
    public function __construct(array $widgetData, $widgetClass, array $dcaFieldData, DC_Hybrid $formInstance)
    {
        $this->widgetData   = $widgetData;
        $this->widgetClass  = $widgetClass;
        $this->dcaFieldData = $dcaFieldData;
        $this->formInstance = $formInstance;
    }

    /**
     * @return array
     */
    public function getWidgetData()
    {
        return $this->widgetData;
    }

    /**
     * @return string
     */
    public function getWidgetClass()
    {
        return $this->widgetClass;
    }

    /**
     * @return array
     */
    public function getDcaFieldData()
    {
        return $this->dcaFieldData;
    }

    /**
     * @return DC_Hybrid
     */
    public function getFormInstance()
    {
        return $this->formInstance;
    }

    /**
     * @param array $widgetData
     */
    public function setWidgetData($widgetData)
    {
        $this->widgetData = $widgetData;
    }

    /**
     * @param string $widgetClass
     */
    public function setWidgetClass($widgetClass)
    {
        $this->widgetClass = $widgetClass;
    }


}