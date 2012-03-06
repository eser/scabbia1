<?php

	class home extends Controller {
		private $limit = 200;

		public function __construct() {
			session_start();
		}

		public function login() {
			// to logout
			unset($_SESSION['logged']);

			$tViewbag = array(
				'title' => 'Login'
			);

			$this->loadview('home_login.cshtml', $tViewbag);
		}

		public function login_post() {
			$this->loadmodel('accountsModel', 'accounts');

			if(!$this->accounts->checkLogin($_POST['name'], $_POST['password'])) {
				return $this->error('username/password error');
			}

			$_SESSION['logged'] = true;
			http::sendRedirect($_SERVER['PHP_SELF'] . '?home/index');
		}

		public function index() {
			if(!isset($_SESSION['logged'])) {
				return $this->login();
			}

			$this->loadmodel('usersModel', 'users');

			$tCurrentPage = $this->httpGet(2, 1, 'int');
			if($tCurrentPage <= 0) {
				$tCurrentPage = 1;
			}

			$tTotal = $this->users->count();
			$tDataSet = $this->users->get(($tCurrentPage - 1) * $this->limit, $this->limit);

			$tViewbag = array(
				'title' => 'List of Accounts',
				'link_back' => string::format('{num:0} records listed in {num:1} pages', $tTotal, ceil($tTotal / $this->limit))
			);

			// generate pagination
			$tViewbag['pagination'] = html::pager(array(
				'total' => $tTotal,
				'pagesize' => $this->limit,
				'current' => $tCurrentPage,
				'numlinks' => 20,
				'link' => '<a href="{baseurl}?home/index/{page}" class="pagerlink">{pagetext}</a>',
				'activelink' => '<span class="pagerlink_active">{pagetext}</span>',
				'passivelink' => '<span class="pagerlink_passive">{pagetext}</span>',
				'firstlast' => true
			));

			// generate table data
			$tViewbag['table'] = html::table(array(
				'data' => $tDataSet,
				'headers' => array(
					'Profile',
					'E-Mail',
					'Name',
					'Locale',
					'Gender',
					'Registered'
				),
				// 'row' => '<tr><td><a href="https://www.facebook.com/profile.php?id={facebookid}"><img src="{ImgPath}" border="0" alt="Facebook Profile" /></a></td><td>{EMail}</td><td>{LongName}</td><td>{Locale}</td><td></td><td></td></tr>'
				'rowFunc' => Events::Callback('home::tableRow')
			));

			$this->loadview('home_index.cshtml', $tViewbag);
		}
		
		public function error($uMsg) {
			$tViewbag = array(
				'title' => 'Error',
				'message' => $uMsg
			);

			$this->loadview('shared_error.cshtml', $tViewbag);
		}
		
		public function notfound() {
			return $this->error('404 not found!');
		}

		public static function tableRow($uRow) {
			switch($uRow['Locale']) {
			case 'tr_TR':
				$tLocale = 'Turkey';
				break;
			case 'de_DE':
				$tLocale = 'Germany';
				break;
			case 'ru_RU':
				$tLocale = 'Russia';
				break;
			case 'nl_NL':
				$tLocale = 'Netherlands';
				break;
			case 'en_US':
				$tLocale = 'United States';
				break;
			case 'en_GB':
				$tLocale = 'United Kingdom';
				break;
			case 'fr_FR':
				$tLocale = 'France';
				break;
			default:
				$tLocale = &$uRow['Locale'];
				break;
			}

			switch($uRow['Gender']) {
			case '1':
				$tGender = 'Female';
				break;
			case '2':
				$tGender = 'Male';
				break;
			case '0':
			default:
				$tGender = '-';
				break;
			}

			$tResult = '<tr>';
			if(!empty($uRow['ImgPath'])) {
				$tResult .= '<td><a href="https://www.facebook.com/profile.php?id=' . $uRow['facebookid'] . '"><img src="' . $uRow['ImgPath'] . '" border="0" alt="Facebook Profile" /></a></td>';
			}
			else {
				$tResult .= '<td></td>';
			}

			$tResult .= '<td><a href="mailto:' . $uRow['EMail'] . '">' . $uRow['EMail'] . '</a></td>';
			$tResult .= '<td><a href="https://www.facebook.com/profile.php?id=' . $uRow['facebookid'] . '">' . $uRow['LongName'] . '</a></td>';
			$tResult .= '<td>' . $tLocale . '</td>';
			$tResult .= '<td>' . $tGender . '</td>';

			if(!empty($uRow['RecDate'])) {
				$tResult .= '<td>' . date('d-m-Y H:i', $uRow['RecDate']) . '</td>';
			}
			else {
				$tResult .= '<td>-</td>';
			}
			$tResult .= '</tr>';
			
			return $tResult;
		}
	}

?>