<?php
				ignore_user_abort();
				date_default_timezone_set('UTC');
				setlocale(LC_ALL, 'en_US.UTF-8');
				mb_internal_encoding('UTF-8');
				mb_http_output('UTF-8');

				define('PHP_OS_WINDOWS', true);
				define('PHP_SAPI_CLI', (PHP_SAPI == 'cli'));
				define('QPATH_CORE', 'C:\\inetpub\\wwwroot\\blackmorep2\\');
				define('QPATH_APP', 'C:\\inetpub\\wwwroot\\blackmorep2\\application/');
				define('QTIME_INIT', microtime(true));
				define('QEXT_PHP', '.php');

				define('SCABBIA_VERSION', '1.0.15411');
				define('INCLUDED', 'Scabbia 1.0.15411');
				define('COMPILED', true);

				define('OUTPUT_NOHANDLER', true);
				define('OUTPUT_GZIP', true);
				define('OUTPUT_MULTIBYTE', true);
			?><?php
 if(!function_exists('fnmatch')) { function fnmatch($uPattern, $uString) { for($tBrackets = 0, $tPregPattern = '', $tCount = 0, $tLen = strlen($uPattern); $tCount < $tLen; $tCount++) { $tChar = $uPattern[$tCount]; if(strpbrk($tChar, '\\')) { $tPregPattern .= '\\' . @$uPattern[++$tCount]; } else if(strpbrk($tChar, '-+^$=!.|(){}<>')) { $tPregPattern .= '\\' . $tChar; } else if(strpbrk($tChar, '?*')) { $tPregPattern .= '.' . $tChar; } else { $tPregPattern .= $tChar; if($tChar == '[') { $tBrackets++; } else if($tChar == ']') { if($tBrackets == 0) { return false; } $tBrackets--; } } } if($tBrackets != 0) { return false; } return preg_match('/' . $tPregPattern . '/i', $uString); } } ?><?php
 class Config { private static $default = null; public static function processChildrenAsArray_r(&$uArray, $uNode, $tListElement = null) { foreach($uNode->children() as $tKey => $tNode) { if(!is_null($tListElement) && $tListElement == $tKey) { self::processChildrenAsArray_r($uArray[], $tNode, null); } else { if(substr($tKey, -4) == 'List') { self::processChildrenAsArray_r($uArray[$tKey], $tNode, substr($tKey, 0, -4)); } else { self::processChildrenAsArray_r($uArray[$tKey], $tNode, null); } } } foreach($uNode->attributes() as $tKey => $tValue) { $uArray['@' . $tKey] = (string)$tValue; } $tNodeValue = rtrim((string)$uNode); if(strlen($tNodeValue) > 0) { $uArray['.'] = $tNodeValue; } else if($tListElement == null) { $uArray['.'] = null; } } public static function processChildren_r(&$uArray, $uPrefix, $uNode) { foreach($uNode->children() as $tKey => $tNode) { $tArrayKey = $uPrefix . '/' . $tKey; if(substr($tKey, -4) == 'List') { if(!isset($uArray[$tArrayKey]) || !is_array($uArray[$tArrayKey])) { $uArray[$tArrayKey] = array(); } self::processChildrenAsArray_r($uArray[$tArrayKey], $tNode, substr($tKey, 0, -4)); continue; } self::processChildren_r($uArray, $tArrayKey, $tNode); } foreach($uNode->attributes() as $tKey => $tValue) { $uArray[$uPrefix . '/@' . $tKey] = (string)$tValue; } $tNodeValue = rtrim((string)$uNode); if(strlen($tNodeValue) > 0) { $uArray[$uPrefix . '/.'] = $tNodeValue; } } public static function &loadFiles($uFiles) { if(isset($_SERVER['SERVER_NAME'])) { $tSocket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']; } else { $tSocket = 'localhost:80'; } $tXmlSource = ''; foreach(glob($uFiles, GLOB_MARK|GLOB_NOSORT) as $tFilename) { if(substr($tFilename, -1) == '/') { continue; } $tXml = simplexml_load_file($tFilename) or exit('Unable to read from config file - ' . $tFilename); if(isset($tXml->scope)) { foreach($tXml->scope as $tScope) { if(fnmatch((string)$tScope['binding'], $tSocket)) { foreach($tScope->children() as $tNode) { $tXmlSource .= $tNode->asXML(); } } } } else { foreach($tXml->children() as $tNode) { $tXmlSource .= $tNode->asXML(); } } } $tConfigDom = simplexml_load_string('<scabbia>' . $tXmlSource . '</scabbia>', null, LIBXML_NOBLANKS|LIBXML_NOCDATA); $tConfig = array(); self::processChildren_r($tConfig, '', $tConfigDom); return $tConfig; } public static function load() { self::$default = self::loadFiles(QPATH_APP . 'config/*'); } public static function &get($uKey, $uDefault = null) { if(!array_key_exists($uKey, self::$default)) { return $uDefault; } return self::$default[$uKey]; } public static function set($uVariable) { self::$default = $uVariable; } public static function dump() { var_dump(self::$default); } public static function export() { return var_export(self::$default, true); } } ?>
<?php
 class Events { private static $callbacks = array(); private static $eventDepth = array(); private static $disabled = false; public static function register($uEventName, $uCallback) { if(!array_key_exists($uEventName, self::$callbacks)) { self::$callbacks[$uEventName] = array(); } self::$callbacks[$uEventName][] = $uCallback; } public static function invoke($uEventName, $uEventArgs = array()) { if(self::$disabled) { return; } if(!array_key_exists($uEventName, self::$callbacks)) { return; } foreach(self::$callbacks[$uEventName] as &$tCallback) { if(is_array($tCallback)) { $tCallname = array(get_class($tCallback[0]), $tCallback[1]); } else { $tCallname = array('GLOBALS', $tCallback); } $tKey = $tCallname[0] . '::' . $tCallname[1]; array_push(self::$eventDepth, $tKey . '()'); if(call_user_func($tCallback, $uEventArgs) === false) { break; } array_pop(self::$eventDepth); } } public static function setDisabled($uDisabled) { self::$disabled = $uDisabled; } public static function getEventDepth() { return self::$eventDepth; } public static function Callback($uCallbackMethod, &$uCallbackObject = null) { if(func_num_args() >= 2) { return array(&$uCallbackObject, $uCallbackMethod); } return $uCallbackMethod; } } ?>
<?php
 class Framework { public static $includePaths = array(); public static $downloadUrls = array(); public static $development; public static $debug; public static $siteroot; public static function translatePath($uPath) { if(substr($uPath, 0, 6) == '{core}') { return QPATH_CORE . substr($uPath, 6); } if(substr($uPath, 0, 5) == '{app}') { return QPATH_APP . substr($uPath, 5); } return $uPath; } public static function load() { self::$development = (bool)Config::get('/options/development/@value', '0'); self::$debug = (bool)Config::get('/options/debug/@value', '0'); self::$siteroot = Config::get('/options/siteroot/@value', null); if(!isset(self::$siteroot)) { $tLen = strlen($_SERVER['DOCUMENT_ROOT']); if(substr(QPATH_CORE, 0, $tLen) == $_SERVER['DOCUMENT_ROOT']) { self::$siteroot = rtrim(strtr(substr(QPATH_CORE, $tLen), DIRECTORY_SEPARATOR, '/'), '/'); } else { self::$siteroot = ''; } } if(!COMPILED) { $tDownloads = Config::get('/downloadList', array()); foreach($tDownloads as &$tDownload) { self::$downloadUrls[$tDownload['@filename']] = $tDownload['@url']; } self::downloadFiles(); $tIncludes = Config::get('/includeList', array()); foreach($tIncludes as &$tInclude) { self::$includePaths[] = self::translatePath($tInclude['@path']); } self::includeFilesFromConfig(); } } public static function output($uValue, $uSecond) { $tParms = array( 'content' => &$uValue ); Events::invoke('output', $tParms); if(OUTPUT_MULTIBYTE) { $tParms['content'] = mb_output_handler($tParms['content'], $uSecond); } if(OUTPUT_GZIP && !PHP_SAPI_CLI && Config::get('/options/gzip/@value', '1') != '0') { $tParms['content'] = ob_gzhandler($tParms['content'], $uSecond); } return $tParms['content']; } public static function run() { ob_start('Framework::output'); ob_implicit_flush(false); if(!COMPILED) { foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $tValue) { if(isset($tValue['type']) && $tValue['type'] == 'include') { $tIncluded = true; } } if(PHP_SAPI_CLI) { $tParameters = implode(' ', array_slice($GLOBALS['argv'], 1)); } else { $tParameters = $_SERVER['QUERY_STRING']; } if(!isset($tIncluded)) { if(self::$development) { if($tParameters == 'build') { self::build('index.php'); self::purgeFolder(QPATH_APP . 'temp'); self::purgeFolder(QPATH_APP . 'sessions'); echo 'build done.'; return; } } } } Events::invoke('run', array()); } public static function downloadFiles() { foreach(self::$downloadUrls as $tFilename => &$tUrl) { self::downloadFile($tFilename, $tUrl); } } public static function downloadFile($uFile, $uUrl) { $tFilePath = QPATH_APP . 'downloaded/' . $uFile; if(file_exists($tFilePath)) { return false; } $tHandle = fopen($tFilePath, 'w'); $tContent = file_get_contents($uUrl); fwrite($tHandle, $tContent); fclose($tHandle); return true; } private static function includeFilesFromConfig() { foreach(self::$includePaths as &$tPath) { foreach(glob($tPath, GLOB_MARK|GLOB_NOSORT) as $tFilename) { if(substr($tFilename, -1) == '/') { continue; } require($tFilename); } } } private static function printIncludeFilesFromConfig() { foreach(self::$includePaths as &$tPath) { self::printFiles(glob($tPath, GLOB_MARK|GLOB_NOSORT)); } } public static function printFiles($uArray) { foreach($uArray as &$tFilename) { if(substr($tFilename, -1) == '/') { continue; } echo php_strip_whitespace($tFilename); } } public static function build($uFilename) { ob_start(); ob_implicit_flush(false); echo '<', '?php
				ignore_user_abort();
				date_default_timezone_set(\'UTC\');
				setlocale(LC_ALL, \'en_US.UTF-8\');
				mb_internal_encoding(\'UTF-8\');
				mb_http_output(\'UTF-8\');

				define(\'PHP_OS_WINDOWS\', ', var_export(PHP_OS_WINDOWS), ');
				define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
				define(\'QPATH_CORE\', ', var_export(QPATH_CORE), ');
				define(\'QPATH_APP\', ', var_export(QPATH_APP), ');
				define(\'QTIME_INIT\', microtime(true));
				define(\'QEXT_PHP\', ', var_export(QEXT_PHP), ');

				define(\'SCABBIA_VERSION\', ', var_export(SCABBIA_VERSION), ');
				define(\'INCLUDED\', ', var_export(INCLUDED), ');
				define(\'COMPILED\', true);

				define(\'OUTPUT_NOHANDLER\', ', var_export(OUTPUT_NOHANDLER), ');
				define(\'OUTPUT_GZIP\', ', var_export(OUTPUT_GZIP), ');
				define(\'OUTPUT_MULTIBYTE\', ', var_export(OUTPUT_MULTIBYTE), ');
			?', '>'; echo php_strip_whitespace(QPATH_CORE . 'include/patches.main' . QEXT_PHP); echo php_strip_whitespace(QPATH_CORE . 'include/config.main' . QEXT_PHP); echo php_strip_whitespace(QPATH_CORE . 'include/events.main' . QEXT_PHP); echo php_strip_whitespace(QPATH_CORE . 'include/framework.main' . QEXT_PHP); echo php_strip_whitespace(QPATH_CORE . 'include/extensions.main' . QEXT_PHP); self::printIncludeFilesFromConfig(); echo '<', '?php Config::set(', Config::export(), '); Framework::load(); Extensions::load(); Framework::run(); ?', '>'; $tContents = ob_get_contents(); ob_end_clean(); $tOutput = fopen($uFilename, 'w') or exit('Unable to write to ' . $uFilename); fwrite($tOutput, $tContents); fclose($tOutput); } public static function purgeFolder($uFolder) { foreach(glob($uFolder . '/*', GLOB_MARK|GLOB_NOSORT) as $tFilename) { if(substr($tFilename, -1) == '/') { continue; } unlink($tFilename); } } } ?><?php
 class Extensions { private static $loaded = array(); public static function load() { $tExtensions = Config::get('/extensionList', array()); foreach($tExtensions as &$tExtension) { self::add($tExtension['@name']); } } public static function add($uExtensionName) { if(in_array($uExtensionName, self::$loaded)) { return true; } if(!class_exists($uExtensionName)) { throw new Exception('extension class not loaded - ' . $uExtensionName); } self::$loaded[] = $uExtensionName; $tClassInfo = call_user_func(array($uExtensionName, 'extension_info')); if(!COMPILED) { if(isset($tClassInfo['phpversion']) && version_compare(PHP_VERSION, $tClassInfo['phpversion'], '<')) { return false; } if(isset($tClassInfo['phpdepends'])) { foreach($tClassInfo['phpdepends'] as &$tExtension) { if(!extension_loaded($tExtension)) { throw new Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName); } } } if(isset($tClassInfo['fwversion']) && version_compare(SCABBIA_VERSION, $tClassInfo['fwversion'], '<')) { return false; } if(isset($tClassInfo['fwdepends'])) { foreach($tClassInfo['fwdepends'] as &$tExtension) { if(!in_array($tExtension, self::$loaded)) { throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName); } } } } if(method_exists($uExtensionName, 'extension_load')) { call_user_func(array($uExtensionName, 'extension_load')); } return true; } public static function dump() { var_dump(self::$loaded); } public static function getAll() { return self::$loaded; } } ?>
<?php
 class collections { public static function extension_info() { return array( 'name' => 'collections', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } } class Collection implements ArrayAccess, IteratorAggregate { public $id; public $tag; public function __construct($tArray = null) { $this->id = null; $this->tag = array(); $this->tag['items'] = is_array($tArray) ? $tArray : array(); $this->tag['class'] = get_class($this); } public function add($uItem) { $this->tag['items'][] = $uItem; } public function addKey($uKey, $uItem) { $this->tag['items'][$uKey] = $uItem; } public function addRange($uItems) { foreach($uItems as &$tItem) { $this->add($tItem); } } public function addKeyRange($uItems) { foreach($uItems as $tKey => &$tItem) { $this->addKey($tKey, $tItem); } } public function keyExists($uKey, $uNullValue = true) { if($uNullValue) { return array_key_exists($uKey, $this->tag['items']); } return isset($this->tag['items'][$uKey]); } public function contains($uItem) { foreach($this->tag['items'] as &$tItem) { if($uItem == $tItem) { return true; } } return false; } public function count($uItem = null) { if(!isset($uItem)) { return count($this->tag['items']); } $tCounted = 0; foreach($this->tag['items'] as &$tItem) { if($uItem != $tItem) { continue; } $tCounted++; } return $tCounted; } public function countRange($uItems) { $tCounted = 0; foreach($uItems as &$tItem) { $tCounted += $this->count($tItem); } return $tCounted; } public function remove($uItem, $uLimit = null) { $tRemoved = 0; foreach($this->tag['items'] as $tKey => &$tVal) { if($uItem != $tVal) { continue; } $tRemoved++; unset($this->tag['items'][$tKey]); if(isset($uLimit) && $uLimit >= $tRemoved) { break; } } return $tRemoved; } public function removeRange($uItems, $uLimitEach = null, $uLimitTotal = null) { $tRemoved = 0; foreach($uItems as &$tItem) { $tRemoved += $this->remove($tItem, $uLimitEach); if(isset($uLimitTotal) && $uLimitTotal >= $tRemoved) { break; } } return $tRemoved; } public function removeKey($uKey) { if(!$this->keyExists($uKey, true)) { return 0; } unset($this->tag['items'][$uKey]); return 1; } public function removeIndex($uIndex) { if($this->count < $uIndex) { return 0; } reset($this->tag['items']); for($i = 0;$i < $uIndex;$i++) { next($this->tag['items']); } unset($this->tag['items'][key($this->tag['items'])]); return 1; } public function chunk($uSize, $uPreserveKeys = false) { $tArray = array_chunk($this->tag['items'], $uSize, $uPreserveKeys); return new $this->tag['class'] ($tArray); } public function combineKeys($uArray) { if(is_subclass_of($uArray, 'Collection')) { $uArray = $uArray->toArrayRef(); } $tArray = array_combine($uArray, $this->tag['items']); return new $this->tag['class'] ($tArray); } public function combineValues($uArray) { if(is_subclass_of($uArray, 'Collection')) { $uArray = $uArray->toArrayRef(); } $tArray = array_combine($this->tag['items'], $uArray); return new $this->tag['class'] ($tArray); } public function countValues() { $tArray = array_count_values($this->tag['items']); return new $this->tag['class'] ($tArray); } public function diff() { $uParms = array(&$this->tag['items']); foreach(func_get_args() as $tItem) { if(is_subclass_of($tItem, 'Collection')) { $uParms[] = $tItem->toArrayRef(); } else { $uParms[] = $tItem; } } $tArray = call_user_func_array('array_diff', $uParms); return new $this->tag['class'] ($tArray); } public function filter($uCallback) { $tArray = array_filter($this->tag['items'], $uCallback); return new $this->tag['class'] ($tArray); } public function flip() { $tArray = array_flip($this->tag['items']); return new $this->tag['class'] ($tArray); } public function intersect() { $uParms = array(&$this->tag['items']); foreach(func_get_args() as $tItem) { if(is_subclass_of($tItem, 'Collection')) { $uParms[] = $tItem->toArrayRef(); } else { $uParms[] = $tItem; } } $tArray = call_user_func_array('array_intersect', $uParms); return new $this->tag['class'] ($tArray); } public function keys() { $tArray = array_keys($this->tag['items']); return new $this->tag['class'] ($tArray); } public function map($uCallback) { $tArray = array_map($uCallback, $this->tag['items']); return new $this->tag['class'] ($tArray); } public function mergeRecursive() { $uParms = array(&$this->tag['items']); foreach(func_get_args() as $tItem) { if(is_subclass_of($tItem, 'Collection')) { $uParms[] = $tItem->toArrayRef(); } else { $uParms[] = $tItem; } } $tArray = call_user_func_array('array_merge_recursive', $uParms); return new $this->tag['class'] ($tArray); } public function merge() { $uParms = array(&$this->tag['items']); foreach(func_get_args() as $tItem) { if(is_subclass_of($tItem, 'Collection')) { $uParms[] = $tItem->toArrayRef(); } else { $uParms[] = $tItem; } } $tArray = call_user_func_array('array_merge', $uParms); return new $this->tag['class'] ($tArray); } public function pad($uSize, $uValue) { $tArray = array_pad($this->tag['items'], $uSize, $uValue); return new $this->tag['class'] ($tArray); } public function pop() { return array_pop($this->tag['items']); } public function product() { return array_product($this->tag['items']); } public function push() { $uParms = array(&$this->tag['items']); foreach(func_get_args() as $tItem) { $uParms[] = $tItem; } return call_user_func_array('array_push', $uParms); } public function first() { reset($this->tag['items']); return $this->current(); } public function last() { return end($this->tag['items']); } public function current() { $tValue = current($this->tag['items']); if($tValue === false) { return null; } return $tValue; } public function next() { $tValue = $this->current(); next($this->tag['items']); return $tValue; } public function clear() { $this->tag['items'] = array(); } public function offsetExists($uId) { return $this->keyExists($uId); } public function offsetGet($uId) { return $this->tag['items'][$uId]; } public function offsetSet($uId, $uValue) { $this->tag['items'][$uId] = $uValue; } public function offsetUnset($uId) { $this->removeKey($uId); } public function getIterator() { return new ArrayIterator($this->tag['items']); } public function toCollection() { return new Collection($this->tag['items']); } public function toArray() { return $this->tag['items']; } public function &toArrayRef() { return $this->tag['items']; } public function toString($uSeperator = '') { return implode($uSeperator, $this->tag['items']); } } class XmlCollection extends Collection { public static function fromString($uString) { $tTemp = new XmlCollection(); $tTemp->add(simplexml_load_string($uString)); return $tTemp; } public static function fromFile($uFile) { $tTemp = new XmlCollection(); $tTemp->add(simplexml_load_file($uFile)); return $tTemp; } public static function fromFiles() { $uFiles = func_get_args(); if(is_array($uFiles[0])) { $uFiles = $uFiles[0]; } $tTemp = new XmlCollection(); foreach($uFiles as &$tFile) { $tTemp->add(simplexml_load_file($tFile)); } return $tTemp; } public static function fromFileScan($uPattern) { $tSep = quotemeta(DIRECTORY_SEPARATOR); $tPos = strrpos($uPattern, $tSep); if($tSep != '/' && $tPos === false) { $tSep = '/'; $tPos = strrpos($uPattern, $tSep); } if($tPos !== false) { $tPattern = substr($uPattern, $tPos + strlen($tSep)); $tPath = substr($uPattern, 0, $tPos + strlen($tSep)); } else { $tPath = $uPattern; $tPattern = ''; } $tTemp = new XmlCollection(); $tHandle = new DirectoryIterator($tPath); $tPatExists = (strlen($uPattern) > 0); for(;$tHandle->valid();$tHandle->next()) { if(!($tHandle->isFile())) { continue; } $tFile = $tHandle->current(); if($tPatExists && !fnmatch($tPattern, $tFile)) { continue; } $tTemp->add(simplexml_load_file($tPath . $tFile)); } return $tTemp; } public static function fromSimplexml($uObject) { $tTemp = new XmlCollection(); $tTemp->add($uObject); return $tTemp; } public static function fromDom($uDom) { $tTemp = new XmlCollection(); $tTemp->add(simplexml_import_dom($uDom)); return $tTemp; } } class FileCollection extends Collection { public static function fromFile($uFile) { $tTemp = new FileCollection(); $tTemp->add($uFile); return $tTemp; } public static function fromFiles() { $uFiles = func_get_args(); if(is_array($uFiles[0])) { $uFiles = $uFiles[0]; } $tTemp = new FileCollection(); foreach($uFiles as &$tFile) { $tTemp->add($tFile); } return $tTemp; } public static function fromFileScan($uPattern) { $tSep = quotemeta(DIRECTORY_SEPARATOR); $tPos = strrpos($uPattern, $tSep); if($tSep != '/' && $tPos === false) { $tSep = '/'; $tPos = strrpos($uPattern, $tSep); } if($tPos !== false) { $tPattern = substr($uPattern, $tPos + strlen($tSep)); $tPath = substr($uPattern, 0, $tPos + strlen($tSep)); } else { $tPath = $uPattern; $tPattern = ''; } $tTemp = new FileCollection(); $tHandle = new DirectoryIterator($tPath); $tPatExists = (strlen($uPattern) > 0); for(;$tHandle->valid();$tHandle->next()) { if(!($tHandle->isFile())) { continue; } $tFile = $tHandle->current(); if($tPatExists && !fnmatch($tPattern, $tFile)) { continue; } $tTemp->add($tPath . $tFile); } return $tTemp; } } ?><?php
 class contracts { public static function extension_info() { return array( 'name' => 'contracts', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function check($uCondition) { if(!$uCondition) { throw new Exception('Condition fail'); } } } ?><?php
 class database { private static $databases = array(); private static $default = null; public static function extension_info() { return array( 'name' => 'database', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array('pdo'), 'fwversion' => '1.0', 'fwdepends' => array('string', 'io') ); } public static function extension_load() { foreach(config::get('/databaseList', array()) as $tDatabaseConfig) { $tDatabase = new DatabaseConnection($tDatabaseConfig); self::$databases[$tDatabase->id] = &$tDatabase; if(is_null(self::$default) || $tDatabase->default) { self::$default = &$tDatabase; } } } public static function &get() { $uArgs = func_get_args(); switch(count($uArgs)) { case 0: return self::$default; break; case 1: return self::$databases[$uArgs[0]]; break; case 2: return self::$databases[$uArgs[0]]->datasets[$uArgs[1]]; break; } return null; } public static function sqlInsert($uTable, $uObject) { $tSql = 'INSERT INTO ' . $uTable . ' (' . implode(', ', array_keys($uObject)) . ') VALUES (' . implode(', ', array_values($uObject)) . ')'; return $tSql; } public static function sqlUpdate($uTable, $uObject, $uWhere, $uExtra = '') { $tPairs = array(); foreach($uObject as $tKey => &$tValue) { $tPairs[] = $tKey . '=' . $tValue; } $tSql = 'UPDATE ' . $uTable . ' SET ' . implode(', ', $tPairs); if(strlen($uWhere) > 0) { $tSql .= ' WHERE ' . $uWhere; } if(strlen($uExtra) > 0) { $tSql .= ' ' . $uExtra; } return $tSql; } public static function sqlDelete($uTable, $uWhere, $uExtra = '') { $tSql = 'DELETE FROM ' . $uTable; if(strlen($uWhere) > 0) { $tSql .= ' WHERE ' . $uWhere; } if(strlen($uExtra) > 0) { $tSql .= ' ' . $uExtra; } return $tSql; } public static function sqlSelect($uTable, $uFields, $uWhere, $uExtra = '') { $tSql = 'SELECT '; if(count($uFields) > 0) { $tSql .= implode(', ', $uFields); } else { $tSql .= '*'; } $tSql .= ' FROM ' . $uTable; if(strlen($uWhere) > 0) { $tSql .= ' WHERE ' . $uWhere; } if(strlen($uExtra) > 0) { $tSql .= ' ' . $uExtra; } return $tSql; } } class DatabaseConnection { public $id; public $default; protected $connection = null; public $driver = null; public $datasets = array(); public $cache = array(); public $stats = array('cache' => 0, 'query' => 0); public $active = false; public $inTransaction = false; protected $pdoString; protected $username; protected $password; protected $initCommand; protected $overrideCase; protected $persistent; public $keyphase = null; public $cachePath; private $affectedRows; public function __construct($uConfig) { $this->id = $uConfig['@id']; $this->default = isset($uConfig['@default']); $this->pdoString = $uConfig['pdoString']['.']; $this->username = $uConfig['username']['.']; $this->password = $uConfig['password']['.']; if(isset($uConfig['initCommand'])) { $this->initCommand = $uConfig['initCommand']['.']; } if(isset($uConfig['overrideCase'])) { $this->overrideCase = $uConfig['overrideCase']['.']; } if(isset($uConfig['@keyphase'])) { $this->keyphase = $uConfig['@keyphase']; } $this->persistent = isset($uConfig['persistent']); $this->cachePath = $uConfig['cachePath']['.']; foreach($uConfig['datasetList'] as &$tDatasetConfig) { $tDataset = new DatabaseDataset($this, $tDatasetConfig); $this->datasets[$tDataset->id] = $tDataset; } } public function __destruct() { if($this->active) { $this->close(); } } public function open() { $tParms = array(); if($this->persistent) { $tParms[PDO::ATTR_PERSISTENT] = true; } switch($this->overrideCase) { case 'lower': $tParms[PDO::ATTR_CASE] = PDO::CASE_LOWER; break; case 'upper': $tParms[PDO::ATTR_CASE] = PDO::CASE_UPPER; break; default: $tParms[PDO::ATTR_CASE] = PDO::CASE_NATURAL; break; } $tParms[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION; try { $this->connection = new PDO($this->pdoString, $this->username, $this->password, $tParms); } catch(PDOException $ex) { throw new PDOException('PDO Exception: ' . $ex->getMessage()); } $this->driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME); $this->active = true; if(strlen($this->initCommand) > 0) { $this->connection->exec($this->initCommand); } } public function close() { $this->active = false; } public function beginTransaction() { $this->open(); $this->connection->beginTransaction(); $this->inTransaction = true; } public function commit() { $this->connection->commit(); $this->inTransaction = false; } public function rollBack() { $this->connection->rollBack(); $this->inTransaction = false; } public function query($uQuery, $uParameters = array()) { $this->open(); $tQuery = $this->connection->prepare($uQuery); $tResult = $tQuery->execute($uParameters); $this->affectedRows = $tQuery->rowCount(); if($tResult) { return $this->affectedRows; } return false; } public function &queryFetch($uQuery, $uParameters = array()) { $this->open(); $tQuery = $this->connection->prepare($uQuery); $tQuery->execute($uParameters); $tIterator = new DataRowsIterator($tQuery); return $tIterator; } public function &querySet($uQuery, $uParameters = array()) { $this->open(); $tQuery = $this->connection->prepare($uQuery); $tQuery->execute($uParameters); $tResult = $tQuery->fetchAll(PDO::FETCH_ASSOC); $tQuery->closeCursor(); return $tResult; } public function &queryRow($uQuery, $uParameters = array()) { $this->open(); $tQuery = $this->connection->prepare($uQuery); $tQuery->execute($uParameters); $tResult = $tQuery->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT); $tQuery->closeCursor(); return $tResult; } public function &queryScalar($uQuery, $uParameters = array()) { $this->open(); $tQuery = $this->connection->prepare($uQuery); $tQuery->execute($uParameters); $tResult = $tQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT); $tQuery->closeCursor(); return $tResult[0]; } public function lastInsertId($uName = null) { return $this->connection->lastInsertId($uName); } public function affectedRows() { return $this->affectedRows; } public function serverInfo() { return $this->connection->getAttribute(PDO::ATTR_SERVER_INFO); } } class DatabaseDataset { protected $database; public $id; public $queryString; public $parameters; public $cacheLife; public $transaction; public function __construct(&$uDatabase, $uConfig) { $this->database = &$uDatabase; $this->id = $uConfig['@id']; $this->queryString = $uConfig['.']; $this->parameters = strlen($uConfig['@parameters']) > 0 ? explode(',', $uConfig['@parameters']) : array(); $this->cacheLife = isset($uConfig['@cacheLife']) ? (int)$uConfig['@cacheLife'] : 0; $this->transaction = isset($uConfig['@transaction']); } public function query() { $uProps = func_get_args(); return $this->queryInternal($uProps); } public function &queryFetch($uQuery, $uParameters = array()) { $uProps = func_get_args(); return $this->queryFetchInternal($uProps); } public function querySet() { $uProps = func_get_args(); $tData = $this->querySetInternal($uProps); return $tData['data']; } public function queryRow() { $uProps = func_get_args(); $tData = $this->querySetInternal($uProps); if(count($tData['data']) > 0) { return $tData['data'][0]; } return null; } public function queryScalar() { $uProps = func_get_args(); $tData = $this->querySetInternal($uProps); if(count($tData['data']) > 0) { return current($tData['data'][0]); } return null; } private function queryInternal($uProps) { if($this->transaction) { $this->database->beginTransaction(); } try { $tCount = 0; $tArray = array(); foreach($this->parameters as &$tParam) { $tArray[$tParam] = $uProps[$tCount++]; } $tQueryExecute = string::format($this->queryString, $tArray); if(Framework::$debug) { echo 'query: ', $tQueryExecute, "\n"; } $tResult = $this->database->query($tQueryExecute); if($this->database->inTransaction) { $this->database->commit(); } } catch(PDOException $ex) { if($this->database->inTransaction) { $this->database->rollBack(); } throw new PDOException($ex->getMessage()); } $this->database->stats['query']++; if(isset($tResult)) { return $this->database->affectedRows(); } return false; } private function &queryFetchInternal($uProps) { if($this->transaction) { $this->database->beginTransaction(); } try { $tCount = 0; $tArray = array(); foreach($this->parameters as &$tParam) { $tArray[$tParam] = $uProps[$tCount++]; } $tQueryExecute = string::format($this->queryString, $tArray); if(Framework::$debug) { echo 'query: ', $tQueryExecute, "\n"; } $tResult = $this->database->queryFetch($tQueryExecute); if($this->database->inTransaction) { $this->database->commit(); } } catch(PDOException $ex) { if($this->database->inTransaction) { $this->database->rollBack(); } throw new PDOException($ex->getMessage()); } $this->database->stats['query']++; if(isset($tResult)) { return $tResult; } return false; } private function &querySetInternal($uProps) { $uPropsSerialized = $this->id; foreach($uProps as &$tProp) { $uPropsSerialized .= '_' . io::sanitize($tProp); } $tFileName = $this->database->id . '_' . $uPropsSerialized; $tFilePath = QPATH_APP . $this->database->cachePath . $tFileName; $tData = null; $tLoadedFromCache = false; if(isset($this->database->cache[$uPropsSerialized])) { $tData = &$this->database->cache[$uPropsSerialized]; $tData['data']->iterator->rewind(); $tLoadedFromCache = true; } else if($this->cacheLife > 0 && is_readable($tFilePath)) { $tData = io::readSerialize($tFilePath, $this->database->keyphase); $tLoadedFromCache = true; $this->database->cache[$uPropsSerialized] = &$tData; } if(is_null($tData) || ($tData['lastmod'] + $this->cacheLife < time())) { if($this->transaction) { $this->database->beginTransaction(); } try { $tCount = 0; $tArray = array(); foreach($this->parameters as &$tParam) { $tArray[$tParam] = $uProps[$tCount++]; } $tQueryExecute = string::format($this->queryString, $tArray); if(Framework::$debug) { echo 'query: ', $tQueryExecute, "\n"; } $tData = array( 'data' => $this->database->querySet($tQueryExecute), 'lastmod' => time() ); if($this->database->inTransaction) { $this->database->commit(); } if($this->cacheLife > 0) { $this->database->cache[$uPropsSerialized] = &$tData; io::writeSerialize($tFilePath, $tData, $this->database->keyphase); } } catch(PDOException $ex) { if($this->database->inTransaction) { $this->database->rollBack(); } throw new PDOException($ex->getMessage()); } $this->database->stats['query']++; } else { $this->database->stats['cache']++; } return $tData; } } class DatabaseQuery { protected $database = null; private $table; private $fields; private $parameters; private $where; private $groupby; private $orderby; private $limit; private $offset; public function __construct(&$uDatabase = null) { $this->setDatabase($uDatabase); } public function setDatabase(&$uDatabase = null) { if(!is_null($uDatabase)) { $this->database = &$uDatabase; } else { $this->database = database::get(); } $this->clear(); } public function setDatabaseName($uDatabaseName) { $this->database = database::get($uDatabaseName); $this->clear(); } public function clear() { $this->table = ''; $this->fields = array(); $this->parameters = array(); $this->where = ''; $this->groupby = ''; $this->orderby = ''; $this->limit = -1; $this->offset = -1; } public function setTable($uTableName) { $this->table = $uTableName; return $this; } public function joinTable($uTableName, $uCondition, $uJoinType = 'INNER') { $this->table .= ' ' . $uJoinType . ' JOIN ' . $uTableName . ' ON ' . $uCondition; return $this; } public function setFields($uArray) { foreach($uArray as $tField => &$tValue) { if(is_null($tValue)) { $this->fields[$tField] = 'NULL'; } else { $this->fields[$tField] = ':' . $tField; $this->parameters[$this->fields[$tField]] = $tValue; } } return $this; } public function setFieldsDirect($uArray) { $this->fields = &$uArray; return $this; } public function addField($uField, $uValue = null) { if(func_num_args() == 1) { $this->fields[] = $uField; return $this; } if(is_null($uValue)) { $this->fields[$uField] = 'NULL'; } else { $this->fields[$uField] = ':' . $uField; $this->parameters[$this->fields[$uField]] = $uValue; } return $this; } public function addFieldDirect($uField, $uValue) { $this->fields[$uField] = $uValue; return $this; } public function addParameter($uParameter, $uValue) { $this->parameters[$uParameter] = $uValue; return $this; } public function setWhere($uCondition) { $this->where = $uCondition; return $this; } public function andWhere($uCondition) { $this->where .= ' AND ' . $uCondition; return $this; } public function orWhere($uCondition) { $this->where .= ' OR ' . $uCondition; return $this; } public function setGroupBy($uGroupBy) { $this->groupby = $uGroupBy; return $this; } public function addGroupBy($uGroupBy) { $this->groupby .= ', ' . $uGroupBy; return $this; } public function setOrderBy($uOrderBy, $uOrder = 'ASC') { $this->orderby = $uOrderBy . ' ' . $uOrder; return $this; } public function addOrderBy($uOrderBy, $uOrder = 'ASC') { $this->orderby .= ', ' . $uOrderBy . ' ' . $uOrder; return $this; } public function setLimit($uLimit) { $this->limit = $uLimit; return $this; } public function setOffset($uOffset) { $this->offset = $uOffset; return $this; } public function insert() { $this->database->query(database::sqlInsert($this->table, $this->fields), $this->parameters); if($this->database->driver == 'pgsql') { $tInsertId = $this->database->lastInsertId($this->table . '_id_seq'); } else { $tInsertId = $this->database->lastInsertId(); } $this->clear(); return $tInsertId; } public function update() { if($this->database->driver == 'mysql' && $this->limit >= 0) { $tExtra = 'LIMIT ' . $this->limit; } else { $tExtra = ''; } $this->database->query(database::sqlUpdate($this->table, $this->fields, $this->where, $tExtra), $this->parameters); $this->clear(); return $this->database->affectedRows(); } public function delete() { if($this->database->driver == 'mysql' && $this->limit >= 0) { $tExtra = 'LIMIT ' . $this->limit; } else { $tExtra = ''; } $this->database->query(database::sqlDelete($this->table, $this->where, $tExtra), $this->parameters); $this->clear(); return $this->database->affectedRows(); } public function &get() { if($this->limit >= 0) { if($this->offset >= 0) { $tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset; } else { $tExtra = 'LIMIT ' . $this->limit; } } else { $tExtra = ''; } $tReturn = $this->database->querySet(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters); $this->clear(); return $tReturn; } public function &getRow() { if($this->limit >= 0) { if($this->offset >= 0) { $tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset; } else { $tExtra = 'LIMIT ' . $this->limit; } } else { $tExtra = ''; } $tReturn = $this->database->queryRow(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters); $this->clear(); return $tReturn; } public function &getScalar() { if($this->limit >= 0) { if($this->offset >= 0) { $tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset; } else { $tExtra = 'LIMIT ' . $this->limit; } } else { $tExtra = ''; } $tReturn = $this->database->queryScalar(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters); $this->clear(); return $tReturn; } public function &calculate($uTable, $uOperation = 'COUNT', $uField = '*', $uWhere = null) { $tReturn = $this->database->queryScalar(database::sqlSelect($uTable, array($uOperation . '(' . $uField . ')'), $uWhere, null), array()); return $tReturn; } } class DataRowsIterator extends NoRewindIterator implements Countable { private $connection; private $current; private $count; private $cursor = 0; public function __construct($uConnection) { $this->connection = &$uConnection; $this->count = $this->connection->rowCount(); $this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT); } public function __destruct() { $this->connection->closeCursor(); } public function count() { return $this->count; } public function current() { return $this->current; } public function key() { return null; } public function next() { $this->cursor++; $this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT); return $this->current; } public function valid() { if($this->cursor < $this->count) { return true; } return false; } } ?>
<?php
 class html { public static function extension_info() { return array( 'name' => 'html', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('string') ); } public static function selectBox($uArray = array(), $uDefault = null) { $tOutput = ''; foreach($uArray as $tKey => &$tVal) { $tOutput .= '<option value="' . string::escapeDQuotes($tKey) . '"'; if($uDefault == $tKey) { $tOutput .= ' selected="selected"'; } $tOutput .= '>' . $tVal . '</option>'; } return $tOutput; } public static function pager($uOptions) { $tPages = ceil($uOptions['total'] / $uOptions['pagesize']); if(!isset($uOptions['divider'])) { $uOptions['divider'] = ''; } if(!isset($uOptions['dots'])) { $uOptions['dots'] = ' ... '; } if(!isset($uOptions['passivelink'])) { $uOptions['passivelink'] = $uOptions['link']; } if(!isset($uOptions['activelink'])) { $uOptions['activelink'] = $uOptions['passivelink']; } if(!isset($uOptions['firstlast'])) { $uOptions['firstlast'] = true; } if(isset($uOptions['current'])) { $tCurrent = (int)$uOptions['current']; if($tCurrent <= 0) { $tCurrent = 1; } } else { $tCurrent = 1; } if(isset($uOptions['numlinks'])) { $tNumLinks = (int)$uOptions['numlinks']; } else { $tNumLinks = 10; } $tStart = $tCurrent - floor($tNumLinks * 0.5); $tEnd = $tCurrent + floor($tNumLinks * 0.5) - 1; if($tStart < 1) { $tEnd += abs($tStart) + 1; $tStart = 1; } if($tEnd > $tPages) { if($tStart - $tEnd - $tPages > 0) { $tStart -= $tEnd - $tPages; } $tEnd = $tPages; } $tResult = ''; if($tPages > 1) { if($tCurrent <= 1) { if($uOptions['firstlast']) { $tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;&lt;')); } $tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;')); } else { if($uOptions['firstlast']) { $tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;&lt;')); } $tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tCurrent - 1, 'pagetext' => '&lt;')); } if($tStart > 1) { $tResult .= $uOptions['dots']; } else { $tResult .= $uOptions['divider']; } } for($i = $tStart;$i <= $tEnd;$i++) { if($tCurrent == $i) { $tResult .= string::format($uOptions['activelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $i, 'pagetext' => $i)); } else { $tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $i, 'pagetext' => $i)); } if($i != $tEnd) { $tResult .= $uOptions['divider']; } } if($tPages > 1) { if($tEnd < $tPages) { $tResult .= $uOptions['dots']; } else { $tResult .= $uOptions['divider']; } if($tCurrent >= $tPages) { $tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;')); if($uOptions['firstlast']) { $tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;&gt;')); } } else { $tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tCurrent + 1, 'pagetext' => '&gt;')); if($uOptions['firstlast']) { $tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;&gt;')); } } } return $tResult; } public static function table($uOptions) { if(!isset($uOptions['table'])) { $uOptions['table'] = '<table>'; } if(!isset($uOptions['cell'])) { $uOptions['cell'] = '<td>{value}</td>'; } if(!isset($uOptions['header'])) { $uOptions['header'] = '<th>{value}</th>'; } $tResult = string::format($uOptions['table'], array()); if(isset($uOptions['headers'])) { $tResult .= '<tr>'; foreach($uOptions['headers'] as &$tColumn) { $tResult .= string::format($uOptions['header'], array('value' => $tColumn)); } $tResult .= '</tr>'; } foreach($uOptions['data'] as &$tRow) { if(isset($uOptions['rowFunc'])) { $tResult .= call_user_func($uOptions['rowFunc'], $tRow); } else if(isset($uOptions['row'])) { $tResult .= string::format($uOptions['row'], $tRow); } else { $tResult .= '<tr>'; foreach($tRow as &$tColumn) { $tResult .= string::format($uOptions['cell'], array('value' => $tColumn)); } $tResult .= '</tr>'; } } $tResult .= '</table>'; return $tResult; } } ?><?php
 class http { private static $platform = null; private static $crawler = null; private static $isAjax = false; private static $isBrowser = false; private static $isRobot = false; private static $isMobile = false; private static $languages = array(); public static function extension_info() { return array( 'name' => 'http', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('string', 'io') ); } public static function extension_load() { ini_set('session.use_trans_sid', '0'); header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'); static $aEnvNames = array( 'HTTP_ACCEPT_LANGUAGE', 'HTTP_HOST', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'PHP_SELF', 'QUERY_STRING', 'REQUEST_URI', 'SERVER_ADDR', 'SERVER_NAME', 'SERVER_PORT' ); foreach($aEnvNames as &$tEnv) { if(isset($_SERVER[$tEnv]) && strlen($_SERVER[$tEnv]) > 0) { continue; } $_SERVER[$tEnv] = getenv($tEnv) or $_SERVER[$tEnv] = ''; } if(isset($_SERVER['HTTP_CLIENT_IP'])) { $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP']; } else if(!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR']; } else { $_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR') or $_SERVER['REMOTE_ADDR'] = '0.0.0.0'; } $_SERVER['PHP_SELF'] = str_replace(array('<', '>'), array('%3C', '%3E'), $_SERVER['PHP_SELF']); $_SERVER['QUERY_STRING'] = self::xss($_SERVER['QUERY_STRING']); foreach(config::get('/http/rewriteList', array()) as $tRewriteList) { $tReturn = preg_replace('|^' . $tRewriteList['@match'] . '$|', $tRewriteList['@forward'], $_SERVER['QUERY_STRING'], -1, $tCount); if($tCount > 0) { $_SERVER['QUERY_STRING'] = $tReturn; break; } } $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF']; if(strlen($_SERVER['QUERY_STRING']) > 0) { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; } if(strlen($_SERVER['HTTP_HOST']) == 0) { $_SERVER['HTTP_HOST'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR']; if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') { $_SERVER['HTTP_HOST'] .= $_SERVER['SERVER_PORT']; } } if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { self::$isAjax = true; } foreach(config::get('/http/userAgents/platformList', array()) as $tPlatformList) { if(preg_match('/' . $tPlatformList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) { self::$platform = $tPlatformList['@name']; break; } } foreach(config::get('/http/userAgents/crawlerList', array()) as $tCrawlerList) { if(preg_match('/' . $tCrawlerList['@match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) { self::$crawler = $tCrawlerList['@name']; switch($tCrawlerList['@type']) { case 'bot': self::$isRobot = true; break; case 'mobile': self::$isMobile = true; break; case 'browser': default: self::$isBrowser = true; break; } break; } } self::$languages = self::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE']); $tParsingType = Config::get('/http/request/@parsingType', '0'); if($tParsingType == '1') { $tDefaultParameter = Config::get('/http/request/@getParameters', '&'); $tDefaultKey = Config::get('/http/request/@getKeys', '='); if($tDefaultParameter != '&' || $tDefaultKey != '=') { self::parseGetType1($tDefaultParameter, $tDefaultKey); $tGetProcessed = true; } } else if($tParsingType == '2') { self::parseGetType2(); $tGetProcessed = true; } if(get_magic_quotes_gpc()) { if(!isset($tGetProcessed)) { array_walk($_GET, array('http', 'magic_quotes_deslash')); } array_walk($_POST, array('http', 'magic_quotes_deslash')); array_walk($_COOKIE, array('http', 'magic_quotes_deslash')); } $_REQUEST = array_merge($_GET, $_POST, $_COOKIE); } public static function xss($uString) { return str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); } public static function encode($uString) { return urlencode($uString); } public static function decode($uString) { return urldecode($uString); } public static function sendStatus($uStatusCode) { switch((int)$uStatusCode) { case 100: $tStatus = 'HTTP/1.1 100 Continue'; break; case 101: $tStatus = 'HTTP/1.1 101 Switching Protocols'; break; case 200: $tStatus = 'HTTP/1.1 200 OK'; break; case 201: $tStatus = 'HTTP/1.1 201 Created'; break; case 202: $tStatus = 'HTTP/1.1 202 Accepted'; break; case 203: $tStatus = 'HTTP/1.1 203 Non-Authoritative Information'; break; case 204: $tStatus = 'HTTP/1.1 204 No Content'; break; case 205: $tStatus = 'HTTP/1.1 205 Reset Content'; break; case 206: $tStatus = 'HTTP/1.1 206 Partial Content'; break; case 300: $tStatus = 'HTTP/1.1 300 Multiple Choices'; break; case 301: $tStatus = 'HTTP/1.1 301 Moved Permanently'; break; case 302: $tStatus = 'HTTP/1.1 302 Found'; break; case 303: $tStatus = 'HTTP/1.1 303 See Other'; break; case 304: $tStatus = 'HTTP/1.1 304 Not Modified'; break; case 305: $tStatus = 'HTTP/1.1 305 Use Proxy'; break; case 307: $tStatus = 'HTTP/1.1 307 Temporary Redirect'; break; case 400: $tStatus = 'HTTP/1.1 400 Bad Request'; break; case 401: $tStatus = 'HTTP/1.1 401 Unauthorized'; break; case 402: $tStatus = 'HTTP/1.1 402 Payment Required'; break; case 403: $tStatus = 'HTTP/1.1 403 Forbidden'; break; case 404: $tStatus = 'HTTP/1.1 404 Not Found'; break; case 405: $tStatus = 'HTTP/1.1 405 Method Not Allowed'; break; case 406: $tStatus = 'HTTP/1.1 406 Not Acceptable'; break; case 407: $tStatus = 'HTTP/1.1 407 Proxy Authentication Required'; break; case 408: $tStatus = 'HTTP/1.1 408 Request Timeout'; break; case 409: $tStatus = 'HTTP/1.1 409 Conflict'; break; case 410: $tStatus = 'HTTP/1.1 410 Gone'; break; case 411: $tStatus = 'HTTP/1.1 411 Length Required'; break; case 412: $tStatus = 'HTTP/1.1 412 Precondition Failed'; break; case 413: $tStatus = 'HTTP/1.1 413 Request Entity Too Large'; break; case 414: $tStatus = 'HTTP/1.1 414 Request-URI Too Long'; break; case 415: $tStatus = 'HTTP/1.1 415 Unsupported Media Type'; break; case 416: $tStatus = 'HTTP/1.1 416 Requested Range Not Satisfiable'; break; case 417: $tStatus = 'HTTP/1.1 417 Expectation Failed'; break; case 500: $tStatus = 'HTTP/1.1 500 Internal Server Error'; break; case 501: $tStatus = 'HTTP/1.1 501 Not Implemented'; break; case 502: $tStatus = 'HTTP/1.1 502 Bad Gateway'; break; case 503: $tStatus = 'HTTP/1.1 503 Service Unavailable'; break; case 504: $tStatus = 'HTTP/1.1 504 Gateway Timeout'; break; case 505: $tStatus = 'HTTP/1.1 505 HTTP Version Not Supported'; break; default: return; } self::sendHeader($tStatus); } public static function sendHeader($uHeader, $uValue = null, $uReplace = false) { if(isset($uValue)) { header($uHeader . ': ' . $uValue, $uReplace); } else { header($uHeader, $uReplace); } } public static function sendFile($uFilePath, $uAttachment = false, $uFindMimeType = true) { $tExtension = pathinfo($uFilePath, PATHINFO_EXTENSION); if($uFindMimeType) { $tType = io::getMimeType($tExtension); } else { $tType = 'application/octet-stream'; } self::sendHeaderExpires(0); self::sendHeaderNoCache(); self::sendHeader('Content-Type', $tType, true); if($uAttachment) { self::sendHeader('Content-Disposition', 'attachment; filename=' . pathinfo($uFilePath, PATHINFO_BASENAME) . ';', true); } self::sendHeader('Content-Transfer-Encoding', 'binary', true); self::sendHeader('Content-Length', filesize($uFilePath), true); self::sendHeaderETag(md5_file($uFilePath)); @readfile($uFilePath); exit(); } public static function sendHeaderLastModified($uTime, $uNotModified = false) { self::sendHeader('Last-Modified', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true); if($uNotModified) { self::sendStatus(304); } } public static function sendHeaderExpires($uTime) { self::sendHeader('Expires', gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true); } public static function sendRedirect($uLocation, $uTerminate = true) { self::sendHeader('Location', $uLocation, true); if($uTerminate) { exit(); } } public static function sendHeaderETag($uHash) { self::sendHeader('ETag', '"' . $uHash . '"', true); } public static function sendHeaderNoCache() { self::sendHeader('Pragma', 'public', true); self::sendHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true); self::sendHeader('Cache-Control', 'pre-check=0, post-check=0, max-age=0'); } public static function sendCookie($uCookie, $uValue, $uExpire = 0) { setrawcookie($uCookie, self::encode($uValue), $uExpire); } public static function parseGetType1($uParameters = '&', $uKeys = '=') { $_GET = string::parseQueryString($_SERVER['QUERY_STRING'], $uParameters, $uKeys); } public static function parseGetType2($uSeperator = '/') { $_GET = explode($uSeperator, $_SERVER['QUERY_STRING']); } public static function parseHeaderString($uString) { $tResult = array(); foreach(explode(',', $uString) as $tPiece) { $tPiece = trim($tPiece); $tResult[] = substr($tPiece, 0, strcspn($tPiece, ';')); } return $tResult; } private static function magic_quotes_deslash(&$uItem) { switch(gettype($uItem)) { case 'array': array_walk($uItem, 'magic_quotes_deslash'); break; case 'string': $uItem = stripslashes($uItem); break; } } public static function getPlatform() { return self::$platform; } public static function getCrawler() { return self::$crawler; } public static function getIsAjax() { return self::$isAjax; } public static function getIsBrowser() { return self::$isBrowser; } public static function getIsRobot() { return self::$isRobot; } public static function getIsMobile() { return self::$isMobile; } public static function getLanguages() { return self::$languages; } } ?>
<?php
 class io { public static function extension_info() { return array( 'name' => 'io', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('string') ); } public static function getMimeType($uExtension, $uDefault = 'application/octet-stream') { switch(string::toLower($uExtension)) { case 'pdf': $tType = 'application/pdf'; break; case 'exe': $tType = 'application/octet-stream'; break; case 'zip': $tType = 'application/zip'; break; case 'gz': $tType = 'application/x-gzip'; break; case 'tar': $tType = 'application/x-tar'; break; case 'csv': $tType = 'text/csv'; break; case 'txt': case 'text': case 'log': $tType = 'text/plain'; break; case 'rtf': $tType = 'text/rtf'; break; case 'eml': $tType = 'message/rfc822'; break; case 'xml': case 'xsl': $tType = 'text/xml'; break; case 'doc': case 'word': $tType = 'application/msword'; break; case 'docx': $tType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break; case 'xls': $tType = 'application/vnd.ms-excel'; break; case 'xl': $tType = 'application/excel'; break; case 'xlsx': $tType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break; case 'ppt': $tType = 'application/vnd.ms-powerpoint'; break; case 'bmp': $tType = 'image/bmp'; break; case 'gif': $tType = 'image/gif'; break; case 'png': $tType = 'image/png'; break; case 'jpeg': case 'jpe': case 'jpg': $tType = 'image/jpg'; break; case 'tif': case 'tiff': $tType = 'image/tiff'; break; case 'mid': case 'midi': $tType = 'audio/midi'; break; case 'mpga': case 'mp2': case 'mp3': $tType = 'audio/mpeg'; break; case 'wav': $tType = 'audio/x-wav'; break; case 'mpeg': case 'mpg': case 'mpe': $tType = 'video/mpeg'; break; case 'qt': case 'mov': $tType = 'video/quicktime'; break; case 'avi': $tType = 'video/x-msvideo'; break; case 'swf': $tType = 'application/x-shockwave-flash'; break; case 'htm': case 'html': case 'shtm': case 'shtml': $tType = 'text/html'; break; case 'php': $tType = 'application/x-httpd-php'; break; case 'phps': $tType = 'application/x-httpd-php-source'; break; case 'css': $tType = 'text/css'; break; case 'js': $tType = 'application/x-javascript'; break; default: $tType = $uDefault; } return $tType; } public static function read($uPath) { return file_get_contents($uPath); } public static function write($uPath, $uContent) { return file_put_contents($uPath, $uContent, LOCK_EX); } public static function readSerialize($uPath, $uEncryptKey = null) { $tContent = self::read($uPath); if(!is_null($uEncryptKey)) { $tContent = string::decrypt($tContent, $uEncryptKey); } return unserialize($tContent); } public static function writeSerialize($uPath, $uContent, $uEncryptKey = null) { $tContent = serialize($uContent); if(!is_null($uEncryptKey)) { $tContent = string::encrypt($tContent, $uEncryptKey); } return self::write($uPath, $tContent); } public static function touch($uPath) { return touch($uPath); } public static function sanitize($uFilename) { static $aReplaceChars = array('_' => '-', '\\' => '-', '/' => '-', ':' => '-', '?' => '-', '*' => '-', '"' => '-', '\'' => '-', '<' => '-', '>' => '-', '|' => '-', '.' => '-'); return strtr($uFilename, $aReplaceChars); } } ?><?php
 class logger { private static $filename; private static $eof = "\r\n"; public static function extension_info() { return array( 'name' => 'logger', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('http') ); } public static function extension_load() { self::$filename = Config::get('/logger/@filename', 'd-m-Y'); set_exception_handler('logger::exceptionCallback'); set_error_handler('logger::errorCallback', E_ALL); } public static function errorCallback($uCode, $uMessage, $uFile, $uLine) { throw new ErrorException($uMessage, $uCode, 0, $uFile, $uLine); } public static function exceptionCallback($uException) { switch($uException->getCode()) { case E_ERROR: case E_USER_ERROR: case E_RECOVERABLE_ERROR: $tType = 'Error'; break; case E_WARNING: case E_USER_WARNING: $tType = 'Warning'; break; case E_NOTICE: case E_USER_NOTICE: $tType = 'Notice'; break; case E_STRICT: $tType = 'Strict'; break; case 8192: case 16384: $tType = 'Deprecated'; break; default: $tType = 'Unknown'; break; } $tIgnoreError = false; Events::invoke('reportError', array( 'type' => &$tType, 'message' => $uException->getMessage(), 'file' => $uException->getFile(), 'line' => $uException->getLine(), 'ignore' => &$tIgnoreError )); if(!$tIgnoreError) { http::sendStatus(500); http::sendHeader('Content-Type', 'text/html', true); Events::setDisabled(true); $tEventDepth = Events::getEventDepth(); for($tCount = ob_get_level(); --$tCount > 1;ob_end_flush()); if(Framework::$development) { $tDeveloperLocation = pathinfo($uException->getFile(), PATHINFO_FILENAME) . ' @' . $uException->getLine(); } else { $tDeveloperLocation = ''; } $tString = ''; $tString .= '<div>'; $tString .= '<div style="font: 11pt \'Lucida Sans Unicode\'; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $tType . '</span>: ' . $tDeveloperLocation . '</div>' . self::$eof; $tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #404040; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;">' . $uException->getMessage() . '</div>' . self::$eof; if(Framework::$development) { if(count($tEventDepth) > 0) { $tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>eventDepth:</b>' . implode('<br />' . self::$eof, $tEventDepth) . '</div>' . self::$eof; } $tString .= '<div style="font: 10pt \'Lucida Sans Unicode\'; color: #800000; padding: 0px 12px 0px 12px; margin: 20px 0px 20px 0px; line-height: 20px;"><b>stackTrace:</b>' . $uException->getTraceAsString() . '</div>' . self::$eof; } $tString .= '</div>'; $tFilename = QPATH_APP . 'logs/' . date(self::$filename) . '.txt'; $tContent = '[' . date('d-m-Y H:i:s') . '] ' . $uException->__toString() . self::$eof . self::$eof; file_put_contents($tFilename, $tContent, FILE_APPEND); $tString .= '<div style="font: 7pt \'Lucida Sans Unicode\'; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="mailto:laroux.pos@gmail.com">' . ucfirst(INCLUDED) . '</a>.</div>'; echo $tString; exit(); } } } ?><?php
 class mvc { private static $controller = null; private static $controllerActual = null; private static $controllerClass = null; private static $action = null; private static $actionActual = null; public static function extension_info() { return array( 'name' => 'mvc', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('string', 'http', 'database') ); } public static function extension_load() { if(COMPILED) { Events::register('run', Events::Callback('mvc::run')); } } public static function run() { $tDefaultController = Config::get('/mvc/routing/@defaultController', 'home'); $tDefaultAction = Config::get('/mvc/routing/@defaultAction', 'index'); $tNotfoundController = Config::get('/mvc/routing/@notfoundController', 'home'); $tNotfoundAction = Config::get('/mvc/routing/@notfoundAction', 'notfound'); $tControllerUrlKey = Config::get('/mvc/routing/@controllerUrlKey', '0'); $tActionUrlKey = Config::get('/mvc/routing/@actionUrlKey', '1'); if(array_key_exists($tControllerUrlKey, $_GET) && strlen($_GET[$tControllerUrlKey]) > 0) { self::$controller = $_GET[$tControllerUrlKey]; } else { self::$controller = $tDefaultController; } if(array_key_exists($tActionUrlKey, $_GET) && strlen($_GET[$tActionUrlKey]) > 0) { self::$action = $_GET[$tActionUrlKey]; } else { self::$action = $tDefaultAction; } self::$controllerActual = self::$controller; if(count($_POST) > 0 && method_exists(self::$controller, self::$action . '_post')) { self::$actionActual = self::$action . '_post'; } else { self::$actionActual = self::$action; } Events::invoke('routing', array( 'controller' => &self::$controller, 'action' => &self::$action, 'controllerActual' => &self::$controllerActual, 'actionActual' => &self::$actionActual )); if(!method_exists(self::$controllerActual, self::$actionActual)) { self::$controllerActual = $tNotfoundController; self::$actionActual = $tNotfoundAction; } self::$controllerClass = new self::$controllerActual (); self::$controllerClass->{self::$actionActual}(); return false; } public static function getController() { return self::$controller; } public static function getControllerActual() { return self::$controllerActual; } public static function getAction() { return self::$action; } public static function getActionActual() { return self::$actionActual; } } abstract class Model { protected $controller; public function loaddatabase($uDatabaseName, $uMemberName = null) { if(is_null($uMemberName)) { $uMemberName = $uDatabaseName; } $this->{$uMemberName} = new DatabaseQuery(database::get($uDatabaseName)); } public function __construct(&$uController) { $this->controller = &$uController; $this->loaddatabase(null, 'db'); } } abstract class Controller { protected $device = ''; protected $language = ''; public function loadmodel($uModelClass, $uMemberName = null) { if(is_null($uMemberName)) { $uMemberName = $uModelClass; } $this->{$uMemberName} = new $uModelClass ($this); } public function loadview($uViewFile, $uModel = null) { $tViewFile = pathinfo($uViewFile, PATHINFO_FILENAME); $tViewExtension = pathinfo($uViewFile, PATHINFO_EXTENSION); if(strlen($this->device) > 0) { $tViewFile .= '.' . $this->device; } if(strlen($this->language) > 0) { $tViewFile .= '.' . $this->language; } $tExtra = array( 'root' => Framework::$siteroot ); Events::invoke('renderview', array( 'viewFile' => &$tViewFile, 'viewExtension' => &$tViewExtension, 'model' => &$uModel, 'extra' => &$tExtra )); } public function getDevice() { return $this->device; } public function getLanguage() { return $this->language; } public function httpGet($uKey, $uDefault = '', $uFilter = null) { if(!array_key_exists($uKey, $_GET)) { return $uDefault; } if(!is_null($uFilter)) { return string::filter($_GET[$uKey], $uFilter); } return $_GET[$uKey]; } public function httpPost($uKey, $uDefault = null) { if(!array_key_exists($uKey, $_POST)) { return $uDefault; } return $_POST[$uKey]; } public function httpCookie($uKey, $uDefault = null) { if(!array_key_exists($uKey, $_COOKIE)) { return $uDefault; } return $_COOKIE[$uKey]; } } ?>
<?php
 class output { private static $effectList = array(); public static function extension_info() { return array( 'name' => 'output', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { } public static function begin() { ob_start('output::flushOutput'); ob_implicit_flush(false); $tArgs = func_get_args(); array_push(self::$effectList, $tArgs); } public static function &end($uFlush = true) { $tContent = ob_get_contents(); ob_end_flush(); foreach(array_pop(self::$effectList) as $tEffect) { $tContent = call_user_func($tEffect, $tContent); } if($uFlush) { echo $tContent; } return $tContent; } public static function flushOutput($uContent) { return ''; } } ?>
<?php
 class repository { private static $packageKey = null; private static $packages = array(); public static function extension_info() { return array( 'name' => 'repository', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('io') ); } public static function extension_load() { if(COMPILED) { Events::register('run', Events::Callback('repository::run')); } foreach(Config::get('/repository/packageList', array()) as $tPackage) { self::$packages[$tPackage['@name']] = array(); foreach($tPackage['fileList'] as &$tFile) { self::$packages[$tPackage['@name']][] = Framework::translatePath($tFile['@path']); } } } public static function run() { $tCheckKey = Config::get('/repository/routing/@repositoryCheckKey', 'rep'); $tCheckValue = Config::get('/repository/routing/@repositoryCheckValue', ''); $tPackageKey = Config::get('/repository/routing/@repositoryPackageKey', $tCheckKey); if(array_key_exists($tCheckKey, $_GET)) { if(strlen($tCheckValue) == 0) { self::$packageKey = $_GET[$tPackageKey]; } else if($_GET[$tCheckKey] == $tCheckValue) { self::$packageKey = $_GET[$tPackageKey]; } } if(isset(self::$packageKey)) { if(array_key_exists(self::$packageKey, self::$packages)) { $tMimetype = io::getMimeType('phps'); header('Content-Type: ' . $tMimetype, true); Framework::printFiles(self::$packages[self::$packageKey]); return false; } else { throw new Exception('package not found.'); } } } public static function getPackageKey() { return self::$packageKey; } } ?>
<?php
 class session { private static $id = null; private static $data = null; private static $flashdata_loaded = null; private static $flashdata_next = array(); private static $sessionName; private static $sessionLife; private static $isModified = false; private static $keyphase = null; public static function extension_info() { return array( 'name' => 'session', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('io', 'http') ); } public static function extension_load() { self::$sessionName = Config::get('/session/cookie/@name', 'sessid'); self::$sessionLife = intval(Config::get('/session/cookie/@life', '0')); self::$keyphase = Config::get('/session/cookie/@keyphase', null); if(array_key_exists(self::$sessionName, $_COOKIE)) { self::$id = $_COOKIE[self::$sessionName]; } Events::register('output', Events::Callback('session::output')); } public static function output() { if(self::$isModified) { self::save(); } } private static function open() { if(!is_null(self::$id)) { $tIpCheck = (bool)Config::get('/session/cookie/@ipCheck', '0'); $tUACheck = (bool)Config::get('/session/cookie/@uaCheck', '1'); $tFilename = QPATH_APP . 'sessions/' . self::$id; if(file_exists($tFilename)) { $tData = io::readSerialize($tFilename, self::$keyphase); if( (self::$sessionLife <= 0 || $tData['lastmod'] + self::$sessionLife >= time()) && (!$tIpCheck || $tData['ip'] == $_SERVER['REMOTE_ADDR']) && (!$tUACheck || $tData['ua'] == $_SERVER['HTTP_USER_AGENT']) ) { self::$data = $tData['data']; self::$flashdata_loaded = $tData['flashdata']; return; } } } self::$data = array(); self::$flashdata_loaded = array(); self::$isModified = false; } public static function save() { $tKeyphase = Config::get('/session/cookie/@keyphase', null); if(is_null(self::$id)) { self::$id = io::sanitize(string::generateUuid()); } $tFilename = QPATH_APP . 'sessions/' . self::$id; if(self::$sessionLife > 0) { $tCookieLife = time() + self::$sessionLife; } else { $tCookieLife = 0; } setcookie(self::$sessionName, self::$id, $tCookieLife, '/'); io::writeSerialize($tFilename, array( 'data' => self::$data, 'flashdata' => self::$flashdata_next, 'lastmod' => time(), 'ip' => $_SERVER['REMOTE_ADDR'], 'ua' => $_SERVER['HTTP_USER_AGENT'] ), self::$keyphase ); self::$isModified = false; } public static function destroy() { if(!is_null(self::$data)) { self::open(); } if(is_null(self::$id)) { return; } $tFilename = QPATH_APP . 'sessions/' . self::$id; setcookie(self::$sessionName, '', time() - 3600, '/'); if(file_exists($tFilename)) { unlink($tFilename); } self::$id = null; self::$data = null; self::$flashdata_loaded = null; self::$isModified = false; } public static function get($uKey, $uDefault = null) { if(is_null(self::$data)) { self::open(); } if(!array_key_exists($uKey, self::$data)) { return $uDefault; } return self::$data[$uKey]; } public static function set($uKey, $uValue) { if(is_null(self::$data)) { self::open(); } self::$data[$uKey] = $uValue; self::$isModified = true; } public static function exists($uKey) { if(is_null(self::$data)) { self::open(); } return array_key_exists($uKey, self::$data); } public static function getFlash($uKey, $uDefault = null) { if(is_null(self::$data)) { self::open(); } if(!array_key_exists($uKey, self::$flashdata_loaded)) { return $uDefault; } return self::$flashdata_loaded[$uKey]; } public static function setFlash($uKey, $uValue) { self::$flashdata_next[$uKey] = $uValue; self::$isModified = true; } public static function keepFlash($uKey, $uDefault) { if(is_null(self::$data)) { self::open(); } if(!array_key_exists($uKey, self::$flashdata_loaded)) { self::$flashdata_next[$uKey] = $uDefault; } else { self::$flashdata_next[$uKey] = self::$flashdata_loaded[$uKey]; } self::$isModified = true; } public static function existsFlash($uKey) { if(is_null(self::$data)) { self::open(); } return array_key_exists($uKey, self::$flashdata_loaded); } } ?>
<?php
 class stopwatch { private static $markers = array(); public static function extension_info() { return array( 'name' => 'stopwatch', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function start($uName) { self::$markers[$uName] = microtime(true); } public static function stop($uName) { $tValue = self::$markers[$uName]; unset(self::$markers[$uName]); return microtime(true) - $tValue; } public static function get($uName) { return self::$markers[$uName]; } public static function set($uName, $uTime) { self::$markers[$uName] = $uTime; } public static function getlist() { return self::$markers; } } ?>
<?php
 class string { public static function extension_info() { return array( 'name' => 'string', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function filter($uVariable, $uFilter) { switch($uFilter) { case 'int': case 'integer': return intval($uVariable); break; case 'squote': return self::squote($uVariable); break; case 'dquote': return self::dquote($uVariable); break; case 'upper': return self::toUpper($uVariable); break; case 'lower': return self::toLower($uVariable); break; case 'num': case 'number': return number_format($uVariable); break; case 'html': return htmlspecialchars($uVariable); break; } return $uVariable; } public static function format($uString) { $tParms = func_get_args(); array_shift($tParms); if(is_array($tParms[0])) { $tParms = $tParms[0]; } $tBrackets = array(''); $tLastItem = 0; for($tPos = 0, $tLen = strlen($uString);$tPos < $tLen;$tPos++) { if($uString[$tPos] == '\\') { $tBrackets[$tLastItem] .= $uString[++$tPos]; continue; } $tLastItem = count($tBrackets) - 1; if($uString[$tPos] == '{') { $tBrackets[$tLastItem + 1] = ''; continue; } if($uString[$tPos] == '}' && $tLastItem > 0) { $tExploded = explode(':', $tBrackets[$tLastItem]); unset($tBrackets[$tLastItem]); $tString = $tParms[$tExploded[count($tExploded) - 1]]; for($i = 0, $tCount = count($tExploded) - 1;$i < $tCount;$i++) { $tString = self::filter($tString, $tExploded[$i]); } $tBrackets[$tLastItem - 1] .= $tString; continue; } $tBrackets[$tLastItem] .= $uString[$tPos]; } return $tBrackets[0]; } public static function vardump($uVariable) { $tVariable = $uVariable; $tType = gettype($tVariable); $tOut = ''; switch($tType) { case 'boolean': $tOut .= '<b>boolean</b>(' . (($tVariable) ? 'true' : 'false') . ')<br />'; break; case 'integer': case 'double': case 'string': $tOut .= '<b>' . $tType . '</b>(\'' . $tVariable . '\')<br />'; break; case 'array': case 'object': if($tType == 'object') { $tType = get_class($tVariable); $tVariable = @get_object_vars($tVariable); } $tCount = count($tVariable); $tOut .= '<b>' . $tType . '</b>(' . $tCount . ')'; if($tCount > 0) { $tOut .= ' {' . '<div style="padding: 0px 0px 0px 50px;">'; foreach($tVariable as $tKey => &$tVal) { $tOut .= '[' . $tKey . '] '; $tOut .= self::vardump($tVal); } $tOut .= '</div>}'; } $tOut .= '<br />'; break; case 'resource': $tOut .= '<b>resource</b>(\'' . get_resource_type($tVariable) . '\')<br />'; break; case 'NULL': $tOut .= '<b><i>null</i></b><br />'; break; case 'unknown type': default: $tOut .= 'unknown'; break; } return $tOut; } public static function generatePassword($uLength) { srand(microtime(true) * 1000000); static $aVowels = array('a', 'e', 'i', 'o', 'u'); static $aCons = array('b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'tr', 'cr', 'br', 'fr', 'th', 'dr', 'ch', 'ph', 'wr', 'st', 'sp', 'sw', 'pr', 'sl', 'cl'); $tConsLen = count($aCons) - 1; $tVowelsLen = count($aVowels) - 1; for($tOutput = '', $tLen = strlen($tOutput);$tLen < $uLength;) { $tOutput .= $aCons[rand(0, $tConsLen)] . $aVowels[rand(0, $tVowelsLen)]; } return substr($tOutput, 0, $uLength); } public static function generateUuid() { return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) ); } public static function generate($uLength, $uCharset = '0123456789ABCDEF') { srand(microtime(true) * 1000000); $tCharsetLen = strlen($uCharset) - 1; for($tOutput = '', $tLen = strlen($tOutput);$tLen < $uLength;) { $tOutput .= $uCharset[rand(0, $tCharsetLen)]; } return $tOutput; } public static function encrypt($uString, $uKey) { $tResult = ''; for($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) { $tChar = substr($uString, $i - 1, 1); $tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1); $tResult .= chr(ord($tChar) + ord($tKeyChar)); } return $tResult; } public static function decrypt($uString, $uKey) { $tResult = ''; for($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) { $tChar = substr($uString, $i - 1, 1); $tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1); $tResult .= chr(ord($tChar) - ord($tKeyChar)); } return $tResult; } public static function strip($uString, $uValids) { $tOutput = ''; for($tCount = 0, $tLen = strlen($uString);$tCount < $tLen;$tCount++) { if(strpos($uValids, $uString[$tCount]) === false) { continue; } $tOutput .= $uString[$tCount]; } return $tOutput; } public static function normalize($uString) { static $sTable = array( 'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', 'Ş'=>'S', 'ş'=>'s', 'İ'=>'I', 'ı'=>'i', 'Ğ'=>'G', 'ğ'=>'g', 'ü'=>'u' ); return strtr($uString, $sTable); } public static function squote($uString) { return strtr($uString, array('\\' => '\\\\', '\'' => '\\\'')); } public static function dquote($uString) { return strtr($uString, array('\\' => '\\\\', '"' => '\\"')); } public static function replaceBreaks($uString, $uBreaks = '<br />') { return strtr($uString, array("\r" => '', "\n" => $uBreaks)); } public static function cropText($uString, $uLength, $uContSign = '') { if(strlen($uString) <= $uLength) { return $uString; } return rtrim(substr($uString, 0, $uLength)) . $uContSign; } public static function encodeHtml($uString) { return strtr($uString, array('&' => '&amp;', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;')); } public static function decodeHtml($uString) { return strtr($uString, array('&amp;' => '&', '&quot;' => '"', '&lt;' => '<', '&gt;' => '>')); } public static function toLower($uString) { return strtolower($uString); } public static function toUpper($uString) { return strtoupper($uString); } public static function sizeCalc($uSize, $uPrecision = 0) { static $tSize = ' KMGT'; for($tCount = 0; $uSize >= 1024; $uSize /= 1024, $tCount++); return round($uSize, $uPrecision) . ' ' . $tSize[$tCount] . 'B'; } public static function htmlHighlight($uString, $uKeyword) { if($uKeyword == '') { return $uString; } $tPosition = strpos(self::toLower($uString), self::toLower($uKeyword)); if($tPosition === false) { return $uString; } return substr($uString, 0, $tPosition) . '<span style="background-color: yellow;">' . substr($uString, $tPosition, strlen($uKeyword)) . '</span>' . substr($uString, $tPosition + strlen($uKeyword)) ; } private static function readset_gquote($uString, &$uPosition) { $tInSlash = false; $tInQuote = false; $tOutput = ''; for($tLen = strlen($uString);$uPosition <= $tLen;++$uPosition) { if(($uString[$uPosition] == '\\') && !$tInSlash) { $tInSlash = true; continue; } if($uString[$uPosition] == '"') { if(!$tInQuote) { $tInQuote = true; continue; } if(!$tInSlash) { return $tOutput; } } $tOutput .= $uString[$uPosition]; $tInSlash = false; } return $tOutput; } public static function readset($uString) { $tStart = strpos($uString, '['); $tOutput = array(); $tBuffer = ''; if($tStart === false) { return $tOutput; } for($tLen = strlen($uString);$tStart <= $tLen;++$tStart) { if($uString[$tStart] == ']') { $tOutput[] = $tBuffer; $tBuffer = ''; return $tOutput; } if($uString[$tStart] == ',') { $tOutput[] = $tBuffer; $tBuffer = ''; continue; } if($uString[$tStart] == '"') { $tBuffer = self::readset_gquote($uString, $tStart); continue; } } return $tOutput; } public static function parseQueryString($uString, $uParameters = '&', $uKeys = '=') { $tParsed = array(); foreach(explode($uParameters, $uString) as $tParameter) { $tParameters = explode($uKeys, trim($tParameter), 2); if($tParameters[0] == '') { continue; } $tParsed[$tParameters[0]] = (isset($tParameters[1])) ? $tParameters[1] : ''; } return $tParsed; } } ?><?php
 class time { public static function extension_info() { return array( 'name' => 'time', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function gmdate($uFormat = null, $uTime = null, $uIsGMT = false) { if(!isset($uFormat)) { $uFormat = 'D, d M Y H:i:s'; } if(!isset($uTime)) { $uTime = time(); } return gmdate($uFormat, $uTime) . ($uIsGMT ? ' GMT' : ''); } public static function dostime($uTime = null) { if(!isset($uTime)) { $uTime = time(); } $tTimeArray = getdate($uTime); if($tTimeArray['year'] < 1980) { $tTimeArray['year'] = 1980; $tTimeArray['mon'] = 1; $tTimeArray['mday'] = 1; $tTimeArray['hours'] = 0; $tTimeArray['minutes'] = 0; $tTimeArray['seconds'] = 0; } return (($tTimeArray['year'] - 1980) << 25) | ($tTimeArray['mon'] << 21) | ($tTimeArray['mday'] << 16) | ($tTimeArray['hours'] << 11) | ($tTimeArray['minutes'] << 5) | ($tTimeArray['seconds'] >> 1); } public static function fromMysqlTime($uDate) { $tDate = sscanf($uDate, '%d-%d-%d %d:%d:%d'); return mktime($tDate[3], $tDate[4], $tDate[5], $tDate[1], $tDate[2], $tDate[0]); } } ?>
<?php
 class unittest { private static $stack = array(); private static $report = array(); public static function extension_info() { return array( 'name' => 'unittest', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array('string') ); } public static function beginClass($uClass) { $tMethods = get_class_methods($uClass); $tInstance = new $uClass (); foreach($tMethods as &$tMethod) { self::begin($uClass . '->' . $tMethod . '()', array(&$tInstance, $tMethod)); } } public static function begin($uName, $uCallback) { array_push(self::$stack, array('name' => $uName, 'callback' => $uCallback)); call_user_func($uCallback); array_pop(self::$stack); } private static function addReport($uOperation, $uIsFailed) { $tScope = end(self::$stack); if(!array_key_exists($tScope['name'], self::$report)) { self::$report[$tScope['name']] = array(); } self::$report[$tScope['name']][] = array( 'operation' => $uOperation, 'failed' => $uIsFailed ); } public static function assertTrue($uCondition) { if($uCondition) { self::addReport('assertTrue', true); return; } self::addReport('assertTrue', false); } public static function assertFalse($uCondition) { if(!$uCondition) { self::addReport('assertFalse', true); return; } self::addReport('assertFalse', false); } public static function assertNull($uVariable) { if(is_null($uVariable)) { self::addReport('assertNull', true); return; } self::addReport('assertNull', false); } public static function assertNotNull($uVariable) { if(!is_null($uVariable)) { self::addReport('assertNotNull', true); return; } self::addReport('assertNotNull', false); } public static function getlist() { return self::$report; } public static function export() { return string::vardump(self::$report); } } ?>
<?php
 class viewrenderer_markdown { private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: markdown', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_markdown::renderview')); self::$extension = Config::get('/markdown/templates/@extension', 'md'); self::$templatePath = QPATH_APP . Config::get('/markdown/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/markdown/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } $tInputFile = self::$templatePath . '/' . $uObject['viewFile'] . '.' . $uObject['viewExtension']; $tOutputFile = self::$compiledPath . '/md_' . $uObject['viewFile']; if(!file_exists($tOutputFile)) { if(is_null(self::$renderer)) { self::$renderer = new Markdown_Parser(); } $tInput = file_get_contents($tInputFile); $tOutput = self::$renderer->transform($tInput); file_put_contents($tOutputFile, $tOutput); } require($tOutputFile); } } @define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX', " />"); @define( 'MARKDOWN_TAB_WIDTH', 4 ); class Markdown_Parser { var $nested_brackets_depth = 6; var $nested_brackets_re; var $nested_url_parenthesis_depth = 4; var $nested_url_parenthesis_re; var $escape_chars = '\`*_{}[]()>#+-.!'; var $escape_chars_re; var $empty_element_suffix = MARKDOWN_EMPTY_ELEMENT_SUFFIX; var $tab_width = MARKDOWN_TAB_WIDTH; var $no_markup = false; var $no_entities = false; var $predef_urls = array(); var $predef_titles = array(); function Markdown_Parser() { $this->_initDetab(); $this->prepareItalicsAndBold(); $this->nested_brackets_re = str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth). str_repeat('\])*', $this->nested_brackets_depth); $this->nested_url_parenthesis_re = str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth). str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth); $this->escape_chars_re = '['.preg_quote($this->escape_chars).']'; asort($this->document_gamut); asort($this->block_gamut); asort($this->span_gamut); } var $urls = array(); var $titles = array(); var $html_hashes = array(); var $in_anchor = false; function setup() { $this->urls = $this->predef_urls; $this->titles = $this->predef_titles; $this->html_hashes = array(); $in_anchor = false; } function teardown() { $this->urls = array(); $this->titles = array(); $this->html_hashes = array(); } function transform($text) { $this->setup(); $text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text); $text = preg_replace('{\r\n?}', "\n", $text); $text .= "\n\n"; $text = $this->detab($text); $text = $this->hashHTMLBlocks($text); $text = preg_replace('/^[ ]+$/m', '', $text); foreach ($this->document_gamut as $method => $priority) { $text = $this->$method($text); } $this->teardown(); return $text . "\n"; } var $document_gamut = array( "stripLinkDefinitions" => 20, "runBasicBlockGamut" => 30, ); function stripLinkDefinitions($text) { $less_than_tab = $this->tab_width - 1; $text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm', array(&$this, '_stripLinkDefinitions_callback'), $text); return $text; } function _stripLinkDefinitions_callback($matches) { $link_id = strtolower($matches[1]); $url = $matches[2] == '' ? $matches[3] : $matches[2]; $this->urls[$link_id] = $url; $this->titles[$link_id] =& $matches[4]; return ''; } function hashHTMLBlocks($text) { if ($this->no_markup) return $text; $less_than_tab = $this->tab_width - 1; $block_tags_a_re = 'ins|del'; $block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'. 'script|noscript|form|fieldset|iframe|math'; $nested_tags_level = 4; $attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?	
			'; $content = str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level). '.*?'. str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |				
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*', $nested_tags_level); $content2 = str_replace('\2', '\3', $content); $text = preg_replace_callback('{(?>
			(?>
				(?<=\n\n)		# Starting after a blank line
				|				# or
				\A\n?			# the beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags 
			  # in between.
					
						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document
					
			| # Special case just for <hr />. It was easier to make a special 
			  # case than to make the other regex more complicated.
			
						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						'.$attr.'			# attributes
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # Special case for standalone HTML comments:
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # PHP and ASP-style processor instructions (<? and <%)
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
					
			)
			)}Sxmi', array(&$this, '_hashHTMLBlocks_callback'), $text); return $text; } function _hashHTMLBlocks_callback($matches) { $text = $matches[1]; $key = $this->hashBlock($text); return "\n\n$key\n\n"; } function hashPart($text, $boundary = 'X') { $text = $this->unhash($text); static $i = 0; $key = "$boundary\x1A" . ++$i . $boundary; $this->html_hashes[$key] = $text; return $key; } function hashBlock($text) { return $this->hashPart($text, 'B'); } var $block_gamut = array( "doHeaders" => 10, "doHorizontalRules" => 20, "doLists" => 40, "doCodeBlocks" => 50, "doBlockQuotes" => 60, ); function runBlockGamut($text) { $text = $this->hashHTMLBlocks($text); return $this->runBasicBlockGamut($text); } function runBasicBlockGamut($text) { foreach ($this->block_gamut as $method => $priority) { $text = $this->$method($text); } $text = $this->formParagraphs($text); return $text; } function doHorizontalRules($text) { return preg_replace( '{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx', "\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n", $text); } var $span_gamut = array( "parseSpan" => -30, "doImages" => 10, "doAnchors" => 20, "doAutoLinks" => 30, "encodeAmpsAndAngles" => 40, "doItalicsAndBold" => 50, "doHardBreaks" => 60, ); function runSpanGamut($text) { foreach ($this->span_gamut as $method => $priority) { $text = $this->$method($text); } return $text; } function doHardBreaks($text) { return preg_replace_callback('/ {2,}\n/', array(&$this, '_doHardBreaks_callback'), $text); } function _doHardBreaks_callback($matches) { return $this->hashPart("<br$this->empty_element_suffix\n"); } function doAnchors($text) { if ($this->in_anchor) return $text; $this->in_anchor = true; $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs', array(&$this, '_doAnchors_inline_callback'), $text); $text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text); $this->in_anchor = false; return $text; } function _doAnchors_reference_callback($matches) { $whole_match = $matches[1]; $link_text = $matches[2]; $link_id =& $matches[3]; if ($link_id == "") { $link_id = $link_text; } $link_id = strtolower($link_id); $link_id = preg_replace('{[ ]?\n}', ' ', $link_id); if (isset($this->urls[$link_id])) { $url = $this->urls[$link_id]; $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if ( isset( $this->titles[$link_id] ) ) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doAnchors_inline_callback($matches) { $whole_match = $matches[1]; $link_text = $this->runSpanGamut($matches[2]); $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $url = $this->encodeAttribute($url); $result = "<a href=\"$url\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $link_text = $this->runSpanGamut($link_text); $result .= ">$link_text</a>"; return $this->hashPart($result); } function doImages($text) { $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', array(&$this, '_doImages_reference_callback'), $text); $text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs', array(&$this, '_doImages_inline_callback'), $text); return $text; } function _doImages_reference_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $link_id = strtolower($matches[3]); if ($link_id == "") { $link_id = strtolower($alt_text); } $alt_text = $this->encodeAttribute($alt_text); if (isset($this->urls[$link_id])) { $url = $this->encodeAttribute($this->urls[$link_id]); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($this->titles[$link_id])) { $title = $this->titles[$link_id]; $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $this->empty_element_suffix; $result = $this->hashPart($result); } else { $result = $whole_match; } return $result; } function _doImages_inline_callback($matches) { $whole_match = $matches[1]; $alt_text = $matches[2]; $url = $matches[3] == '' ? $matches[4] : $matches[3]; $title =& $matches[7]; $alt_text = $this->encodeAttribute($alt_text); $url = $this->encodeAttribute($url); $result = "<img src=\"$url\" alt=\"$alt_text\""; if (isset($title)) { $title = $this->encodeAttribute($title); $result .= " title=\"$title\""; } $result .= $this->empty_element_suffix; return $this->hashPart($result); } function doHeaders($text) { $text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx', array(&$this, '_doHeaders_callback_setext'), $text); $text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm', array(&$this, '_doHeaders_callback_atx'), $text); return $text; } function _doHeaders_callback_setext($matches) { if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1])) return $matches[0]; $level = $matches[2]{0} == '=' ? 1 : 2; $block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function _doHeaders_callback_atx($matches) { $level = strlen($matches[1]); $block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>"; return "\n" . $this->hashBlock($block) . "\n\n"; } function doLists($text) { $less_than_tab = $this->tab_width - 1; $marker_ul_re = '[*+-]'; $marker_ol_re = '\d+[\.]'; $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)"; $markers_relist = array( $marker_ul_re => $marker_ol_re, $marker_ol_re => $marker_ul_re, ); foreach ($markers_relist as $marker_re => $other_marker_re) { $whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			# $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						# Lookahead for another kind of list
					    \n
						\3						# Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; if ($this->list_level) { $text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx', array(&$this, '_doLists_callback'), $text); } else { $text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx', array(&$this, '_doLists_callback'), $text); } } return $text; } function _doLists_callback($matches) { $marker_ul_re = '[*+-]'; $marker_ol_re = '\d+[\.]'; $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)"; $list = $matches[1]; $list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol"; $marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re ); $list .= "\n"; $result = $this->processListItems($list, $marker_any_re); $result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>"); return "\n". $result ."\n\n"; } var $list_level = 0; function processListItems($list_str, $marker_any_re) { $this->list_level++; $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str); $list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)							# leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm', array(&$this, '_processListItems_callback'), $list_str); $this->list_level--; return $list_str; } function _processListItems_callback($matches) { $item = $matches[4]; $leading_line =& $matches[1]; $leading_space =& $matches[2]; $marker_space = $matches[3]; $tailing_blank_line =& $matches[5]; if ($leading_line || $tailing_blank_line || preg_match('/\n{2,}/', $item)) { $item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item; $item = $this->runBlockGamut($this->outdent($item)."\n"); } else { $item = $this->doLists($this->outdent($item)); $item = preg_replace('/\n+$/', '', $item); $item = $this->runSpanGamut($item); } return "<li>" . $item . "</li>\n"; } function doCodeBlocks($text) { $text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm', array(&$this, '_doCodeBlocks_callback'), $text); return $text; } function _doCodeBlocks_callback($matches) { $codeblock = $matches[1]; $codeblock = $this->outdent($codeblock); $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES); $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock); $codeblock = "<pre><code>$codeblock\n</code></pre>"; return "\n\n".$this->hashBlock($codeblock)."\n\n"; } function makeCodeSpan($code) { $code = htmlspecialchars(trim($code), ENT_NOQUOTES); return $this->hashPart("<code>$code</code>"); } var $em_relist = array( '' => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![\.,:;]\s)', '*' => '(?<=\S|^)(?<!\*)\*(?!\*)', '_' => '(?<=\S|^)(?<!_)_(?!_)', ); var $strong_relist = array( '' => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![\.,:;]\s)', '**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)', '__' => '(?<=\S|^)(?<!_)__(?!_)', ); var $em_strong_relist = array( '' => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![\.,:;]\s)', '***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)', '___' => '(?<=\S|^)(?<!_)___(?!_)', ); var $em_strong_prepared_relist; function prepareItalicsAndBold() { foreach ($this->em_relist as $em => $em_re) { foreach ($this->strong_relist as $strong => $strong_re) { $token_relist = array(); if (isset($this->em_strong_relist["$em$strong"])) { $token_relist[] = $this->em_strong_relist["$em$strong"]; } $token_relist[] = $em_re; $token_relist[] = $strong_re; $token_re = '{('. implode('|', $token_relist) .')}'; $this->em_strong_prepared_relist["$em$strong"] = $token_re; } } } function doItalicsAndBold($text) { $token_stack = array(''); $text_stack = array(''); $em = ''; $strong = ''; $tree_char_em = false; while (1) { $token_re = $this->em_strong_prepared_relist["$em$strong"]; $parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE); $text_stack[0] .= $parts[0]; $token =& $parts[1]; $text =& $parts[2]; if (empty($token)) { while ($token_stack[0]) { $text_stack[1] .= array_shift($token_stack); $text_stack[0] .= array_shift($text_stack); } break; } $token_len = strlen($token); if ($tree_char_em) { if ($token_len == 3) { array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<strong><em>$span</em></strong>"; $text_stack[0] .= $this->hashPart($span); $em = ''; $strong = ''; } else { $token_stack[0] = str_repeat($token{0}, 3-$token_len); $tag = $token_len == 2 ? "strong" : "em"; $span = $text_stack[0]; $span = $this->runSpanGamut($span); $span = "<$tag>$span</$tag>"; $text_stack[0] = $this->hashPart($span); $$tag = ''; } $tree_char_em = false; } else if ($token_len == 3) { if ($em) { for ($i = 0; $i < 2; ++$i) { $shifted_token = array_shift($token_stack); $tag = strlen($shifted_token) == 2 ? "strong" : "em"; $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<$tag>$span</$tag>"; $text_stack[0] .= $this->hashPart($span); $$tag = ''; } } else { $em = $token{0}; $strong = "$em$em"; array_unshift($token_stack, $token); array_unshift($text_stack, ''); $tree_char_em = true; } } else if ($token_len == 2) { if ($strong) { if (strlen($token_stack[0]) == 1) { $text_stack[1] .= array_shift($token_stack); $text_stack[0] .= array_shift($text_stack); } array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<strong>$span</strong>"; $text_stack[0] .= $this->hashPart($span); $strong = ''; } else { array_unshift($token_stack, $token); array_unshift($text_stack, ''); $strong = $token; } } else { if ($em) { if (strlen($token_stack[0]) == 1) { array_shift($token_stack); $span = array_shift($text_stack); $span = $this->runSpanGamut($span); $span = "<em>$span</em>"; $text_stack[0] .= $this->hashPart($span); $em = ''; } else { $text_stack[0] .= $token; } } else { array_unshift($token_stack, $token); array_unshift($text_stack, ''); $em = $token; } } } return $text_stack[0]; } function doBlockQuotes($text) { $text = preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  (.+\n)*					# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm', array(&$this, '_doBlockQuotes_callback'), $text); return $text; } function _doBlockQuotes_callback($matches) { $bq = $matches[1]; $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq); $bq = $this->runBlockGamut($bq); $bq = preg_replace('/^/m', "  ", $bq); $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $bq); return "\n". $this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n"; } function _doBlockQuotes_callback2($matches) { $pre = $matches[1]; $pre = preg_replace('/^  /m', '', $pre); return $pre; } function formParagraphs($text) { $text = preg_replace('/\A\n+|\n+\z/', '', $text); $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY); foreach ($grafs as $key => $value) { if (!preg_match('/^B\x1A[0-9]+B$/', $value)) { $value = $this->runSpanGamut($value); $value = preg_replace('/^([ ]*)/', "<p>", $value); $value .= "</p>"; $grafs[$key] = $this->unhash($value); } else { $graf = $value; $block = $this->html_hashes[$graf]; $graf = $block; $grafs[$key] = $graf; } } return implode("\n\n", $grafs); } function encodeAttribute($text) { $text = $this->encodeAmpsAndAngles($text); $text = str_replace('"', '&quot;', $text); return $text; } function encodeAmpsAndAngles($text) { if ($this->no_entities) { $text = str_replace('&', '&amp;', $text); } else { $text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);; } $text = str_replace('<', '&lt;', $text); return $text; } function doAutoLinks($text) { $text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', array(&$this, '_doAutoLinks_url_callback'), $text); $text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi', array(&$this, '_doAutoLinks_email_callback'), $text); return $text; } function _doAutoLinks_url_callback($matches) { $url = $this->encodeAttribute($matches[1]); $link = "<a href=\"$url\">$url</a>"; return $this->hashPart($link); } function _doAutoLinks_email_callback($matches) { $address = $matches[1]; $link = $this->encodeEmailAddress($address); return $this->hashPart($link); } function encodeEmailAddress($addr) { $addr = "mailto:" . $addr; $chars = preg_split('/(?<!^)(?!$)/', $addr); $seed = (int)abs(crc32($addr) / strlen($addr)); foreach ($chars as $key => $char) { $ord = ord($char); if ($ord < 128) { $r = ($seed * (1 + $key)) % 100; if ($r > 90 && $char != '@') ; else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';'; else $chars[$key] = '&#'.$ord.';'; } } $addr = implode('', $chars); $text = implode('', array_slice($chars, 7)); $addr = "<a href=\"$addr\">$text</a>"; return $addr; } function parseSpan($str) { $output = ''; $span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs'; while (1) { $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE); if ($parts[0] != "") { $output .= $parts[0]; } if (isset($parts[1])) { $output .= $this->handleSpanToken($parts[1], $parts[2]); $str = $parts[2]; } else { break; } } return $output; } function handleSpanToken($token, &$str) { switch ($token{0}) { case "\\": return $this->hashPart("&#". ord($token{1}). ";"); case "`": if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', $str, $matches)) { $str = $matches[2]; $codespan = $this->makeCodeSpan($matches[1]); return $this->hashPart($codespan); } return $token; default: return $this->hashPart($token); } } function outdent($text) { return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text); } var $utf8_strlen = 'mb_strlen'; function detab($text) { $text = preg_replace_callback('/^.*\t.*$/m', array(&$this, '_detab_callback'), $text); return $text; } function _detab_callback($matches) { $line = $matches[0]; $strlen = $this->utf8_strlen; $blocks = explode("\t", $line); $line = $blocks[0]; unset($blocks[0]); foreach ($blocks as $block) { $amount = $this->tab_width - $strlen($line, 'UTF-8') % $this->tab_width; $line .= str_repeat(" ", $amount) . $block; } return $line; } function _initDetab() { if (function_exists($this->utf8_strlen)) return; $this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);'); } function unhash($text) { return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(&$this, '_unhash_callback'), $text); } function _unhash_callback($matches) { return $this->html_hashes[$matches[0]]; } } ?><?php
 class viewrenderer_php { private static $extension; private static $templatePath; public static function extension_info() { return array( 'name' => 'viewrenderer: php', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_php::renderview')); self::$extension = Config::get('/php/templates/@extension', 'php'); self::$templatePath = QPATH_APP . Config::get('/php/templates/@templatePath', 'views'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } $tInputFile = self::$templatePath . '/' . $uObject['viewFile'] . '.' . $uObject['viewExtension']; $model = &$uObject['model']; if(is_array($model)) { extract($model, EXTR_SKIP|EXTR_REFS); } extract($uObject['extra'], EXTR_SKIP|EXTR_REFS); require($tInputFile); } } ?><?php
 class viewrenderer_phptal { private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: phptal', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_phptal::renderview')); self::$extension = Config::get('/phptal/templates/@extension', 'zpt'); self::$templatePath = QPATH_APP . Config::get('/phptal/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/phptal/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } if(is_null(self::$renderer)) { $tPath = Config::get('/phptal/installation/@path', 'include/3rdparty/PHPTAL'); require($tPath . '/PHPTAL.php'); self::$renderer = new PHPTAL(); } else { unset(self::$renderer); self::$renderer = new PHPTAL(); } if(is_array($uObject['model'])) { foreach($uObject['model'] as $tKey => &$tValue) { self::$renderer->set($tKey, $tValue); } } else { self::$renderer->set('model', $uObject['model']); } foreach($uObject['extra'] as $tKey => &$tValue) { self::$renderer->set($tKey, $tValue); } self::$renderer->setForceReparse(false); self::$renderer->setTemplateRepository(self::$templatePath . '/'); self::$renderer->setPhpCodeDestination(self::$compiledPath . '/'); self::$renderer->setOutputMode(PHPTAL::HTML5); self::$renderer->setEncoding('UTF-8'); self::$renderer->setTemplate($uObject['viewFile'] . '.' . $uObject['viewExtension']); self::$renderer->echoExecute(); } } ?><?php
 class viewrenderer_raintpl { private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: raintpl', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_raintpl::renderview')); self::$extension = Config::get('/raintpl/templates/@extension', 'rain'); self::$templatePath = QPATH_APP . Config::get('/raintpl/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/raintpl/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } if(is_null(self::$renderer)) { $tPath = Config::get('/raintpl/installation/@path', 'include/3rdparty/raintpl/inc'); require($tPath . '/rain.tpl.class.php'); raintpl::configure('base_url', null); raintpl::configure('tpl_dir', self::$templatePath . '/'); raintpl::configure('tpl_ext', self::$extension); raintpl::configure('cache_dir', self::$compiledPath . '/'); self::$renderer = new RainTPL(); } else { self::$renderer = new RainTPL(); } if(is_array($uObject['model'])) { foreach($uObject['model'] as $tKey => &$tValue) { self::$renderer->assign($tKey, $tValue); } } else { self::$renderer->assign('model', $uObject['model']); } foreach($uObject['extra'] as $tKey => &$tValue) { self::$renderer->assign($tKey, $tValue); } self::$renderer->draw($uObject['viewFile']); } } ?><?php
 class viewrenderer_razor { private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: razor', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_razor::renderview')); self::$extension = Config::get('/razor/templates/@extension', 'cshtml'); self::$templatePath = QPATH_APP . Config::get('/razor/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/razor/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } $tInputFile = self::$templatePath . '/' . $uObject['viewFile'] . '.' . $uObject['viewExtension']; $tOutputFile = self::$compiledPath . '/rzr_' . $uObject['viewFile']; if(!file_exists($tOutputFile)) { if(is_null(self::$renderer)) { self::$renderer = new RazorViewRenderer(); } self::$renderer->generateViewFile($tInputFile, $tOutputFile); } $model = &$uObject['model']; if(is_array($model)) { extract($model, EXTR_SKIP|EXTR_REFS); } extract($uObject['extra'], EXTR_SKIP|EXTR_REFS); require($tOutputFile); } } class RazorViewRenderer { private $_input; private $_output; private $_sourceFile; public function generateViewFile($sourceFile, $viewFile) { $this->_sourceFile = $sourceFile; $this->_input = file_get_contents($sourceFile); $this->_output = "<?php /* source file: {$sourceFile} */ ?>\n"; $this->parse(0, strlen($this->_input)); file_put_contents($viewFile, $this->_output); } private function parse($beginBlock, $endBlock) { $offset = $beginBlock; while (($p = strpos($this->_input, "@", $offset)) !== false && $p < $endBlock) { if ($this->isNextToken($p, $endBlock, "@")) { $this->_output .= substr($this->_input, $offset, $p - $offset + 1); $offset = $p + 2; continue; } if ($this->isNextToken($p, $endBlock, "(")) { $end = $this->findClosingBracket($p + 1, $endBlock, "(", ")"); $this->_output .= substr($this->_input, $offset, $p - $offset); $this->generatePHPOutput($p, $end); $offset = $end + 1; continue; } if ($this->isNextToken($p, $endBlock, "{")) { $end = $this->findClosingBracket($p + 1, $endBlock, "{", "}"); $this->_output .= substr($this->_input, $offset, $p - $offset); $this->_output .= "<?php " . substr($this->_input, $p + 2, $end - $p - 2) . " ?>"; $offset = $end + 1; continue; } if ($this->isNextToken($p, $endBlock, ":")) { $statement = $this->detectStatement($p + 2, $endBlock); $end = $this->findEndStatement($p + 1 + strlen($statement), $endBlock); $this->_output .= substr($this->_input, $offset, $p - $offset); $this->generatePHPOutput($p + 1, $end, true); $offset = $end + 1; continue; } $statement = $this->detectStatement($p + 1, $endBlock); if ($statement == "foreach" || $statement == "for" || $statement == "while") { $offset = $this->processLoopStatement($p, $offset, $endBlock, $statement); } elseif ($statement == "if") { $offset = $this->processIfStatement($p, $offset, $endBlock, $statement); } else { $end = $this->findEndStatement($p + strlen($statement), $endBlock); $this->_output .= substr($this->_input, $offset, $p - $offset); $this->generatePHPOutput($p, $end); $offset = $end + 1; } } $this->_output .= substr($this->_input, $offset, $endBlock - $offset); } private function generatePHPOutput($currentPosition, $endPosition, $htmlEncode = false) { $this->_output .= "<?php echo " . ($htmlEncode ? "CHtml::encode(" : "") . substr($this->_input, $currentPosition + 1, $endPosition - $currentPosition) . ($htmlEncode ? ")" : "") . "; ?>"; } private function processLoopStatement($currentPosition, $offset, $endBlock, $statement) { if (($bracketPosition = $this->findOpenBracketAtLine($currentPosition + 1, $endBlock)) === false) { throw new RazorViewRendererException("Cannot find open bracket for '{$statement}' statement.", $this->_sourceFile, $this->getLineNumber($currentPosition)); } $this->_output .= substr($this->_input, $offset, $currentPosition - $offset); $this->_output .= "<?php " . substr($this->_input, $currentPosition + 1, $bracketPosition - $currentPosition) . " ?>"; $offset = $bracketPosition + 1; $end = $this->findClosingBracket($bracketPosition, $endBlock, "{", "}"); $this->parse($offset, $end); $this->_output .= "<?php } ?>"; return $end + 1; } private function processIfStatement($currentPosition, $offset, $endBlock, $statement) { $bracketPosition = $this->findOpenBracketAtLine($currentPosition + 1, $endBlock); if ($bracketPosition === false) { throw new RazorViewRendererException("Cannot find open bracket for '{$statement}' statement.", $this->_sourceFile, $this->getLineNumber($currentPosition)); } $this->_output .= substr($this->_input, $offset, $currentPosition - $offset); $start = $currentPosition + 1; while (true) { $this->_output .= "<?php " . substr($this->_input, $start, $bracketPosition - $start + 1) . " ?>"; $offset = $bracketPosition + 1; $end = $this->findClosingBracket($bracketPosition, $endBlock, "{", "}"); $this->parse($offset, $end); $offset = $end + 1; $bracketPosition = $this->findOpenBracketAtLine($offset, $endBlock); if ($bracketPosition === false) { $this->_output .= "<?php } ?>"; break; } $start = $end; } return $offset; } private function findOpenBracketAtLine($currentPosition, $endBlock) { $openDoubleQuotes = false; $openSingleQuotes = false; for ($p = $currentPosition; $p < $endBlock; ++$p) { if ($this->_input[$p] == "\n") { return false; } $quotesNotOpened = !$openDoubleQuotes && !$openSingleQuotes; if ($this->_input[$p] == '"') { $openDoubleQuotes = $this->getQuotesState($openDoubleQuotes, $quotesNotOpened, $p); } elseif ($this->_input[$p] == "'") { $openSingleQuotes = $this->getQuotesState($openSingleQuotes, $quotesNotOpened, $p); } elseif ($this->_input[$p] == "{" && $quotesNotOpened) { return $p; } } return false; } private function isNextToken($currentPosition, $endBlock, $token) { return $currentPosition + strlen($token) < $endBlock && substr($this->_input, $currentPosition + 1, strlen($token)) == $token; } private function isEscaped($currentPosition) { $cntBackSlashes = 0; for ($p = $currentPosition - 1; $p >= 0; --$p) { if ($this->_input[$p] != "\\") { break; } ++$cntBackSlashes; } return $cntBackSlashes % 2 == 1; } private function getQuotesState($testedQuotes, $quotesNotOpened, $currentPosition) { if ($quotesNotOpened) { return true; } return $testedQuotes && !$this->isEscaped($currentPosition) ? false: $testedQuotes; } private function findClosingBracket($openBracketPosition, $endBlock, $openBracket, $closeBracket) { $opened = 0; $openDoubleQuotes = false; $openSingleQuotes = false; for ($p = $openBracketPosition; $p < $endBlock; ++$p) { $quotesNotOpened = !$openDoubleQuotes && !$openSingleQuotes; if ($this->_input[$p] == '"') { $openDoubleQuotes = $this->getQuotesState($openDoubleQuotes, $quotesNotOpened, $p); } elseif ($this->_input[$p] == "'") { $openSingleQuotes = $this->getQuotesState($openSingleQuotes, $quotesNotOpened, $p); } elseif ($this->_input[$p] == $openBracket && $quotesNotOpened) { $opened++; } elseif ($this->_input[$p] == $closeBracket && $quotesNotOpened) { if (--$opened == 0) { return $p; } } } throw new RazorViewRendererException("Cannot find closing bracket.", $this->_sourceFile, $this->getLineNumber($openBracketPosition)); } private function findEndStatement($endPosition, $endBlock) { if ($this->isNextToken($endPosition, $endBlock, "(")) { $endPosition = $this->findClosingBracket($endPosition + 1, $endBlock, "(", ")"); $endPosition = $this->findEndStatement($endPosition, $endBlock); } elseif ($this->isNextToken($endPosition, $endBlock, "[")) { $endPosition = $this->findClosingBracket($endPosition + 1, $endBlock, "[", "]"); $endPosition = $this->findEndStatement($endPosition, $endBlock); } elseif ($this->isNextToken($endPosition, $endBlock, "->")) { $endPosition += 2; $statement = $this->detectStatement($endPosition + 1, $endBlock); $endPosition = $this->findEndStatement($endPosition + strlen($statement), $endBlock); } elseif ($this->isNextToken($endPosition, $endBlock, "::")) { $endPosition += 2; $statement = $this->detectStatement($endPosition + 1, $endBlock); $endPosition = $this->findEndStatement($endPosition + strlen($statement), $endBlock); } return $endPosition; } private function detectStatement($currentPosition, $endBlock) { $invalidCharPosition = $endBlock; for ($p = $currentPosition; $p < $invalidCharPosition; ++$p) { if ($this->_input[$p] == "$" && $p == $currentPosition) { continue; } if (preg_match('/[a-zA-Z0-9_]/', $this->_input[$p])) { continue; } $invalidCharPosition = $p; break; } if ($currentPosition == $invalidCharPosition) { throw new RazorViewRendererException("Cannot detect statement.", $this->_sourceFile, $this->getLineNumber($currentPosition)); } return substr($this->_input, $currentPosition, $invalidCharPosition - $currentPosition); } private function getLineNumber($currentPosition) { return count(explode("\n", substr($this->_input, 0, $currentPosition))); } } class RazorViewRendererException { public function __construct($message, $templateFileName, $line) { parent::__construct("Invalid view template: {$templateFileName}, at line {$line}. {$message}", null, null); } } ?><?php
 class viewrenderer_smarty { private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: smarty', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_smarty::renderview')); self::$extension = Config::get('/smarty/templates/@extension', 'tpl'); self::$templatePath = QPATH_APP . Config::get('/smarty/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/smarty/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } if(is_null(self::$renderer)) { $tPath = Config::get('/smarty/installation/@path', 'include/3rdparty/smarty/libs'); require($tPath . '/Smarty.class.php'); self::$renderer = new Smarty(); self::$renderer->setTemplateDir(self::$templatePath . '/'); self::$renderer->setCompileDir(self::$compiledPath . '/'); } else { self::$renderer->clearAllAssign(); } if(is_array($uObject['model'])) { foreach($uObject['model'] as $tKey => &$tValue) { self::$renderer->assignByRef($tKey, $tValue); } } else { self::$renderer->assignByRef('model', $uObject['model']); } foreach($uObject['extra'] as $tKey => &$tValue) { self::$renderer->assignByRef($tKey, $tValue); } self::$renderer->display($uObject['viewFile'] . '.' . $uObject['viewExtension']); } } ?><?php
 class viewrenderer_twig { private static $loader = null; private static $renderer = null; private static $extension; private static $templatePath; private static $compiledPath; public static function extension_info() { return array( 'name' => 'viewrenderer: twig', 'version' => '1.0.2', 'phpversion' => '5.1.0', 'phpdepends' => array(), 'fwversion' => '1.0', 'fwdepends' => array() ); } public static function extension_load() { Events::register('renderview', Events::Callback('viewrenderer_twig::renderview')); self::$extension = Config::get('/twig/templates/@extension', 'twig'); self::$templatePath = QPATH_APP . Config::get('/twig/templates/@templatePath', 'views'); self::$compiledPath = QPATH_APP . Config::get('/twig/templates/@compiledPath', 'views/compiled'); } public static function renderview($uObject) { if($uObject['viewExtension'] != self::$extension) { return; } if(is_null(self::$renderer)) { $tPath = Config::get('/twig/installation/@path', 'include/3rdparty/twig/lib/Twig'); require($tPath . '/Autoloader.php'); Twig_Autoloader::register(); self::$loader = new Twig_Loader_Filesystem(self::$templatePath); self::$renderer = new Twig_Environment(self::$loader, array( 'cache' => self::$compiledPath )); } echo self::$renderer->render($uObject['viewFile'] . '.' . $uObject['viewExtension'], array_combine($uObject['model'], $uObject['extra'])); } } ?><?php
 class docs extends Controller { public function index() { $this->loadview('docs_index.md'); } } ?><?php
 class home extends Controller { private $limit = 200; public function __construct() { session_start(); } public function login() { unset($_SESSION['logged']); $tViewbag = array( 'title' => 'Login' ); $this->loadview('home_login.cshtml', $tViewbag); } public function login_post() { $this->loadmodel('accountsModel', 'accounts'); if(!$this->accounts->checkLogin($_POST['name'], $_POST['password'])) { return $this->error('username/password error'); } $_SESSION['logged'] = true; http::sendRedirect($_SERVER['PHP_SELF'] . '?home/index'); } public function index() { if(!isset($_SESSION['logged'])) { return $this->login(); } $this->loadmodel('usersModel', 'users'); $tCurrentPage = $this->httpGet(2, 1, 'int'); if($tCurrentPage <= 0) { $tCurrentPage = 1; } $tTotal = $this->users->count(); $tDataSet = $this->users->get(($tCurrentPage - 1) * $this->limit, $this->limit); $tViewbag = array( 'title' => 'List of Accounts', 'link_back' => string::format('{num:0} records listed in {num:1} pages', $tTotal, ceil($tTotal / $this->limit)) ); $tViewbag['pagination'] = html::pager(array( 'total' => $tTotal, 'pagesize' => $this->limit, 'current' => $tCurrentPage, 'numlinks' => 20, 'link' => '<a href="{baseurl}?home/index/{page}" class="pagerlink">{pagetext}</a>', 'activelink' => '<span class="pagerlink_active">{pagetext}</span>', 'passivelink' => '<span class="pagerlink_passive">{pagetext}</span>', 'firstlast' => true )); $tViewbag['table'] = html::table(array( 'data' => $tDataSet, 'headers' => array( 'Profile', 'E-Mail', 'Name', 'Locale', 'Gender', 'Registered' ), 'rowFunc' => Events::Callback('home::tableRow') )); $this->loadview('home_index.cshtml', $tViewbag); } public function error($uMsg) { $tViewbag = array( 'title' => 'Error', 'message' => $uMsg ); $this->loadview('shared_error.cshtml', $tViewbag); } public function notfound() { return $this->error('404 not found!'); } public static function tableRow($uRow) { switch($uRow['Locale']) { case 'tr_TR': $tLocale = 'Turkey'; break; case 'de_DE': $tLocale = 'Germany'; break; case 'ru_RU': $tLocale = 'Russia'; break; case 'nl_NL': $tLocale = 'Netherlands'; break; case 'en_US': $tLocale = 'United States'; break; case 'en_GB': $tLocale = 'United Kingdom'; break; case 'fr_FR': $tLocale = 'France'; break; default: $tLocale = &$uRow['Locale']; break; } switch($uRow['Gender']) { case '1': $tGender = 'Female'; break; case '2': $tGender = 'Male'; break; case '0': default: $tGender = '-'; break; } $tResult = '<tr>'; if(!empty($uRow['ImgPath'])) { $tResult .= '<td><a href="https://www.facebook.com/profile.php?id=' . $uRow['facebookid'] . '"><img src="' . $uRow['ImgPath'] . '" border="0" alt="Facebook Profile" /></a></td>'; } else { $tResult .= '<td></td>'; } $tResult .= '<td><a href="mailto:' . $uRow['EMail'] . '">' . $uRow['EMail'] . '</a></td>'; $tResult .= '<td><a href="https://www.facebook.com/profile.php?id=' . $uRow['facebookid'] . '">' . $uRow['LongName'] . '</a></td>'; $tResult .= '<td>' . $tLocale . '</td>'; $tResult .= '<td>' . $tGender . '</td>'; if(!empty($uRow['RecDate'])) { $tResult .= '<td>' . date('d-m-Y H:i', $uRow['RecDate']) . '</td>'; } else { $tResult .= '<td>-</td>'; } $tResult .= '</tr>'; return $tResult; } } ?><?php
 class tests extends Controller { public function index() { contracts::check(1 == 1); $viewbag = array('deneme' => 'problem'); $this->loadview('tests_temp.cshtml', $viewbag); } public function notfound() { echo '404 not found!'; } public function phptal() { $viewbag = array('deneme' => 'testing phptal'); $this->loadview('tests_temp.zpt', $viewbag); } public function smarty() { $viewbag = array('deneme' => 'testing smarty'); $this->loadview('tests_temp.tpl', $viewbag); } public function raintpl() { $viewbag = array('deneme' => 'testing raintpl'); $this->loadview('tests_temp.rain', $viewbag); } public function twig() { $viewbag = array('deneme' => 'testing twig'); $this->loadview('tests_temp.twig', $viewbag); } public function version() { echo SCABBIA_VERSION; } public function extensions() { Extensions::dump(); } public function config() { echo '<pre>'; Config::dump(); } public function get() { echo string::vardump($_GET); } public function browser() { echo '<pre>'; echo http::getPlatform(); echo '<br />'; echo http::getCrawler(); } public function languages() { echo string::vardump(http::getLanguages()); } public function ucaser($uObject) { $uObject['content'] = strtoupper($uObject['content']); } public function output() { Events::register('output', Events::Callback('ucaser', $this)); output::begin('ucase'); echo 'output sample<br />'; output::end(); } public function mvc() { echo MVC::getController(); echo '<br />'; echo MVC::getAction(); echo '<br />'; echo $_GET[2]; } public function msec() { echo microtime(true) - QTIME_INIT; } public function database() { echo string::vardump(database::get('dbconn', 'dbs')->query('testtable')); echo string::vardump(database::get('dbconn')); } public function accounts() { $this->loadmodel('testModel'); echo $this->testModel->delete(); echo '<br />'; echo $this->testModel->insert(); echo '<br />'; echo string::vardump($this->testModel->get()); echo '<br />'; echo string::vardump($this->testModel->getRow()); echo '<br />'; echo string::vardump($this->testModel->getScalar()); echo '<br />'; } } ?><?php
 class users extends Controller { public function unsubscribe() { $tEmail = $this->httpGet(2, ''); if(empty($tEmail)) { return $this->error('user is empty'); } $this->loadmodel('usersModel', 'users'); $tResult = $this->users->unsubscribe($tEmail); $tViewbag = array( 'title' => 'Done' ); if($tResult) { $tViewbag['message'] = 'Your e-mail is unsubscribed from mailing list. You won\'t get any notification mails from now on.'; } else { $tViewbag['message'] = 'Your e-mail has already been unsubscribed from mailing list.'; } $this->loadview('shared_error.cshtml', $tViewbag); } public function image() { $tCampaign = $this->httpGet(2, ''); if(empty($tCampaign)) { return $this->error('campaign is empty'); } $tUserId = $this->httpGet(3, ''); if(empty($tUserId)) { return $this->error('user is empty'); } $this->loadmodel('usersModel', 'users'); $this->users->logCampaignView($tUserId, $tCampaign, 2); http::sendFile(QPATH_CORE . 'res/eposta.png'); } public function content() { $tCampaign = $this->httpGet(2, ''); if(empty($tCampaign)) { return $this->error('campaign is empty'); } $tUserId = $this->httpGet(3, ''); if(empty($tUserId)) { return $this->error('user is empty'); } $this->loadmodel('usersModel', 'users'); $tUser = $this->users->getSingle($tUserId); if(is_null($tUser)) { return $this->error('user is not exists'); } $this->users->logCampaignView($tUserId, $tCampaign, 1); $tViewbag = array( 'title' => $tUser['LongName'], 'longname' => $tUser['LongName'], 'email' => $tUser['EMail'], 'facebookid' => $tUser['facebookid'], 'imgpath' => $tUser['ImgPath'], 'gender' => $tUser['Gender'], 'locale' => $tUser['Locale'], 'recdate' => $tUser['RecDate'], 'campaign' => $tCampaign, 'userid' => $tUserId, 'image' => $_SERVER['PHP_SELF'] . '?users/image/' . $tCampaign . '/' . $tUserId ); $this->loadview('users_content.cshtml', $tViewbag); } public function error($uMsg) { $tViewbag = array( 'title' => 'Error', 'message' => $uMsg ); $this->loadview('shared_error.cshtml', $tViewbag); } public function notfound() { return $this->error('404 not found!'); } } ?><?php
 class accountsModel extends Model { function checkLogin($uName, $uPassword) { $tPassword = database::get('dbconn', 'getLoginPassword')->queryScalar($uName); if(!is_null($tPassword) && $tPassword == md5($uPassword)) { return true; } return false; } } ?><?php
 class testModel extends Model { public function __construct($uController) { parent::__construct($uController); $this->db->setDatabaseName('dbconn'); } function insert() { return $this->db ->setTable('testtable') ->addField('name', 'test3') ->insert(); } function update() { return $this->db ->setTable('testtable') ->setFields(array('isim' => 'eser', 'soyisim' => 'ozvataf')) ->addField('yas', '27') ->setWhere('id=1') ->andWhere('level<3') ->setLimit(1) ->update(); } function delete() { return $this->db ->setTable('testtable') ->setWhere('name=:name') ->addParameter(':name', 'test3') ->setLimit(1) ->delete(); } function get($uLimit, $uOffset) { return $this->db ->setTable('testtable') ->setLimit($uLimit) ->setOffset($uOffset) ->get(); } function count() { return $this->db->calculate('testtable', 'COUNT'); } function getRow() { return $this->db ->setTable('testtable') ->setWhere('name=\'test\'') ->getRow(); } function getScalar() { return $this->db ->setTable('testtable') ->setFieldsDirect(array('name')) ->setWhere('name=\'test\'') ->getScalar(); } } ?><?php
 class usersModel extends Model { function get($uOffset, $uLimit) { $tUsers = database::get('dbconn', 'getUsers')->querySet($uOffset, $uLimit); return $tUsers; } function getSingle($uUserId) { return database::get('dbconn', 'getSingleUser')->queryRow($uUserId); } function count() { $tCount = database::get('dbconn', 'getUserCount')->queryScalar(); return (int)$tCount; } function unsubscribe($uEmail) { return database::get('dbconn', 'setUserUnsubscribed')->query($uEmail); } function logCampaignView($uUserId, $uCampaign, $uOperation) { try { return database::get('dbconn', 'logCampaignView')->query($uUserId, $uCampaign, $uOperation); } catch(PDOException $ex) { return false; } } } ?><?php Config::set(array (
  '/databaseList' => 
  array (
    0 => 
    array (
      'cachePath' => 
      array (
        '.' => 'cache/',
      ),
      'persistent' => 
      array (
        '.' => NULL,
      ),
      'overrideCase' => 
      array (
        '.' => 'natural',
      ),
      'pdoString' => 
      array (
        '.' => 'mysql:host=localhost;dbname=test',
      ),
      'username' => 
      array (
        '.' => 'postgres',
      ),
      'password' => 
      array (
        '.' => 'passwd',
      ),
      'initCommand' => 
      array (
        '.' => '
				SET NAMES utf8',
      ),
      'datasetList' => 
      array (
        0 => 
        array (
          '@id' => 'getUserCount',
          '@cacheLife' => '15',
          '@parameters' => '',
          '.' => '
					SELECT COUNT(*) FROM users',
        ),
        1 => 
        array (
          '@id' => 'getUsers',
          '@cacheLife' => '15',
          '@parameters' => 'offset,limit',
          '.' => '
					SELECT facebookid, EMail, LongName, ImgPath, Gender, Locale, UNIX_TIMESTAMP(RecDate) AS RecDate FROM users LIMIT {offset}, {limit}',
        ),
        2 => 
        array (
          '@id' => 'getSingleUser',
          '@cacheLife' => '15',
          '@parameters' => 'uuid',
          '.' => '
					SELECT facebookid, EMail, LongName, ImgPath, Gender, Locale, UNIX_TIMESTAMP(RecDate) AS RecDate FROM users WHERE uuid=\'{squote:uuid}\' LIMIT 0, 1',
        ),
        3 => 
        array (
          '@id' => 'setUserUnsubscribed',
          '@parameters' => 'email',
          '.' => '
					UPDATE users SET unsubscribed=\'1\' WHERE EMail=\'{squote:email}\' LIMIT 1',
        ),
        4 => 
        array (
          '@id' => 'getLoginPassword',
          '@cacheLife' => '15',
          '@parameters' => 'name',
          '.' => '
					SELECT password FROM accounts WHERE name=\'{squote:name}\' LIMIT 0, 1',
        ),
        5 => 
        array (
          '@id' => 'logCampaignView',
          '@parameters' => 'userid,campaign,operation',
          '.' => '
					INSERT INTO userrefs (userid, campaign, operation, insertdate) VALUES (\'{squote:userid}\', \'{squote:campaign}\', \'{int:operation}\', CURRENT_TIMESTAMP())',
        ),
        6 => 
        array (
          '@id' => 'getCsvOutput',
          '@parameters' => 'offset,limit',
          '.' => '
					SELECT uuid, LongName, EMail FROM users LIMIT {int:offset}, {int:limit}',
        ),
      ),
      '@id' => 'dbconn',
      '@default' => 'default',
      '@keyphase' => 'test',
      '.' => NULL,
    ),
  ),
  '/options/development/@value' => '1',
  '/options/debug/@value' => '0',
  '/options/gzip/@value' => '1',
  '/downloadList' => 
  array (
  ),
  '/includeList' => 
  array (
    0 => 
    array (
      '@path' => '{core}extensions/*.php',
      '.' => NULL,
    ),
    1 => 
    array (
      '@path' => '{app}downloaded/*.php',
      '.' => NULL,
    ),
    2 => 
    array (
      '@path' => '{app}controllers/*.php',
      '.' => NULL,
    ),
    3 => 
    array (
      '@path' => '{app}models/*.php',
      '.' => NULL,
    ),
  ),
  '/extensionList' => 
  array (
    0 => 
    array (
      '@name' => 'string',
      '.' => NULL,
    ),
    1 => 
    array (
      '@name' => 'io',
      '.' => NULL,
    ),
    2 => 
    array (
      '@name' => 'http',
      '.' => NULL,
    ),
    3 => 
    array (
      '@name' => 'time',
      '.' => NULL,
    ),
    4 => 
    array (
      '@name' => 'collections',
      '.' => NULL,
    ),
    5 => 
    array (
      '@name' => 'contracts',
      '.' => NULL,
    ),
    6 => 
    array (
      '@name' => 'unittest',
      '.' => NULL,
    ),
    7 => 
    array (
      '@name' => 'database',
      '.' => NULL,
    ),
    8 => 
    array (
      '@name' => 'session',
      '.' => NULL,
    ),
    9 => 
    array (
      '@name' => 'output',
      '.' => NULL,
    ),
    10 => 
    array (
      '@name' => 'repository',
      '.' => NULL,
    ),
    11 => 
    array (
      '@name' => 'mvc',
      '.' => NULL,
    ),
    12 => 
    array (
      '@name' => 'logger',
      '.' => NULL,
    ),
    13 => 
    array (
      '@name' => 'html',
      '.' => NULL,
    ),
    14 => 
    array (
      '@name' => 'viewrenderer_razor',
      '.' => NULL,
    ),
    15 => 
    array (
      '@name' => 'viewrenderer_markdown',
      '.' => NULL,
    ),
    16 => 
    array (
      '@name' => 'stopwatch',
      '.' => NULL,
    ),
  ),
  '/languageList' => 
  array (
    0 => 
    array (
      '@id' => 'tr',
      '.' => 'Turkish',
    ),
    1 => 
    array (
      '@id' => 'en',
      '.' => 'English',
    ),
  ),
  '/logger/@filename' => 'd-m-Y',
  '/http/request/@parsingType' => '2',
  '/http/request/@getParameters' => ',',
  '/http/request/@getKeys' => ':',
  '/http/rewriteList' => 
  array (
    0 => 
    array (
      '@match' => '(\\w+)/contacts',
      '@forward' => 'home/mvc/$1/why',
      '.' => NULL,
    ),
  ),
  '/http/ipFilterList' => 
  array (
    0 => 
    array (
      '@type' => 'deny',
      '@pattern' => '127.0.0.?',
      '.' => NULL,
    ),
    1 => 
    array (
      '@type' => 'allow',
      '@pattern' => '*.*.*.*',
      '.' => NULL,
    ),
  ),
  '/http/userAgents/platformList' => 
  array (
    0 => 
    array (
      '@match' => 'windows|winnt|win95|win98',
      '@name' => 'Windows',
      '.' => NULL,
    ),
    1 => 
    array (
      '@match' => 'os x|ppc mac|ppc',
      '@name' => 'MacOS',
      '.' => NULL,
    ),
    2 => 
    array (
      '@match' => 'freebsd',
      '@name' => 'FreeBSD',
      '.' => NULL,
    ),
    3 => 
    array (
      '@match' => 'linux|debian|gnu',
      '@name' => 'Linux',
      '.' => NULL,
    ),
    4 => 
    array (
      '@match' => 'sunos',
      '@name' => 'Solaris',
      '.' => NULL,
    ),
    5 => 
    array (
      '@match' => 'irix|netbsd|openbsd|bsdi|unix',
      '@name' => 'Unix',
      '.' => NULL,
    ),
  ),
  '/http/userAgents/crawlerList' => 
  array (
    0 => 
    array (
      '@type' => 'bot',
      '@match' => 'googlebot|msnbot|slurp|yahoo|askjeeves|fastcrawler|infoseek|lycos',
      '@name' => 'Searchbot',
      '.' => NULL,
    ),
    1 => 
    array (
      '@type' => 'browser',
      '@match' => 'Opera',
      '@name' => 'Opera',
      '.' => NULL,
    ),
    2 => 
    array (
      '@type' => 'browser',
      '@match' => 'Mozilla|Firefox|Firebird|Phoenix',
      '@name' => 'Firefox',
      '.' => NULL,
    ),
    3 => 
    array (
      '@type' => 'browser',
      '@match' => 'MSIE|Internet Explorer',
      '@name' => 'Internet Explorer',
      '.' => NULL,
    ),
    4 => 
    array (
      '@type' => 'browser',
      '@match' => 'Flock',
      '@name' => 'Flock',
      '.' => NULL,
    ),
    5 => 
    array (
      '@type' => 'browser',
      '@match' => 'Chrome',
      '@name' => 'Chrome',
      '.' => NULL,
    ),
    6 => 
    array (
      '@type' => 'browser',
      '@match' => 'Shiira',
      '@name' => 'Shiira',
      '.' => NULL,
    ),
    7 => 
    array (
      '@type' => 'browser',
      '@match' => 'Chimera',
      '@name' => 'Chimera',
      '.' => NULL,
    ),
    8 => 
    array (
      '@type' => 'browser',
      '@match' => 'Camino',
      '@name' => 'Camino',
      '.' => NULL,
    ),
    9 => 
    array (
      '@type' => 'browser',
      '@match' => 'Netscape',
      '@name' => 'Netscape',
      '.' => NULL,
    ),
    10 => 
    array (
      '@type' => 'browser',
      '@match' => 'OmniWeb',
      '@name' => 'OmniWeb',
      '.' => NULL,
    ),
    11 => 
    array (
      '@type' => 'browser',
      '@match' => 'Safari',
      '@name' => 'Safari',
      '.' => NULL,
    ),
    12 => 
    array (
      '@type' => 'browser',
      '@match' => 'Konqueror',
      '@name' => 'Konqueror',
      '.' => NULL,
    ),
    13 => 
    array (
      '@type' => 'browser',
      '@match' => 'icab',
      '@name' => 'iCab',
      '.' => NULL,
    ),
    14 => 
    array (
      '@type' => 'browser',
      '@match' => 'Lynx',
      '@name' => 'Lynx',
      '.' => NULL,
    ),
    15 => 
    array (
      '@type' => 'browser',
      '@match' => 'Links',
      '@name' => 'Links',
      '.' => NULL,
    ),
    16 => 
    array (
      '@type' => 'browser',
      '@match' => 'hotjava',
      '@name' => 'HotJava',
      '.' => NULL,
    ),
    17 => 
    array (
      '@type' => 'browser',
      '@match' => 'amaya',
      '@name' => 'Amaya',
      '.' => NULL,
    ),
    18 => 
    array (
      '@type' => 'browser',
      '@match' => 'IBrowse',
      '@name' => 'IBrowse',
      '.' => NULL,
    ),
    19 => 
    array (
      '@type' => 'mobile',
      '@match' => 'palm|elaine',
      '@name' => 'Palm',
      '.' => NULL,
    ),
    20 => 
    array (
      '@type' => 'mobile',
      '@match' => 'iphone|ipod',
      '@name' => 'iOS',
      '.' => NULL,
    ),
    21 => 
    array (
      '@type' => 'mobile',
      '@match' => 'blackberry',
      '@name' => 'Blackberry',
      '.' => NULL,
    ),
    22 => 
    array (
      '@type' => 'mobile',
      '@match' => 'symbian|series60',
      '@name' => 'SymbianOS',
      '.' => NULL,
    ),
    23 => 
    array (
      '@type' => 'mobile',
      '@match' => 'windows ce',
      '@name' => 'Windows CE',
      '.' => NULL,
    ),
    24 => 
    array (
      '@type' => 'mobile',
      '@match' => 'opera mini|operamini',
      '@name' => 'Opera Mini',
      '.' => NULL,
    ),
    25 => 
    array (
      '@type' => 'mobile',
      '@match' => 'mobile|wireless|j2me|phone',
      '@name' => 'Other Mobile',
      '.' => NULL,
    ),
  ),
  '/session/cookie/@name' => 'sessid',
  '/session/cookie/@life' => '0',
  '/session/cookie/@ipCheck' => '0',
  '/session/cookie/@uaCheck' => '1',
  '/session/cookie/@keyphase' => 'test',
  '/mvc/routing/@defaultController' => 'home',
  '/mvc/routing/@defaultAction' => 'index',
  '/mvc/routing/@notfoundController' => 'home',
  '/mvc/routing/@notfoundAction' => 'notfound',
  '/mvc/routing/@controllerUrlKey' => '0',
  '/mvc/routing/@actionUrlKey' => '1',
  '/repository/routing/@repositoryCheckKey' => '0',
  '/repository/routing/@repositoryCheckValue' => 'repository',
  '/repository/routing/@repositoryPackageKey' => '1',
  '/repository/packageList' => 
  array (
  ),
  '/razor/templates/@extension' => 'cshtml',
  '/razor/templates/@templatePath' => 'views',
  '/razor/templates/@compiledPath' => 'views/compiled',
  '/phptal/installation/@path' => 'include/3rdparty/PHPTAL-1.2.2',
  '/phptal/templates/@extension' => 'zpt',
  '/phptal/templates/@templatePath' => 'views',
  '/phptal/templates/@compiledPath' => 'views/compiled',
  '/smarty/installation/@path' => 'include/3rdparty/Smarty-3.1.7/libs',
  '/smarty/templates/@extension' => 'tpl',
  '/smarty/templates/@templatePath' => 'views',
  '/smarty/templates/@compiledPath' => 'views/compiled',
  '/raintpl/installation/@path' => 'include/3rdparty/raintpl-v.2.7.1.2-0/inc',
  '/raintpl/templates/@extension' => 'rain',
  '/raintpl/templates/@templatePath' => 'views',
  '/raintpl/templates/@compiledPath' => 'views/compiled',
  '/twig/installation/@path' => 'include/3rdparty/Twig-v1.6.0-0/lib/Twig',
  '/twig/templates/@extension' => 'twig',
  '/twig/templates/@templatePath' => 'views',
  '/twig/templates/@compiledPath' => 'views/compiled',
  '/markdown/templates/@extension' => 'md',
  '/markdown/templates/@templatePath' => 'views',
  '/markdown/templates/@compiledPath' => 'views/compiled',
  '/php/templates/@extension' => 'php',
  '/php/templates/@templatePath' => 'views',
)); Framework::load(); Extensions::load(); Framework::run(); ?>