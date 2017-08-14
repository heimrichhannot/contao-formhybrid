<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FormHybrid;

use Firebase\JWT\JWT;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Ajax\AjaxAction;

class TokenGenerator
{
    /**
     * Generates opt out tokens for notification center
     * Following tokens are generated:
     * * opt_out_token the encrypted token to append to url
     * * opt_out_link relative url container url parameter and token
     *
     * @param string $table The entity database table name
     * @param string $token The token-value from opt out database field
     *
     * @return array Contains the tokens
     */
    public static function optOutTokens ($table, $token)
    {
        $arrToken = [];
        $strJWT = JWT::encode(
            [
                'table' => $table,
                'token' => $token,
                'date'  => time(),
            ],
            \Config::get('encryptionKey')
        );

        $arrToken['opt_out_token'] = $strJWT;
        $arrToken['opt_out_link']  = Url::addQueryString(
            FormHybrid::OPT_OUT_REQUEST_ATTRIBUTE . '=' . $strJWT,
            ''
        );
        return $arrToken;
    }
}