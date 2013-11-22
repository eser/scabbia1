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
use Scabbia\Extensions\Session\Session;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Request;

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
    public static $appExtendedAccess;
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
    public static function loadApi($uForceClear = false)
    {
        self::$appId = Config::get('facebook/applicationId');
        self::$appSecret = Config::get('facebook/applicationSecret');
        self::$appUrl = Config::get('facebook/applicationUrl');
        self::$appPermissions = Config::get('facebook/permissions', 'email');
        self::$appRedirectUri = Config::get('facebook/redirectUrl');
        self::$appFileUpload = Config::get('facebook/fileUpload', false);
        self::$appExtendedAccess = Config::get('facebook/extendedAccess', false);

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

        $tFirstTime = (self::$facebookData === null || self::$facebookData['userid'] !== self::$userId);
        if ($tFirstTime && self::$appExtendedAccess) {
            self::$api->setExtendedAccessToken();
        }

        if ($uForceClear || $tFirstTime) {
            self::$facebookData = array(
                'userid'        => self::$userId,
                'access_token'  => self::$api->getAccessToken(),
                'cache'         => array()
            );

            Session::set('facebookData', self::$facebookData);
        }
    }

    /**
     * @ignore
     */
    public static function login($uForcePermissions = false, $uRedirectUrl = null)
    {
        $tResult = self::loginUrl($uForcePermissions, $uRedirectUrl);

        if (!$tResult[0] && $tResult[1] !== null) {
            header('Location: ' . $tResult[1], true);
            Framework::end(0);
        }

        return $tResult[0];
    }

    /**
     * @ignore
     */
    public static function loginUrl($uForcePermissions = false, $uRedirectUrl = null)
    {
        if (self::$userId !== self::NO_USER_ID) {
            if (!$uForcePermissions || self::checkUserPermission(self::$appPermissions)) {
                return array(true);
            }
        }

        $tError = Request::get('error_code', null);
        if ($tError !== null) {
            return array(false, null);
        }

        $tCode = Request::get('code', null);
        if ($tCode === null) {
            Session::set('facebookData', self::$facebookData);

            $tLoginUrl = self::$api->getLoginUrl(
                array(
                    'scope' => self::$appPermissions,
                    'redirect_uri' => ($uRedirectUrl !== null) ? $uRedirectUrl : self::$appRedirectUri
                )
            );

            return array(false, $tLoginUrl);
        }

        return array(true);
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

        if ($uExtra === null) {
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

        if ($tPermissions !== null) {
            $tUserPermissions = self::get('/me/permissions', true);

            if ($tUserPermissions === false || count($tUserPermissions->data) === 0) {
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
        if ($uExtra === null) {
            $uExtra = array();
        }

        if (!isset($uExtra['fields'])) {
            $uExtra['fields'] = 'name,first_name,middle_name,last_name,name_format,username,quotes,gender,email,' .
                                'timezone,locale,verified,updated_time,link,picture';
        }

        return self::get('/me', true, $uExtra);
    }

    /**
     * @ignore
     */
    public static function getUserLikes($uExtra = null)
    {
        if ($uExtra === null) {
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
        if ($uExtra === null) {
            $uExtra = array();
        }

        if (!isset($uExtra['fields'])) {
            $uExtra['fields'] = 'name,username,picture,link';
        }

        return self::get('/me/friends', true, $uExtra);
    }
}
