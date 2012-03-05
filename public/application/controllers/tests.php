<?php

	class tests extends Controller {
		public function index() {
			contracts::check(1 == 1);

			$viewbag = array('deneme' => 'problem');
			$this->loadview('tests_temp.cshtml', $viewbag);
		}
		
		public function notfound() {
			echo '404 not found!';
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

		public function get() {
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