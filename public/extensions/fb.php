<?php

if(extensions::isSelected('fb')) {
	/**
	* FB Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*
	* @todo direct api query like /me/home
	*/
	class fb {
		public static $appId;
		public static $appSecret;
		public static $appFileUpload;
		public static $appUrl;
		public static $appPageId;
		public static $appRedirectUri;
		public static $api = null;
		public static $userId = null;

		public static function extension_info() {
			return array(
				'name' => 'fb',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('session', 'http')
			);
		}

		public static function extension_load() {
			self::$appId = config::get('/facebook/APP_ID/.');
			self::$appSecret = config::get('/facebook/APP_SECRET/.');
			self::$appFileUpload = config::get('/facebook/APP_FILEUPLOAD/.');
			self::$appUrl = config::get('/facebook/APP_URL/.');
			self::$appPageId = config::get('/facebook/APP_PAGE_ID/.');
			self::$appRedirectUri = config::get('/facebook/APP_REDIRECT_URI/.');
		}

		public static function loadApi() {
			if(is_null(self::$api)) {
				self::$api = new Facebook(array(
					'appId'			=> self::$appId,
					'secret'		=> self::$appSecret,
					'cookie'		=> true,
					'fileUpload'	=> (self::$appFileUpload == '1')
				));
			}

			self::$userId = self::$api->getUser();

			$tUserId = session::get('fbUserId', null);
			if(is_null($tUserId) || self::$userId != intval($tUserId)) {
				self::resetSession();
			}
		}

		public static function resetSession() {
			session::remove('fbUser');
			session::remove('fbUserAccessToken');
			session::remove('fbUserPermissions');
			session::remove('fbUserLikes');

			session::set('fbUserId', self::$userId);
		}
		
		public static function getUserId() {
			return self::$userId;
		}

		public static function getUserAccessToken() {
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
 
			return $tUserAccessToken;
		}

		public static function getLoginUrl($uPermissions, $uRedirectUri = null) {
			$tLoginUrl = self::$api->getLoginUrl(array(
				'scope' => $uPermissions,
				'redirect_uri' => string::coalesce($uRedirectUri, self::$appRedirectUri)
			));
			
			return $tLoginUrl;
		}

		public static function checkLogin($uPermissions, $uRequiredPermissions = null, $uRedirectUri = null) {
			if(
				self::$userId == 0 ||
				(!is_null($uRequiredPermissions) && strlen($uRequiredPermissions) > 0 && !self::checkUserPermission($uRequiredPermissions))
			) {
				$tLoginUrl = self::getLoginUrl($uPermissions, $uRedirectUri);
				http::sendRedirect($tLoginUrl, true);
			}
		}

		public static function checkUserPermission($uPermissions) {
			if(self::$userId == 0) {
				return false;
			}

			$tUserPermissions = session::get('fbUserPermissions', null);
			if(is_null($tUserPermissions)) {
				try {
					$tUserPermissions = self::$api->api('/me/permissions');
					session::set('fbUserPermissions', $tUserPermissions);
				}
				catch(FacebookApiException $tException) {
					return false;
				}
			}

			foreach(explode(',', $uPermissions) as $tPermission) {
				if(!array_key_exists($tPermission, $tUserPermissions['data'][0])) {
					return false;
				}
			}

			return true;
		}

		public static function checkLike($uId) {
			if(self::$userId == 0) {
				return false;
			}

			$tLikeResponse = self::$api->api('/me/likes/' . $uId);

			if(!empty($tLikeResponse['data'])) {
				return true;
			}

			return false;
		}

		public static function getUser() {
			if(self::$userId == 0) {
				return false;
			}

			$tUser = session::get('fbUser', null);
			if(is_null($tUser)) {
				try {
					$tUser = self::$api->api('/me');
					session::set('fbUser', $tUser);
				}
				catch(FacebookApiException $tException) {
					return false;
				}
			}

			return $tUser;
		}
		
		public static function getUserLikes() {
			if(self::$userId == 0) {
				return false;
			}

			$tUserLikes = session::get('fbUserLikes', null);
			try {
				$tUserLikes = self::$api->api('/me/likes');
				session::set('fbUserLikes', $tUserLikes);
			}
			catch(FacebookApiException $tException) {
				return false;
			}

			return $tUserLikes;
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
	
	/**
	 * Extends the BaseFacebook class with the intent of using
	 * PHP sessions to store user ids and access tokens.
	 *
	 * @ignore -- Scabbia
	 */
	class Facebook extends BaseFacebook {
		/**
		* Identical to the parent constructor, except that
		* we start a PHP session to store the user ID and
		* access token if during the course of execution
		* we discover them.
		*
		* @param Array $config the application configuration.
		* @see BaseFacebook::__construct in facebook.php
		*/
		public function __construct($config) {
			parent::__construct($config);
		}

		protected static $kSupportedKeys = array('state', 'code', 'access_token', 'user_id');

		/**
		* Provides the implementations of the inherited abstract
		* methods.  The implementation uses PHP sessions to maintain
		* a store for authorization codes, user ids, CSRF states, and
		* access tokens.
		*/
		protected function setPersistentData($key, $value) {
			if(!in_array($key, self::$kSupportedKeys)) {
				self::errorLog('Unsupported key passed to setPersistentData.');
				return;
			}

			$session_var_name = $this->constructSessionVariableName($key);

			session::set($session_var_name, $value);
		}

		protected function getPersistentData($key, $default = false) {
			if(!in_array($key, self::$kSupportedKeys)) {
				self::errorLog('Unsupported key passed to getPersistentData.');

				return $default;
			}

			$session_var_name = $this->constructSessionVariableName($key);

			return session::get($session_var_name, $default);
		}

		protected function clearPersistentData($key) {
			if(!in_array($key, self::$kSupportedKeys)) {
				self::errorLog('Unsupported key passed to clearPersistentData.');
				return;
			}

			$session_var_name = $this->constructSessionVariableName($key);
			session::remove($session_var_name);
		}

		protected function clearAllPersistentData() {
			foreach(self::$kSupportedKeys as $key) {
				$this->clearPersistentData($key);
			}
		}

		protected function constructSessionVariableName($key) {
			return implode('_', array('fb', $this->getAppId(), $key));
		}
	}
}

?>