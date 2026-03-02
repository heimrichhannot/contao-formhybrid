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

use Contao\Config;
use Firebase\JWT\JWT;
use HeimrichHannot\Haste\Util\Url;

class TokenGenerator
{
    /**
     * Generates opt-out tokens for notification_center
     * Following tokens are generated:
     * * opt_out_token the encrypted token to append to url
     * * opt_out_link relative url container url parameter and token
     *
     * @param string $table The entity database table name
     * @param string $token The token-value from the opt-out database field
     *
     * @return array Contains the tokens
     */
    public static function optOutTokens(string $table, string $token): array
    {
        $jwt = JWT::encode(
            payload: [
                'table' => $table,
                'token' => $token,
                'date'  => time(),
            ],
            key: Config::get('encryptionKey'),
            alg: 'HS256',
        );

        $link = Url::addQueryString(FormHybrid::OPT_OUT_REQUEST_ATTRIBUTE . '=' . $jwt, '');

        return [
            'opt_out_token' => $jwt,
            'opt_out_link' => $link,
        ];
    }
}