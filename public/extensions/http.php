<?php

	class http {
		private static $platform = null;
		private static $crawler = null;
		private static $isAjax = false;
		private static $isBrowser = false;
		private static $isRobot = false;
		private static $isMobile = false;
		private static $languages = array();

		public static function extension_info() {
			return array(
				'name' => 'http',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array('string', 'io')
			);
		}
		
		public static function extension_load() {
			// session trans sid
			ini_set('session.use_trans_sid', '0');
			
			// required for IE in iframe facebook environments if sessions are to work.
			header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

			// replace missing environment variables
			static $aEnvNames = array(
				'HTTP_ACCEPT_LANGUAGE',
				'HTTP_HOST',
				'HTTP_USER_AGENT',
				'HTTP_REFERER',
				'PHP_SELF',
				'QUERY_STRING',
				'REQUEST_URI',
				'SERVER_ADDR',
				'SERVER_NAME',
				'SERVER_PORT'
			);

			foreach($aEnvNames as &$tEnv) {
				if(isset($_SERVER[$tEnv]) && strlen($_SERVER[$tEnv]) > 0) {
					continue;
				}

				$_SERVER[$tEnv] = getenv($tEnv) or $_SERVER[$tEnv] = '';
			}

			if(isset($_SERVER['HTTP_CLIENT_IP'])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if(!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else {
				$_SERVER['REMOTE_ADDR'] = getenv($tEnv) or $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
			}

			// phpself and query string
			$_SERVER['PHP_SELF'] = str_replace(array('<', '>'), array('%3C', '%3E'), $_SERVER['PHP_SELF']);
			$_SERVER['QUERY_STRING'] = self::xss($_SERVER['QUERY_STRING']);

			foreach(config::get('/http/rewriteList', array()) as $tRewriteList) {
				$tReturn = preg_replace('|^' . $tRewriteList['@match'] . '$|', $tRewriteList['@forward'], $_SERVER['QUERY_STRING'], -1, $tCount);
				if($tCount > 0) {
					$_SERVER['QUERY_STRING'] = $tReturn;
					break;
				}
			}

			$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];

			if(strlen($_SERVER['QUERY_STRING']) > 0) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}

			if(strlen($_SERVER['HTTP_HOST']) == 0) {
				$_SERVER['HTTP_HOST'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

				if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
					$_SERVER['HTTP_HOST'] .= $_SERVER['SERVER_PORT'];
				}
			}
			
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
				self::$isAjax = true;
			}
			
			foreach(config::get('/http/userAgents/platformList', array()) as $tPlatformList) {
				if(preg_match('/' . $tPlatformList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$platform = $tPlatformList['@name'];
					break;
				}
			}

			foreach(config::get('/http/userAgents/crawlerList', array()) as $tCrawlerList) {
				if(preg_match('/' . $tCrawlerList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
					self::$crawler = $tCrawlerList['@name'];
					
					switch($tCrawlerList['@type']) {
					case 'bot':
						self::$isRobot = true;
						break;
					case 'mobile':
						self::$isMobile = true;
						break;
					case 'browser':
					default:
						self::$isBrowser = true;
						break;
					}
					
					break;
				}
			}

			// self::$browser = get_browser(null, true);
			self::$languages = self::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			
			$tParsingType = Config::get('/http/request/@parsingType', '0');
			if($tParsingType == '1') {
				$tDefaultParameter = Config::get('/http/request/@getParameters', '&');
				$tDefaultKey = Config::get('/http/request/@getKeys', '=');

				if($tDefaultParameter != '&' || $tDefaultKey != '=') {
					self::parseGetType1($tDefaultParameter, $tDefaultKey);
					$tGetProcessed = true;
				}
			}
			else if($tParsingType == '2') {
				self::parseGetType2();
				$tGetProcessed = true;
			}

			if(get_magic_quotes_gpc()) {
				if(!isset($tGetProcessed)) {
					array_walk($_GET, array('http', 'magic_quotes_deslash'));
				}

				array_walk($_POST, array('http', 'magic_quotes_deslash'));
				array_walk($_COOKIE, array('http', 'magic_quotes_deslash'));
//				array_walk($_REQUEST, array('http', 'magic_quotes_deslash'));
			}

			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE); // GPC Order w/o session vars.
		}

		public static function xss($uString) {
			return str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); // '&' => '&#38;'
		}
		
		public static function encode($uString) {
			return urlencode($uString);
		}

		public static function decode($uString) {
			return urldecode($uString);
		}

		public static function sendStatus($uStatusCode) {
			switch((int)$uStatusCode) {
			case 100: $tStatus = 'HTTP/1.1 100 Continue'; break;
			case 101: $tStatus = 'HTTP/1.1 101 Switching Protocols'; break;
			case 200: $tStatus = 'HTTP/1.1 200 OK'; break;
			case 201: $tStatus = 'HTTP/1.1 201 Created'; break;
			case 202: $tStatus = 'HTTP/1.1 202 Accepted'; break;
			case 203: $tStatus = 'HTTP/1.1 203 Non-Authoritative Information'; break;
			case 204: $tStatus = 'HTTP/1.1 204 No Content'; break;
			case 205: $tStatus = 'HTTP/1.1 205 Reset Content'; break;
			case 206: $tStatus = 'HTTP/1.1 206 Partial Content'; break;
			case 300: $tStatus = 'HTTP/1.1 300 Multiple Choices'; break;
			case 301: $tStatus = 'HTTP/1.1 301 Moved Permanently'; break;
			case 302: $tStatus = 'HTTP/1.1 302 Found'; break;
			case 303: $tStatus = 'HTTP/1.1 303 See Other'; break;
			case 304: $tStatus = 'HTTP/1.1 304 Not Modified'; break;
			case 305: $tStatus = 'HTTP/1.1 305 Use Proxy'; break;
			case 307: $tStatus = 'HTTP/1.1 307 Temporary Redirect'; break;
			case 400: $tStatus = 'HTTP/1.1 400 Bad Request'; break;
			case 401: $tStatus = 'HTTP/1.1 401 Unauthorized'; break;
			case 402: $tStatus = 'HTTP/1.1 402 Payment Required'; break;
			case 403: $tStatus = 'HTTP/1.1 403 Forbidden'; break;
			case 404: $tStatus = 'HTTP/1.1 404 Not Found'; break;
			case 405: $tStatus = 'HTTP/1.1 405 Method Not Allowed'; break;
			case 406: $tStatus = 'HTTP/1.1 406 Not Acceptable'; break;
			case 407: $tStatus = 'HTTP/1.1 407 Proxy Authentication Required'; break;
			case 408: $tStatus = 'HTTP/1.1 408 Request Timeout'; break;
			case 409: $tStatus = 'HTTP/1.1 409 Conflict'; break;
			case 410: $tStatus = 'HTTP/1.1 410 Gone'; break;
			case 411: $tStatus = 'HTTP/1.1 411 Length Required'; break;
			case 412: $tStatus = 'HTTP/1.1 412 Precondition Failed'; break;
			case 413: $tStatus = 'HTTP/1.1 413 Request Entity Too Large'; break;
			case 414: $tStatus = 'HTTP/1.1 414 Request-URI Too Long'; break;
			case 415: $tStatus = 'HTTP/1.1 415 Unsupported Media Type'; break;
			case 416: $tStatus = 'HTTP/1.1 416 Requested Range Not Satisfiable'; break;
			case 417: $tStatus = 'HTTP/1.1 417 Expectation Failed'; break;
			case 500: $tStatus = 'HTTP/1.1 500 Internal Server Error'; break;
			case 501: $tStatus = 'HTTP/1.1 501 Not Implemented'; break;
			case 502: $tStatus = 'HTTP/1.1 502 Bad Gateway'; break;
			case 503: $tStatus = 'HTTP/1.1 503 Service Unavailable'; break;
			case 504: $tStatus = 'HTTP/1.1 504 Gateway Timeout'; break;
			case 505: $tStatus = 'HTTP/1.1 505 HTTP Version Not Supported'; break;
			default:
				return;
			}

			self::sendHeader($tStatus);
		}

		public static function sendHeader($uHeader, $uValue = null, $uReplace = false) {
			if(isset($uValue)) {
				header($uHeader . ': ' . $uValue, $uReplace);
			}
			else {
				header($uHeader, $uReplace);
			}
		}

		public static function sendFile($uFilePath, $uAttachment = false, $uFindMimeType = true) {
			$tExtension = pathinfo($uFilePath, PATHINFO_EXTENSION);

			if($uFindMimeType) {
				$tType = io::getMimeType($tExtension);
			}
			else {
				$tType = 'application/octet-stream';
			}

			self::sendHeaderExpires(0); // 1970
			self::sendHeaderNoCache();
			// self::sendHeader('Accept-Ranges', 'bytes', true);
			self::sendHeader('Content-Type', $tType, true);
			if($uAttachment) {
				self::sendHeader('Content-Disposition', 'attachment; filename=' . basename($uFilePath) . ';', true);
			}
			self::sendHeader('Content-Transfer-Encoding', 'binary', true);
			self::sendHeader('Content-Length', filesize($uFilePath), true);
			self::sendHeaderETag(md5_file($uFilePath));
			@readfile($uFilePath);
			exit();
		}

		public static function sendHeaderLastModified($uTime, $uNotModified = false) {
			self::sendHeader('Last-Modified', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);

			if($uNotModified) {
				self::sendStatus(304);
			}
		}

		public static function sendHeaderExpires($uTime) {
			self::sendHeader('Expires', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);
		}

		public static function sendRedirect($uLocation, $uTerminate = true) {
			self::sendHeader('Location', $uLocation, true);

			if($uTerminate) {
				exit();
			}
		}

		public static function sendHeaderETag($uHash) {
			self::sendHeader('ETag', '"' . $uHash . '"', true);
		}

		public static function sendHeaderNoCache() {
			self::sendHeader('Pragma', 'public', true);
			self::sendHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
			self::sendHeader('Cache-Control', 'pre-check=0, post-check=0, max-age=0');
		}

		public static function sendCookie($uCookie, $uValue, $uExpire = 0) {
			setrawcookie($uCookie, self::encode($uValue), $uExpire);
		}
		
		public static function parseGetType1($uParameters = '&', $uKeys = '=') {
			$_GET = string::parseQueryString($_SERVER['QUERY_STRING'], $uParameters, $uKeys);
		}

		public static function parseGetType2($uSeperator = '/') {
//			if(strlen($_SERVER['QUERY_STRING']) == 0) {
//				$_GET = array();
//				return;
//			}
			
			$_GET = explode($uSeperator, $_SERVER['QUERY_STRING']);
		}
		
		public static function parseHeaderString($uString) {
			$tResult = array();

			foreach(explode(',', $uString) as $tPiece) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$tPiece = trim($tPiece);
				$tResult[] = substr($tPiece, 0, strcspn($tPiece, ';'));
			}

			return $tResult;
		}

		private static function magic_quotes_deslash(&$uItem) {
			switch(gettype($uItem)) {
			case 'array':
				array_walk($uItem, 'magic_quotes_deslash'); break;
			case 'string':
				$uItem = stripslashes($uItem); break;
			}
		}
		
		public static function getPlatform() {
			return self::$platform;
		}

		public static function getCrawler() {
			return self::$crawler;
		}

		public static function getIsAjax() {
			return self::$isAjax;
		}

		public static function getIsBrowser() {
			return self::$isBrowser;
		}

		public static function getIsRobot() {
			return self::$isRobot;
		}

		public static function getIsMobile() {
			return self::$isMobile;
		}

		public static function getLanguages() {
			return self::$languages;
		}

//		public static function extension_generateOutput() {
//			foreach($_COOKIE as $tCookie => &$tValue) {
//				if($tValue == '') {
//					setrawcookie($tCookie, urlencode($tValue), time() - 60*60);
//				} else {
//					setrawcookie($tCookie, urlencode($tValue), time() + 60*60);
//				}
//			}
//		}
	}

?>
