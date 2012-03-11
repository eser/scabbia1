<?php

	class html {
		public static function extension_info() {
			return array(
				'name' => 'html',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string')
			);
		}

		public static function selectBox($uArray = array(), $uDefault = null) {
			$tOutput = '';

			foreach($uArray as $tKey => &$tVal) {
				$tOutput .= '<option value="' . string::escapeDQuotes($tKey) . '"';
				if($uDefault == $tKey) { $tOutput .= ' selected="selected"'; }
				$tOutput .= '>' . $tVal . '</option>';
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
			// 	$uOptions['link'] = '<a href="{baseurl}?home/index/{page}" class="pagerlink">{pagetext}</a>';
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
						$tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;&lt;'));
					}
					$tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;'));
				}
				else {
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => '1', 'pagetext' => '&lt;&lt;'));
					}
					$tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tCurrent - 1, 'pagetext' => '&lt;'));
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
					$tResult .= string::format($uOptions['activelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $i, 'pagetext' => $i));
				}
				else {
					$tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $i, 'pagetext' => $i));
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
					$tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;'));
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['passivelink'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
					}
				}
				else {
					$tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tCurrent + 1, 'pagetext' => '&gt;'));
					if($uOptions['firstlast']) {
						$tResult .= string::format($uOptions['link'], array('baseurl' => $_SERVER['PHP_SELF'], 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
					}
				}
			}

			return $tResult;
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

			foreach($uOptions['data'] as &$tRow) {
				if(isset($uOptions['rowFunc'])) {
					$tResult .= call_user_func($uOptions['rowFunc'], $tRow);
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

?>