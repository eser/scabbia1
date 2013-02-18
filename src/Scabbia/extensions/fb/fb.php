<?php

	namespace Scabbia\Extensions\Fb;

	use Scabbia\config;
	use Scabbia\Extensions\Session\session;
	use Scabbia\Extensions\String\string;
	use Scabbia\framework;
	use Scabbia\Extensions\Fb\Facebook;
	use Scabbia\Extensions\Fb\FacebookQueryObject;

	/**
	 * Facebook (FB) Extension
	 *
	 * @package Scabbia
	 * @subpackage fb
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends session
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 *
	 * @todo direct api query like /me/home
	 */
	class fb {
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
		public static $appFileUpload;
		/**
		 * @ignore
		 */
		public static $appUrl;
		/**
		 * @ignore
		 */
		public static $appPageId;
		/**
		 * @ignore
		 */
		public static $appRedirectUri;
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
		public static function loadApi() {
			self::$appId = config::get('/facebook/APP_ID');
			self::$appSecret = config::get('/facebook/APP_SECRET');
			self::$appFileUpload = config::get('/facebook/APP_FILEUPLOAD');
			self::$appUrl = config::get('/facebook/APP_URL');
			self::$appPageId = config::get('/facebook/APP_PAGE_ID');
			self::$appRedirectUri = config::get('/facebook/APP_REDIRECT_URI');

			if(is_null(self::$api)) {
				self::$api = new Facebook(array(
				                               'appId' => self::$appId,
				                               'secret' => self::$appSecret,
				                               'cookie' => true,
				                               'fileUpload' => (self::$appFileUpload == '1')
				                          ));
			}

			self::$userId = self::$api->getUser();

			$tUserId = session::get('fbUserId', null);
			if(is_null($tUserId)) { // || self::$userId != intval($tUserId)
				self::resetSession();
			}
		}

		/**
		 * @ignore
		 */
		public static function resetSession() {
			session::remove('fbUser');
			session::remove('fbUserAccessToken');

			foreach(session::getKeys() as $tKey) {
				if(substr($tKey, 0, 3) != 'fb_') {
					continue;
				}

				session::remove($tKey);
			}

			session::set('fbUserId', self::$userId);
		}

		/**
		 * @ignore
		 */
		public static function getUserId() {
			return self::$userId;
		}

		/**
		 * @ignore
		 */
		public static function getUserAccessToken($uExtended = false) {
			if(self::$userId == 0) {
				return false;
			}

			$tUserAccessToken = session::get('fbUserAccessToken', null);
			if(is_null($tUserAccessToken)) {
				$tUserAccessToken = self::$api->getAccessToken();

				if($tUserAccessToken === false) {
					$tUserAccessToken = null;
				}

				session::set('fbUserAccessToken', $tUserAccessToken);
			}

			if($uExtended && !is_null($tUserAccessToken)) {
				$tExtendedUserAccessToken = session::get('fbUserAccessTokenEx', null);
				if(is_null($tExtendedUserAccessToken)) {
					$tExtendedUserAccessTokenResponse = self::$api->unboxOauthRequest(
						self::$api->unboxGetUrl('graph', '/oauth/access_token'),
						array(
						     'client_id' => self::$appId,
						     'client_secret' => self::$appSecret,
						     'grant_type' => 'fb_exchange_token',
						     'fb_exchange_token' => $tUserAccessToken
						)
					);

					if($tExtendedUserAccessTokenResponse !== false) {
						$tExtendedUserAccessTokenArray = array();
						parse_str($tExtendedUserAccessTokenResponse, $tExtendedUserAccessTokenArray);

						if(isset($tExtendedUserAccessTokenArray['access_token'])) {
							$tExtendedUserAccessToken = $tExtendedUserAccessTokenArray['access_token'];

							session::set('fbUserAccessTokenEx', $tExtendedUserAccessToken);
							$tUserAccessToken = $tExtendedUserAccessToken;
						}
					}
				}
				else {
					$tUserAccessToken = $tExtendedUserAccessToken;
				}
			}

			return $tUserAccessToken;
		}

		/**
		 * @ignore
		 */
		public static function getLoginUrl($uPermissions, $uRedirectUri = null) {
			$tLoginUrl = self::$api->getLoginUrl(array(
			                                          'scope' => $uPermissions,
			                                          'redirect_uri' => string::coalesce($uRedirectUri, self::$appRedirectUri)
			                                     ));

			return $tLoginUrl;
		}

		/**
		 * @ignore
		 */
		public static function checkLogin($uPermissions, $uRequiredPermissions = null, $uRedirectUri = null) {
			if(
				self::$userId == 0 ||
				(!is_null($uRequiredPermissions) && strlen($uRequiredPermissions) > 0 && !self::checkUserPermission($uRequiredPermissions))
			) {
				$tLoginUrl = self::getLoginUrl($uPermissions, $uRedirectUri);
				session::remove('fb_me_permissions');
				header('Location: ' . $tLoginUrl, true);
				framework::end(0);
			}
		}

		/**
		 * @ignore
		 */
		public static function checkUserPermission($uPermissions) {
			if(self::$userId == 0) {
				return false;
			}

			$tUserPermissions = self::get('/me/permissions', true);

			if(count($tUserPermissions->data) == 0) {
				return false;
			}

			foreach(explode(',', $uPermissions) as $tPermission) {
				if(!array_key_exists($tPermission, $tUserPermissions->data[0])) {
					return false;
				}
			}

			return true;
		}

		/**
		 * @ignore
		 */
		public static function checkLike($uId) {
			if(self::$userId == 0) {
				return false;
			}

			$tLikeResponse = self::get('/me/likes/' . $uId, false, null);

			if(!empty($tLikeResponse->data)) {
				return true;
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public static function get($uQuery, $uUseCache = false, $uExtra = null) {
			if(self::$userId == 0) {
				return false;
			}

			if(is_null($uExtra)) {
				$uExtra = array();
			}

			if(!$uUseCache || framework::$development >= 1) {
				try {
					$tObject = self::$api->api($uQuery, $uExtra);
				}
				catch(FacebookApiException $tException) {
					return false;
				}

				return new FacebookQueryObject($tObject);
			}

			$tQuerySerialized = 'fb' . string::capitalizeEx($uQuery, '/', '_');
			$tObject = session::get($tQuerySerialized, null);
			if(is_null($tObject)) {
				try {
					$tObject = self::$api->api($uQuery, $uExtra);
					session::set($tQuerySerialized, $tObject);
				}
				catch(FacebookApiException $tException) {
					return false;
				}
			}

			return new FacebookQueryObject($tObject);
		}

		/**
		 * @ignore
		 */
		public static function postToFeed($uUser, $uAccessToken, $uContent) {
			$uContent['access_token'] = $uAccessToken;

			self::$api->api('/' . $uUser . '/feed', 'post', $uContent);
		}

		/**
		 * @ignore
		 */
		public static function getUser($uExtra = null) {
			if(is_null($uExtra)) {
				$uExtra = array();
			}

			if(!isset($uExtra['fields'])) {
				$uExtra['fields'] = 'name,first_name,last_name,username,quotes,gender,email,timezone,locale,verified,updated_time,picture,link';
			}

			return self::get('/me', true, $uExtra);
		}

		/**
		 * @ignore
		 */
		public static function getUserLikes($uExtra = null) {
			if(is_null($uExtra)) {
				$uExtra = array();
			}

			if(!isset($uExtra['fields'])) {
				$uExtra['fields'] = 'name,category,picture,link';
			}

			return self::get('/me/likes', true, $uExtra);
		}

		/**
		 * @ignore
		 */
		public static function getUserHome($uExtra = null) {
			return self::get('/me/home', true, $uExtra);
		}

		/**
		 * @ignore
		 */
		public static function getUserFeed($uExtra = null) {
			return self::get('/me/feed', true, $uExtra);
		}

		/**
		 * @ignore
		 */
		public static function getUserFriends($uExtra = null) {
			if(is_null($uExtra)) {
				$uExtra = array();
			}

			if(!isset($uExtra['fields'])) {
				$uExtra['fields'] = 'name,username,picture,link';
			}

			return self::get('/me/friends', true, $uExtra);
		}

//		public static function getAccessToken($uCode) {
//			$tResult = file_get_contents(BaseFacebook::$DOMAIN_MAP['graph'] . 'oauth/access_token?client_id=' . self::$appId . '&redirect_uri=' . urlencode(self::$appRedirectUri) . '&client_secret=' . self::$appSecret . '&code=' . $uCode);
//
//			return ($tResult == 'true');
//		}

//		public static function userLikedPage($uFacebookId, $uAccessToken) {
//			$tResult = file_get_contents(BaseFacebook::$DOMAIN_MAP['api'] . 'method/pages.isFan?page_id=' . self::$appPageId . '&uid=' . $uFacebookId . '&access_token=' . $uAccessToken . '&format=json');
//
//			return ($tResult == 'true');
//		}
	}

	?>