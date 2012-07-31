<?php

	class tests extends Controller {
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

			$this->loadview($tViewbag, 'home_login.cshtml');
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

			$this->loadview($tViewbag, 'home_index.cshtml');
		}
		
		public function notfound() {
			$this->error(string::format('404 not found! {@0}/{@1}', mvc::$controller, mvc::$action));
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
		
		public function contracts() {
			contracts::check(1 == 1);

			$viewbag = array('deneme' => 'problem');
			$this->loadview('tests_temp.cshtml', $viewbag);
		}
		
		public function phptal() {
			$viewbag = array('deneme' => 'testing phptal');

			$this->loadview('tests_temp.zpt', $viewbag);
		}

		public function smarty() {
			$viewbag = array('deneme' => 'testing smarty');

			$this->loadview('tests_temp.tpl', $viewbag);
		}

		public function raintpl() {
			$viewbag = array('deneme' => 'testing raintpl');

			$this->loadview('tests_temp.rain', $viewbag);
		}

		public function twig() {
			$viewbag = array('deneme' => 'testing twig');

			$this->loadview('tests_temp.twig', $viewbag);
		}

		public function version() {
			echo SCABBIA_VERSION;
		}
		
		public function extensions() {
			Extensions::dump();
		}

		public function config() {
			echo '<pre>';
			Config::dump();
		}

		public function getvars() {
			echo string::vardump($_GET);
		}
		
		public function browser() {
			echo '<pre>';
			echo http::getPlatform();
			echo '<br />';
			echo http::getCrawler();
		}
		
		public function languages() {
			echo string::vardump(http::getLanguages());
		}
		
		public function ucaser($uObject) {
			$uObject['content'] = strtoupper($uObject['content']);
		}
		
		public function output() {
			Events::register('output', Events::Callback('ucaser', $this));
			output::begin('ucase');

			echo 'output sample<br />';

			output::end();
		}

		public function mvc() {
			echo MVC::getController();
			echo '<br />';
			echo MVC::getAction();
			echo '<br />';
			echo $_GET[2];
		}
		
		public function msec() {
			echo microtime(true) - QTIME_INIT;
		}
		
		public function database() {
			echo string::vardump(database::get('dbconn', 'dbs')->query('testtable'));
			echo string::vardump(database::get('dbconn'));
		}
		
		public function accounts() {
			$this->loadmodel('testModel');

			echo $this->testModel->delete();
			echo '<br />';
			echo $this->testModel->insert();
			echo '<br />';
			// $this->testModel->update();
			// echo '<br />';

			echo string::vardump($this->testModel->get());
			echo '<br />';
			echo string::vardump($this->testModel->getRow());
			echo '<br />';
			echo string::vardump($this->testModel->getScalar());
			echo '<br />';
		}
		
		public function unsubscribe() {
			$tEmail = $this->httpGet(2, '');
			if(empty($tEmail)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
			$tResult = $this->users->unsubscribe($tEmail);

			$tViewbag = array(
				'title' => 'Done'
			);

			if($tResult) {
				$tViewbag['message'] = 'Your e-mail is unsubscribed from mailing list. You won\'t get any notification mails from now on.';
			}
			else {
				$tViewbag['message'] = 'Your e-mail has already been unsubscribed from mailing list.';
			}

			$this->loadview('shared_error.cshtml', $tViewbag);
		}

		public function image() {
			$tCampaign = $this->httpGet(2, '');
			if(empty($tCampaign)) {
				return $this->error('campaign is empty');
			}

			$tUserId = $this->httpGet(3, '');
			if(empty($tUserId)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
			$this->users->logCampaignView($tUserId, $tCampaign, 2); // 2=image

			http::sendFile(QPATH_CORE . 'res/eposta.png');
		}

		public function content() {
			$tCampaign = $this->httpGet(2, '');
			if(empty($tCampaign)) {
				return $this->error('campaign is empty');
			}

			$tUserId = $this->httpGet(3, '');
			if(empty($tUserId)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
			
			$tUser = $this->users->getSingle($tUserId);
			if(is_null($tUser)) {
				return $this->error('user is not exists');
			}

			$this->users->logCampaignView($tUserId, $tCampaign, 1); // 1=content

			$tViewbag = array(
				'title' => $tUser['LongName'],
				'longname' => $tUser['LongName'],
				'email' => $tUser['EMail'],
				'facebookid' => $tUser['facebookid'],
				'imgpath' => $tUser['ImgPath'],
				'gender' => $tUser['Gender'],
				'locale' => $tUser['Locale'],
				'recdate' => $tUser['RecDate'],
				'campaign' => $tCampaign,
				'userid' => $tUserId,
				'image' => $_SERVER['PHP_SELF'] . '?users/image/' . $tCampaign . '/' . $tUserId
			);

			$this->loadview('users_content.cshtml', $tViewbag);
		}

/*	
	// GZIP OUTPUT START
	$tGzipEnabled = $output->gzip->httpHeader();
	$output->begin('gzip');

	// IPBAN
	$http->ipban->add(new IpBanItem('10.0.0.2', IpBanItem::TYPE_DENY));
	$http->ipban->add(new IpBanItem('192.168.0.3', IpBanItem::TYPE_DENY));

	echo '<br />IPBan List:<br />--------------<br />';
	foreach($http->ipban->toArray() as $tRow) {
		echo $tRow->pattern, ': ', ($tRow->type == IpBanItem::TYPE_ALLOW ? 'allow' : 'deny'), '<br />';
	}

	// STORAGE DB
	// $tProps = new Collection();
	// $tProps->addKey('pdoString', 'mysql:host=localhost;dbname=kanka');
	// $tProps->addKey('host', 'localhost');
	// $tProps->addKey('username', 'root');
	// $tProps->addKey('password', '');
	// $storage->addStorage('dbconn', 'mysql', $tProps);
	// $storage['dbconn']->cachePath = 'cache/';

	echo '<br />Storage List:<br />--------------<br />';
	foreach($storage->toArray() as $tRow) {
		echo $tRow->id, ': ', $tRow->type, '<br />';
	}

	// DATASETS AND CACHE
	$queries = new Collection(array('SHOW DATABASES'));
	$parameters = new Collection();
	$storage->addDataset('dbconn', 'databases', $queries, $parameters, 0, 3, false);

	$tQuery = $storage['dbconn']->datasets['databases']->query();
	echo '<br />Database List:<br />--------------<br />';
	while($tRow = $tQuery->next()) {
		echo $tRow['Database'] . '<br />';
	}

	// HTTP REQUEST
	echo '<br />HTTP Request:<br />--------------<br />';
	echo $string->vardump($_REQUEST);

	// USERS QUERY
	echo '<br />Users Query:<br />--------------<br />';
	echo $string->vardump($storage['dbconn']->datasets['dbs']->query('users'));

	// OUTPUT
	echo '<br />Output:<br />--------------<br />';
	$output->begin('ucwords');
	echo 'son ruya';
	$output->end(false);
	echo 'UCWORDS: ', $output->pop(), '<br />';

	// STATICS
	echo '<br />Statics:<br />--------------<br />';
	echo '<br />DatabaseCached: ', $storage['dbconn']->stats['cache'];
	echo '<br />Framework Compiled: ', (int)COMPILED;
	echo '<br />Gzip Enabled: ', (int)$tGzipEnabled;
	echo '<br />Loadtime: ', $s;
	echo '<br /><a href="./">Plain</a> | <a href="./?compiled">Compiled</a> | <a href="./build.php">BUILD</a>';

	// GZIP OUTPUT END
	$output->end();
*/
	}

?>