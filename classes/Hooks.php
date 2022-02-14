<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package formhybrid
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


class Hooks extends \Controller
{
    public function parseWidgetHook($strBuffer, $objWidget)
    {
        if (TL_MODE == 'BE') {
            return $strBuffer;
        }

        if ($objWidget->sub) {
            return $strBuffer . $objWidget->sub;
        }

        return $strBuffer;
    }
}
