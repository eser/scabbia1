<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Oauth;

use Scabbia\Extensions\Http\Http;

/**
 * Oauth Extension
 *
 * @package Scabbia
 * @subpackage Oauth
 * @version 1.1.0
 */
class Oauth
{
    /**
     * @ignore
     */
    const METHOD_PLAINTEXT = 0;
    /**
     * @ignore
     */
    const METHOD_HMAC_SHA1 = 1;

    /**
     * @ignore
     */
    public static function getTokenUrl($uKey, $uSecret)
    {
        return Http::encodeArray(array(
            'oauth_token' => $uKey,
            'oauth_token_secret' => $uSecret
        ));
    }

    /**
     * @ignore
     */
    public static function sign($uMethod, $uBaseString, $uConsumerSecret, $uTokenSecret)
    {
        $tKey = Http::encode($uConsumerSecret);
        if (strlen($uTokenSecret) > 0) {
            $tKey .= '&' . Http::encode($uTokenSecret);
        }

        if ($uMethod === self::METHOD_PLAINTEXT) {
            return $tKey;
        }

        return base64_encode(hash_hmac('sha1', $uBaseString, $tKey, true));
    }

    /**
     * @ignore
     */
    public static function checkSign($uMethod, $uBaseString, $uConsumer, $uTokenSecret, $uSignatureSecret)
    {
        $tBuilt = self::sign($uMethod, $uBaseString, $uConsumer, $uTokenSecret);

        $tBuiltLen = strlen($tBuilt);
        $tSignatureLen = strlen($uSignatureSecret);
        if ($tBuiltLen === 0 || $tSignatureLen === 0 || $tBuiltLen !== $tSignatureLen) {
            return false;
        }

        $tResult = 0;
        for ($i = 0; $i < $tSignatureLen; $i++) {
            $tResult |= ord($tBuilt[$i]) ^ ord($tSignatureLen[$i]);
        }

        return ($tResult === 0);
    }
}
