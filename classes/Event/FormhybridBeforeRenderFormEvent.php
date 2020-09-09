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


use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\FormHybrid\DC_Hybrid;

class FormhybridBeforeRenderFormEvent
{
    const NAME = 'formhybridBeforeRenderForm';
    /**
     * @var Template
     */
    protected $template;
    /**
     * @var ModuleModel
     */
    protected $moduleModel;
    /**
     * @var DC_Hybrid
     */
    protected $dataContainer;

    /**
     * FormhybridBeforeRenderFormEvent constructor.
     */
    public function __construct(Template $template, ModuleModel $module, DC_Hybrid $dataContainer)
    {
        $this->template      = $template;
        $this->moduleModel   = $module;
        $this->dataContainer = $dataContainer;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return ModuleModel
     */
    public function getModuleModel()
    {
        return $this->moduleModel;
    }

    /**
     * @return DC_Hybrid
     */
    public function getDataContainer()
    {
        return $this->dataContainer;
    }
}