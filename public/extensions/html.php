<?php

if(extensions::isSelected('html')) {
	/**
	* Html Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*
	* @todo form open
	* @todo form fields
	* @todo add callJavascriptFromRepository
	* @todo add callStylesheetFromRepository
	*/
	class html {
		public static $attributeOrder = array(
			'action', 'method', 'type', 'id', 'name', 'value',
			'href', 'src', 'width', 'height', 'cols', 'rows',
			'size', 'maxlength', 'rel', 'media', 'accept-charset',
			'accept', 'tabindex', 'accesskey', 'alt', 'title', 'class',
			'style', 'selected', 'checked', 'readonly', 'disabled'
		);

		public static function extension_info() {
			return array(
				'name' => 'html',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'i8n', 'arrays')
			);
		}

		public static function tag($uName, $uAttributes) {
			$tReturn = '<' . $uName;
			if(is_array($uAttributes)) {
				$tReturn .= ' ' . self::attributes($uAttributes);
			}
			$tReturn .= ' />';

			return $tReturn;
		}

		public static function attributes($uAttributes) {
			$tAttributes = arrays::sortByPriority($uAttributes, self::$attributeOrder);

			$tReturn = array();
			foreach($tAttributes as $tKey => $tValue) {
				if(is_null($tValue)) {
					$tReturn[] = $tKey . '="' . $tKey . '"';
					continue;
				}

				$tReturn[] = $tKey . '="' . string::htmlEscape($tValue) . '"';
			}

			return implode(' ', $tReturn);
		}

		public static function selectOptions($uArray = array(), $uDefault = null) {
			$tOutput = '';

			foreach($uArray as $tKey => &$tVal) {
				$tOutput .= '<option value="' . string::dquote($tKey) . '"';

				if($uDefault == $tKey) {
					$tOutput .= ' selected="selected"';
				}

				$tOutput .= '>' . $tVal . '</option>';
			}

			return $tOutput;
		}

		public static function textBox($uName, $uValue) {
			$tOutput = '<input type="text" name="' . string::dquote($uValue) . '" value="' . string::dquote($uValue) . '" />';

			return $tOutput;
		}

		public static function checkBox($uName, $uValue, $uCurrentValue = null, $uText = null, $uId = null) {
			if(is_null($uId)) {
				$uId = $uName;
			}

			$tOutput = '<input type="checkbox" id="' . string::dquote($uId) . '" name="' . string::dquote($uName) . '" value="' . string::dquote($uValue) . '"';

			if($uCurrentValue == $uValue) {
				$tOutput .= ' checked="checked"';
			}

			$tOutput .= ' />';

			if(!is_null($uText)) {
				$tOutput .= '<label for="' . string::dquote($uId) . '">' . $uText . '</label>';
			}

			return $tOutput;
		}

		public static function pager($uOptions) {
			$tPages = ceil($uOptions['total'] / $uOptions['pagesize']);

			if(!isset($uOptions['divider'])) {
				$uOptions['divider'] = '';
			}

			if(!isset($uOptions['dots'])) {
				$uOptions['dots'] = ' ... ';
			}

			// if(!isset($uOptions['link'])) {
			// 	$uOptions['link'] = '<a href="{root}?home/index/{page}" class="pagerlink">{pagetext}</a>';
			// }

			if(!isset($uOptions['passivelink'])) {
				$uOptions['passivelink'] = $uOptions['link'];
			}

			if(!isset($uOptions['activelink'])) {
				$uOptions['activelink'] = $uOptions['passivelink'];
			}

			if(!isset($uOptions['firstlast'])) {
				$uOptions['firstlast'] = true;
			}

			if(isset($uOptions['current'])) {
				$tCurrent = (int)$uOptions['current'];
				if($tCurrent <= 0) { // || $tCurrent > $tPages
					$tCurrent = 1;
				}
			}
			else {
				$tCurrent = 1;
			}

			if(isset($uOptions['numlinks'])) {
				$tNumLinks = (int)$uOptions['numlinks'];
			}
			else {
				$tNumLinks = 10;
			}

			$tStart = $tCurrent - floor($tNumLinks * 0.5);
			$tEnd = $tCurrent + floor($tNumLinks * 0.5) - 1;

			if($tStart < 1) {
				$tEnd += abs($tStart) + 1;
				$tStart = 1;
			}

			if($tEnd > $tPages) {
				if($tStart - $tEnd - $tPages > 0) {
					$tStart -= $tEnd - $tPages;
				}
				$tEnd = $tPages;
			}

			$tResult = '';

			if($tPages > 1) {
				if($tCurrent <= 1) {
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['passivelink'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => '1', 'pagetext' => '&lt;&lt;'));
					}
					$tResult .= string::format($uOptions['passivelink'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => '1', 'pagetext' => '&lt;'));
				}
				else {
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['link'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => '1', 'pagetext' => '&lt;&lt;'));
					}
					$tResult .= string::format($uOptions['link'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $tCurrent - 1, 'pagetext' => '&lt;'));
				}

				if($tStart > 1) {
					$tResult .= $uOptions['dots'];
				}
				else {
					$tResult .= $uOptions['divider'];
				}
			}

			for($i = $tStart;$i <= $tEnd;$i++) {
				if($tCurrent == $i) {
					$tResult .= string::format($uOptions['activelink'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $i, 'pagetext' => $i));
				}
				else {
					$tResult .= string::format($uOptions['link'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $i, 'pagetext' => $i));
				}

				if($i != $tEnd) {
					$tResult .= $uOptions['divider'];
				}
			}

			if($tPages > 1) {
				if($tEnd < $tPages) {
					$tResult .= $uOptions['dots'];
				}
				else {
					$tResult .= $uOptions['divider'];
				}

				if($tCurrent >= $tPages) {
					$tResult .= string::format($uOptions['passivelink'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $tPages, 'pagetext' => '&gt;'));
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['passivelink'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
					}
				}
				else {
					$tResult .= string::format($uOptions['link'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $tCurrent + 1, 'pagetext' => '&gt;'));
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['link'], array('root' => framework::$siteroot, 'lang' => i8n::$languageKey, 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
					}
				}
			}

			return $tResult;
		}

	    public static function doctype($type = 'html5') {
			switch($uType) {
			case 'html5':
			case 'xhtml5':
				return '<!DOCTYPE html>';
				break;
			case 'xhtml11':
			case 'xhtml1.1':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				break;
			case 'xhtml1':
			case 'xhtml1-strict':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				break;
			case 'xhtml1-trans':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				break;
			case 'xhtml1-frame':
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				break;
			case 'html4-strict':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				break;
			case 'html4':
			case 'html4-trans':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				break;
			case 'html4-frame':
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
				break;
			}

			return false;
	    }

		public static function table($uOptions) {
			if(!isset($uOptions['table'])) {
				$uOptions['table'] = '<table>';
			}

			if(!isset($uOptions['cell'])) {
				$uOptions['cell'] = '<td>{value}</td>';
			}

			if(!isset($uOptions['header'])) {
				$uOptions['header'] = '<th>{value}</th>';
			}

			$tResult = string::format($uOptions['table'], array());

			if(isset($uOptions['headers'])) {
				$tResult .= '<tr>';
				foreach($uOptions['headers'] as &$tColumn) {
					$tResult .= string::format($uOptions['header'], array('value' => $tColumn));
				}
				$tResult .= '</tr>';
			}

			$tCount = 0;
			foreach($uOptions['data'] as &$tRow) {
				if(isset($uOptions['rowFunc'])) {
					$tResult .= call_user_func($uOptions['rowFunc'], $tRow, $tCount++);
				}
				else if(isset($uOptions['row'])) {
					$tResult .= string::format($uOptions['row'], $tRow);
				}
				else {
					$tResult .= '<tr>';

					foreach($tRow as &$tColumn) {
						$tResult .= string::format($uOptions['cell'], array('value' => $tColumn));
					}

					$tResult .= '</tr>';
				}
			}

			$tResult .= '</table>';

			return $tResult;
		}
	}
}

?>