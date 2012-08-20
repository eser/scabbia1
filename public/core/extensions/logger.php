<?php

if(extensions::isSelected('logger')) {
	/**
	* Logger Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	class logger {
		/**
		* @ignore
		*/
		public static $filename;
		/**
		* @ignore
		*/
		public static $line;

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'logger',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'http')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$filename = config::get('/logger/@filename', '{date|\'d-m-Y\'}.txt');
			self::$line = config::get('/logger/@line', '[{date|\'d-m-Y H:i:s\'}] {strtoupper|@category} | {@ip} | {@message}');

			set_exception_handler('logger::exceptionCallback');
			set_error_handler('logger::errorCallback', E_ALL);
			// ini_set('display_errors', '1');
			// ini_set('track_errors', '1');
			// ini_set('html_errors', '0');
		}

		/**
		* @ignore
		*/
		public static function errorCallback($uCode, $uMessage, $uFile, $uLine) {
			self::handler(
				$uMessage,
				$uCode,
				$uFile,
				$uLine
			);
		}

		/**
		* @ignore
		*/
		public static function exceptionCallback($uException) {
			self::handler(
				$uException->getMessage(),
				$uException->getCode(),
				$uException->getFile(),
				$uException->getLine()
			);
		}
		
		/**
		* @ignore
		*/
		public static function handler($uMessage, $uCode, $uFile, $uLine) {
			switch($uCode) {
				case E_ERROR:
				case E_USER_ERROR:
				case E_RECOVERABLE_ERROR:
					$tType = 'Error';
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$tType = 'Warning';
					break;
				case E_NOTICE:
				case E_USER_NOTICE:
					$tType = 'Notice';
					break;
				case E_STRICT:
					$tType = 'Strict';
					break;
				// case E_DEPRECATED: // PHP >= 5.3.0
				case 8192:
				// case E_USER_DEPRECATED: // PHP >= 5.3.0
				case 16384:
					break;
				default:
					$tType = 'Unknown';
					break;
			}

			$tIgnoreError = false;
			events::invoke('reportError', array(
				'type' => &$tType,
				'message' => $uMessage,
				'file' => $uFile,
				'line' => $uLine,
				'ignore' => &$tIgnoreError
			));

			if(!$tIgnoreError) {
				http::sendStatus(500);
				http::sendHeader('Content-Type', 'text/html', true);

				events::setDisabled(true);
				$tEventDepth = events::getEventDepth();

				for($tCount = ob_get_level(); --$tCount > 1;ob_end_flush());

				if(framework::$development >= 1) {
					$tDeveloperLocation = $uFile . ' @' . $uLine;
				}
				else {
					$tDeveloperLocation = pathinfo($uFile, PATHINFO_FILENAME);
				}

				$tString = '';
				$tString .= '<div>'; // for content-type: text/xml
				$tString .= '<div style="font: 11pt \'Lucida Sans Unicode\'; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $tType . '</span>: ' . $tDeveloperLocation . '</div>' . string::$eol . string::$eol;
				$tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #404040; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;">' . $uMessage . '</div>' . string::$eol . string::$eol;

				if(framework::$development >= 1) {
					if(count($tEventDepth) > 0) {
						$tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>eventDepth:</b><br />' . string::$eol . implode(string::$eol, $tEventDepth) . '</div>' . string::$eol . string::$eol;
					}

					$tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>stackTrace:</b><br />' . string::$eol;

					$tCount = 0;
					foreach(debug_backtrace() as $tFrame) {
						$tArgs = array();
						if(isset($tFrame['args'])) {
							foreach($tFrame['args'] as $tArg) {
								$tArgs[] = var_export($tArg, true);
							}
						}

						if(isset($tFrame['class'])) {
							$tFunction = $tFrame['class'] . $tFrame['type'] . $tFrame['function'];
						}
						else {
							$tFunction = $tFrame['function'];
						}

						$tCount++;
						if(isset($tFrame['file'])) {
							$tString .= '#' . $tCount . ' ' . $tFrame['file'] . '(' . $tFrame['line'] . '):<br />' . string::$eol;
						}

						$tString .= '#' . $tCount . ' <strong>' . $tFunction . '</strong>(' . implode(', ', $tArgs) . ')<br /><br />' . string::$eol . string::$eol;
					}

					$tString .= '</div>' . string::$eol;

					if(extensions::isSelected('profiler')) {
						$tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>profiler stack:</b><br />' . string::$eol;
						$tString .= profiler::exportStack(false);
						$tString .= '</div>' . string::$eol;

						$tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>profiler output:</b><br />' . string::$eol;
						$tString .= profiler::export(false);
						$tString .= '</div>';
					}
				}

				$tString .= '</div>';

				self::write('error', array('message' => strip_tags($tString)));

				$tString .= '<div style="font: 7pt \'Lucida Sans Unicode\'; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="mailto:eser@sent.com">' . ucfirst(INCLUDED) . '</a>.</div>' . string::$eol;
				echo $tString;

				exit();
			}
		}

		/**
		* @ignore
		*/
		public static function write($uCategory, $uParams) {
			$uParams['category'] = &$uCategory;
			$uParams['ip'] = $_SERVER['REMOTE_ADDR'];

			$uParams['message'] = string::prefixLines($uParams['message'], '- ', "\n");

			$tFilename = framework::writablePath('logs/' . string::format(self::$filename, $uParams));
			$tContent = '+ ' . string::format(self::$line, $uParams);

			file_put_contents($tFilename, $tContent, FILE_APPEND);
		}
	}
}

?>