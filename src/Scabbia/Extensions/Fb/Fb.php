<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Fb;

use Scabbia\Extensions\Fb\Facebook;
use Scabbia\Extensions\Fb\FacebookQueryObject;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Session\Session;
use Scabbia\Config;
use Scabbia\Framework;

/**
 * Fb Extension
 *
 * @package Scabbia
 * @subpackage Fb
 * @version 1.1.0
 */
class Fb
{
    /**
     * @ignore
     */
    const NO_USER_ID = 0;


    /**
     * @ignore
     */
    public static $appId;
    /**
     * @ignore
     */
    public static $appSecret;
    /**
     * @ignore
     */
    public static $appUrl;
    /**
     * @ignore
     */
    public static $appPermissions;
    /**
     * @ignore
     */
    public static $appRedirectUri;
    /**
     * @ignore
     */
    public static $appFileUpload;
    /**
     * @ignore
     */
    public static $api = null;
    /**
     * @ignore
     */
    public static $userId = null;
    /**
     * @ignore
     */
    public static $facebookData = null;


    /**
     * @ignore
     */
    public static function loadApi()
    {
        self::$appId = Config::get('facebook/applicationId');
        self::$appSecret = Config::get('facebook/applicationSecret');
        self::$appUrl = Config::get('facebook/applicationUrl');
        self::$appPermissions = Config::get('facebook/permissions', 'email, read_stream');
        self::$appRedirectUri = Config::get('facebook/redirectUrl');
        self::$appFileUpload = Config::get('facebook/fileUpload', false);

        self::$api = new \Facebook(
            array(
                'appId' => self::$appId,
                'secret' => self::$appSecret,
                'cookie' => true,
                'fileUpload' => self::$appFileUpload
            )
        );

        self::$userId = self::$api->getUser();

        self::$facebookData = Session::get('facebookData', null);
        if (is_null(self::$facebookData) || self::$facebookData['userid'] !== self::$userId)
        {
            self::resetSession();
        }
    }

    /**
     * @ignore
     */
    public static function resetSession($uForceClear = false)
    {
        if ($uForceClear || is_null(self::$facebookData)) {
            self::$facebookData = array();
        }

        self::$facebookData['userid'] = self::$userId;
        self::$facebookData['cache'] = array();

        Session::set('facebookData', self::$facebookData);
    }

    /**
     * @ignore
     */
    public static function login($uExtendedAccess = true, $uForcePermissions = false)
    {
        if (self::$userId !== self::NO_USER_ID) {
            if (!$uForcePermissions || self::checkUserPermission(self::$appPermissions)) {
                return true;
            }
        }

        $tError = Request::get('error_code', null);
        if (!is_null($tError)) {
            return false;
        }

        $tCode = Request::get('code', null);
        if (is_null($tCode)) {
            Session::set('facebookData', self::$facebookData);

            $tLoginUrl = self::$api->getLoginUrl(
                array(
                    'scope' => self::$appPermissions,
                    'redirect_uri' => self::$appRedirectUri
                )
            );

            header('Location: ' . $tLoginUrl, true);
            Framework::end(0);

            return false;
        }

        if ($uExtendedAccess) {
            self::$api->setExtendedAccessToken();
        }

        self::$facebookData['access_token'] = self::$api->getAccessToken();
        Session::set('facebookData', self::$facebookData);

        return true;
    }

    /**
     * @ignore
     */
    public static function logoutUrl()
    {
        return self::$api->getLogoutUrl();
    }

    /**
     * @ignore
     */
    public static function get($uQuery, $uUseCache = false, $uExtra = null)
    {
        if (self::$userId === self::NO_USER_ID) {
            return false;
        }

        if (is_null($uExtra)) {
            $uExtra = array();
        }

        if ($uUseCache && isset(self::$facebookData['cache'][$uQuery])) {
            $tObject = self::$facebookData['cache'][$uQuery];
        } else {
            try {
                $tObject = self::$api->api($uQuery, $uExtra);

                self::$facebookData['cache'][$uQuery] = $tObject;
                Session::set('facebookData', self::$facebookData);
            } catch (\FacebookApiException $tException) {
                return false;
            }
        }

        return new FacebookQueryObject($tObject);
    }

    /**
     * @ignore
     */
    public static function checkUserPermission($uPermissions = null)
    {
        $tPermissions = String::coalesce($uPermissions, self::$appPermissions);

        if (!is_null($tPermissions)) {
            $tUserPermissions = self::get('/me/permissions', true);

            if ($tUserPermissions === false || count($tUserPermissions->data) == 0) {
                return false;
            }

            foreach (explode(',', $uPermissions) as $tPermission) {
                if (!array_key_exists(trim($tPermission), $tUserPermissions->data[0])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function checkLike($uId)
    {
        $tLikeResponse = self::get('/me/likes/' . $uId, false, null);

        if ($tLikeResponse === false || empty($tLikeResponse->data)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function postToFeed($uUser, $uContent)
    {
        $uContent['access_token'] = self::$facebookData['access_token'];

        self::$api->api('/' . $uUser . '/feed', 'post', $uContent);
    }

    /**
     * @ignore
     */
    public static function getUser($uExtra = null)
    {
        if (is_null($uExtra)) {
            $uExtra = array();
        }

        if (!isset($uExtra['fields'])) {
            $uExtra['fields'] = 'name,first_name,last_name,username,quotes,gender,email' .
                                'timezone,locale,verified,updated_time,picture,link';
        }

        return self::get('/me', true, $uExtra);
    }

    /**
     * @ignore
     */
    public static function getUserLikes($uExtra = null)
    {
        if (is_null($uExtra)) {
            $uExtra = array();
        }

        if (!isset($uExtra['fields'])) {
            $uExtra['fields'] = 'name,category,picture,link';
        }

        return self::get('/me/likes', true, $uExtra);
    }

    /**
     * @ignore
     */
    public static function getUserHome($uExtra = null)
    {
        return self::get('/me/home', true, $uExtra);
    }

    /**
     * @ignore
     */
    public static function getUserFeed($uExtra = null)
    {
        return self::get('/me/feed', true, $uExtra);
    }

    /**
     * @ignore
     */
    public static function getUserFriends($uExtra = null)
    {
        if (is_null($uExtra)) {
            $uExtra = array();
        }

        if (!isset($uExtra['fields'])) {
            $uExtra['fields'] = 'name,username,picture,link';
        }

        return self::get('/me/friends', true, $uExtra);
    }
}
