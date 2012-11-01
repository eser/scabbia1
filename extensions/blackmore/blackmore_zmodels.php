<?php

	/**
	* Blackmore Extension: ZModels Section
	*
	* @package Scabbia
	* @subpackage blackmore_zmodels
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, validation, http, auth, zmodels
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore_zmodels {
		/**
		* @ignore
		*/
		public static function blackmore_registerModules($uParms) {
			foreach(zmodels::$zmodels as $tKey => &$tZmodel) {
				$uParms['modules'][$tKey] = array(
					'title' => $tZmodel['title'],
					'callback' => 'blackmore_zmodels::all',
					'submenus' => true,
					'actions' => array(
						array(
							'callback' => 'blackmore_zmodels::add',
							'menutitle' => 'Add ' . $tZmodel['singularTitle'],
							'action' => 'add'
						),
						array(
							'callback' => 'blackmore_zmodels::edit',
							'action' => 'edit'
						),
						array(
							'callback' => 'blackmore_zmodels::remove',
							'action' => 'remove'
						),
						array(
							'callback' => 'blackmore_zmodels::all',
							'menutitle' => 'All ' . $tZmodel['title'],
							'action' => 'all'
						)
					)
				);
			}
		}
		
		/**
		* @ignore
		*/
		public static function all() {
			auth::checkRedirect('editor');

			$tModel = new blackmoreZmodelModel();
			$tModule = zmodels::$zmodels[blackmore::$module];
			
			$tRows = $tModel->getAll($tModule['name']);

			mvc::viewFile('{core}views/blackmore/zmodels/list.php', array(
				'module' => &$tModule,
				'rows' => &$tRows
			));
		}

		/**
		* @ignore
		*/
		public static function add($uAction) {
			auth::checkRedirect('editor');

			$tTypes = array(
				'post' => 'Post',
				'page' => 'Page',
				'link' => 'Link',
				'file' => 'File'
			);

			$tViewbag = array();

			if(http::$method == 'post') {
				// validations
				validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
				// validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

				if(validation::validate($_POST)) {
					$tSlug = http::post('slug', '');
					if(strlen(rtrim($tSlug)) == 0) {
						$tSlug = http::post('name', '');
					}

					$tDate = time();
					$tInput = array(
						'type' => http::post('type'),
						'name' => http::post('name'),
						'slug' => string::slug(string::removeAccent($tSlug))
					);

					$tModel = new blackmoreZmodelModel();
					$tModel->insert($tInput);

					session::setFlash('notification', 'Category added.');
					mvc::redirect('blackmore/categories');

					return;
				}

				$tViewbag['error'] = implode('<br />', validation::getErrorMessages(true));
			}
			
			$tViewbag['inputId'] = '';

			$tViewbag['inputType'] = html::tag(
										'select',
										array('name' => 'type', 'class' => 'select'),
										html::selectOptions($tTypes, http::post('type', null))
									);

			$tViewbag['inputName'] = html::tag('input', array(
										'type' => 'text',
										'name' => 'name',
										'value' => http::post('name', ''),
										'class' => 'text'
									));

			$tViewbag['inputSlug'] = html::tag('input', array(
										'type' => 'text',
										'name' => 'slug',
										'value' => http::post('slug', ''),
										'class' => 'text'
									));

			mvc::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);
		}

		/**
		* @ignore
		*/
		public static function edit($uAction, $uSlug) {
			auth::checkRedirect('editor');

			$tTypes = array(
				'post' => 'Post',
				'page' => 'Page',
				'link' => 'Link',
				'file' => 'File'
			);

			if(http::$method == 'post') {
				// validations
				validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
				// validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

				if(validation::validate($_POST)) {
					$tCategoryId = http::post('categoryid', '');

					$tSlug = http::post('slug', '');
					if(strlen(rtrim($tSlug)) == 0) {
						$tSlug = http::post('name', '');
					}

					$tDate = time();
					$tInput = array(
						'type' => http::post('type'),
						'name' => http::post('name'),
						'slug' => string::slug(string::removeAccent($tSlug))
					);

					$tModel = new blackmoreZmodelModel();
					$tModel->update($tCategoryId, $tInput);

					session::setFlash('notification', 'Category modified.');
					mvc::redirect('blackmore/categories');

					return;
				}

				$tViewbag['error'] = implode('<br />', validation::getErrorMessages(true));

				$tViewbag['inputId'] = html::tag('input', array(
											'type' => 'hidden',
											'name' => 'categoryid',
											'value' => http::post('categoryid', '')
										));

				$tViewbag['inputType'] = html::tag(
											'select',
											array('name' => 'type', 'class' => 'select'),
											html::selectOptions($tTypes, http::post('type', null))
										);

				$tViewbag['inputName'] = html::tag('input', array(
											'type' => 'text',
											'name' => 'name',
											'value' => http::post('name', ''),
											'class' => 'text'
										));

				$tViewbag['inputSlug'] = html::tag('input', array(
											'type' => 'text',
											'name' => 'slug',
											'value' => http::post('slug', ''),
											'class' => 'text'
										));
				
				mvc::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);
				return;
			}

			$tModel = new blackmoreZmodelModel();
			$tCategory = $tModel->getBySlug($uSlug);

			$tViewbag['inputId'] = html::tag('input', array(
										'type' => 'hidden',
										'name' => 'categoryid',
										'value' => $tCategory['categoryid']
									));

			$tViewbag['inputType'] = html::tag(
										'select',
										array('name' => 'type', 'class' => 'select'),
										html::selectOptions($tTypes, $tCategory['type'])
									);

			$tViewbag['inputName'] = html::tag('input', array(
										'type' => 'text',
										'name' => 'name',
										'value' => $tCategory['name'],
										'class' => 'text'
									));

			$tViewbag['inputSlug'] = html::tag('input', array(
										'type' => 'text',
										'name' => 'slug',
										'value' => $tCategory['slug'],
										'class' => 'text'
									));

			mvc::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);
		}

		/**
		* @ignore
		*/
		public static function remove($uAction, $uSlug) {
			auth::checkRedirect('editor');

			session::setFlash('notification', 'Category removed.');
			mvc::redirect('blackmore/categories');
		}
	}

?>