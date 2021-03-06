<?php

namespace HeimrichHannot\FormHybrid;

class FormHybrid
{
    const EXPORT_TYPE_FORMHYBRID = 'formhybrid';

    const NOTIFICATION_TYPE_FORMHYBRID  = 'formhybrid';
    const NOTIFICATION_TYPE_FORM_OPT_IN = 'formhybrid-opt-in';

    const OPT_IN_REQUEST_ATTRIBUTE = 'fh-oit';
    const OPT_OUT_REQUEST_ATTRIBUTE = 'fh-oot';

    const OPT_IN_DATABASE_FIELD = 'optInToken';
    const OPT_OUT_DATABASE_FIELD = 'optOutToken';

    public static function addOptInFieldToTable($strTable)
    {
        \Controller::loadDataContainer($strTable);

        $GLOBALS['TL_DCA'][$strTable]['fields'][static::OPT_IN_DATABASE_FIELD] = ['sql' => "varchar(36) NOT NULL default ''"];
    }

    public static function addOptOutFieldToTable($strTable)
    {
        \Controller::loadDataContainer($strTable);

        $GLOBALS['TL_DCA'][$strTable]['fields'][static::OPT_OUT_DATABASE_FIELD] = ['sql' => "varchar(36) NOT NULL default ''"];
    }
}