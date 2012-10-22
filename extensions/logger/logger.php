<?php

if(extensions::isSelected('logger')) {
	/**
	* Logger Extension
	*
	* @package Scabbia
	* @subpackage logger
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
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
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$filename = config::get(config::MAIN, '/logger/filename', '{date|\'d-m-Y\'}.txt');
			self::$line = config::get(config::MAIN, '/logger/line', '[{date|\'d-m-Y H:i:s\'}] {strtoupper|@category} | {@ip} | {@message}');

			set_exception_handler('logger::exceptionCallback');
			set_error_handler('logger::errorCallback', E_ALL);
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
				header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
				header('Content-Type: text/html, charset=UTF-8', true);

				events::$disabled = true;
				$tEventDepth = events::$eventDepth;

				for($tCount = ob_get_level(); --$tCount > 1;ob_end_flush());

				if(framework::$development >= 1) {
					$tDeveloperLocation = $uFile . ' @' . $uLine;
				}
				else {
					$tDeveloperLocation = pathinfo($uFile, PATHINFO_FILENAME);
				}

				$tString = '';
				$tString .= '<div>'; // for content-type: text/xml
				$tString .= '<div style="font-size: 11pt; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $tType . '</span>: ' . $tDeveloperLocation . '</div>' . PHP_EOL . PHP_EOL;
				$tString .= '<div style="font-size: 10pt; color: #404040; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;">' . $uMessage . '</div>' . PHP_EOL . PHP_EOL;

				if(framework::$development >= 1) {
					if(count($tEventDepth) > 0) {
						$tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>eventDepth:</b><br />' . PHP_EOL . implode(PHP_EOL, $tEventDepth) . '</div>' . PHP_EOL . PHP_EOL;
					}

					$tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>stackTrace:</b><br />' . PHP_EOL;

					$tCount = 0;
					foreach(array_slice(debug_backtrace(), 2) as $tFrame) {
						$tArgs = array();
						if(isset($tFrame['args'])) {
							/*foreach($tFrame['args'] as $tArg) {
								$tArgs[] = var_export($tArg, true);
							}*/
						}

						if(isset($tFrame['class'])) {
							$tFunction = $tFrame['class'] . $tFrame['type'] . $tFrame['function'];
						}
						else {
							$tFunction = $tFrame['function'];
						}

						$tCount++;
						if(isset($tFrame['file'])) {
							$tString .= '#' . $tCount . ' ' . $tFrame['file'] . '(' . $tFrame['line'] . '):<br />' . PHP_EOL;
						}

						$tString .= '#' . $tCount . ' <strong>' . $tFunction . '</strong>(' . implode(', ', $tArgs) . ')<br /><br />' . PHP_EOL . PHP_EOL;
					}

					$tString .= '</div>' . PHP_EOL;

					if(extensions::isSelected('profiler')) {
						$tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>profiler stack:</b><br />' . PHP_EOL;
						$tString .= profiler::exportStack(false);
						$tString .= '</div>' . PHP_EOL;

						$tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>profiler output:</b><br />' . PHP_EOL;
						$tString .= profiler::export(false);
						$tString .= '</div>';
					}
				}

				$tString .= '</div>';

				self::write('error', array('message' => strip_tags($tString)));

				$tString .= '<div style="font-size: 7pt; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="mailto:eser@sent.com">Scabbia ' . SCABBIA_VERSION . '</a>.</div>' . PHP_EOL;
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

			$uParams['message'] = string::prefixLines($uParams['message'], '- ', PHP_EOL);

			$tFilename = framework::writablePath('logs/' . string::format(self::$filename, $uParams));
			$tContent = '+ ' . string::format(self::$line, $uParams);

			file_put_contents($tFilename, $tContent, FILE_APPEND);
		}
	}
}

?>