<?php

if(extensions::isSelected('fb')) {
	class fb {
		public static $appId;
		public static $appSecret;
		public static $appUrl;
		public static $appPageId;
		public static $appRedirectUri;
		public static $api = null;
		public static $userId = null;
		public static $userPermissions = null;
		public static $user = null;

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
			self::$appUrl = config::get('/facebook/APP_URL/.');
			self::$appPageId = config::get('/facebook/APP_PAGE_ID/.');
			self::$appRedirectUri = config::get('/facebook/APP_REDIRECT_URI/.');
		}

		public static function loadApi() {
			if(is_null(self::$api)) {
				self::$api = new Facebook(array(
					'appId'			=> self::$appId,
					'secret'		=> self::$appSecret,
					'cookie'		=> true
				));
			}
		}

		public static function getUserId() {
			if(is_null(self::$userId)) {
				self::$userId = self::$api->getUser();

				if(!self::$userId) {
					self::$userId = null;
				}
			}
 
			return self::$userId;
		}

		public static function checkLogin($uPermissions, $uRequiredPermissions = null) {
			if(is_null($uRequiredPermissions)) {
				$uRequiredPermissions = $uPermissions;
			}

			if(!self::checkUserPermission($uRequiredPermissions)) {
				$tLoginUrl = self::$api->getLoginUrl(array(
					'scope' => $uPermissions,
					'redirect_uri' => self::$appRedirectUri
				));

				http::sendRedirect($tLoginUrl);
				return false;
			}

			return true;
		}

		public static function checkUserPermission($uPermissions) {
			if(is_null(self::getUserId())) {
				self::$user = null;
				return false;
			}
			else if(is_null(self::$userPermissions)) {
				try {
					self::$userPermissions = self::$api->api('/me/permissions');
				}
				catch(FacebookApiException $tException) {
					self::$userPermissions = null;
					return false;
				}
			}

			foreach(explode(',', $uPermissions) as $tPermission) {
				if(!array_key_exists($tPermission, self::$userPermissions['data'][0])) {
					return false;
				}
			}

			return true;
		}

		public static function checkLike($uId) {
			if(is_null(self::getUserId())) {
				self::$user = null;
				return false;
			}
			else {
				$tLikeResponse = self::$api->api('/me/likes/' . $uId);

				if(!empty($tLikeResponse['data'])) {
					return true;
				}
			}

			return false;
		}

		public static function getUser() {
			if(is_null(self::getUserId())) {
				self::$user = null;
			}
			else if(is_null(self::$user)) {
				try {
					self::$user = self::$api->api('/me');
				}
				catch(FacebookApiException $tException) {
					self::$user = null;
				}
			}

			return self::$user;
		}
		
		public static function getUserLikes() {
			try {
				$tReturn = self::$api->api('/me/likes');
			}
			catch(FacebookApiException $tException) {
				$tReturn = null;
			}
			
			return $tReturn;
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