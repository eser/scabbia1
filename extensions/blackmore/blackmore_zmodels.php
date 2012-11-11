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
			$uParms['modules']['index']['submenus'] = true;

			$uParms['modules']['index']['actions'][] = array(
				'callback' => 'blackmore_zmodels::generateSql',
				'menutitle' => 'Generate Zmodel SQL',
				'action' => 'generateSql'
			);

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
		public static function &getModel() {
			return mvc::load('blackmoreZmodelModel', null, config::get('/blackmore/database', null));
		}

		/**
		 * @ignore
		 */
		public static function generateSql() {
			auth::checkRedirect('admin');

			$tSql = zmodels::generateCreateSql('categories');

			mvc::viewFile('{core}views/blackmore/zmodels/sql.php', array(
				'sql' => &$tSql
			));
		}
		
		/**
		 * @ignore
		 */
		public static function all() {
			auth::checkRedirect('editor');

			$tModel = self::getModel();
			$tModule = &zmodels::$zmodels[blackmore::$module];
			
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

			$tModule = &zmodels::$zmodels[blackmore::$module];
			$tViewbag = array(
				'module' => &$tModule,
				'fields' => array()
			);

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

					$tModel = self::getModel();
					$tModel->insert($tInput);

					session::setFlash('notification', 'Category added.');
					mvc::redirect('blackmore/categories');

					return;
				}

				$tViewbag['error'] = implode('<br />', validation::getErrorMessages(true));
			}

			foreach($tModule['fieldList'] as &$tField) {
				$tIsView = array_key_exists('view', $tField['methods']);
				$tIsEdit = array_key_exists('edit', $tField['methods']);

				if($tIsView || $tIsEdit) {
					switch($tField['type']) {
					case 'enum':
						$tTypes = array();
						foreach($tField['valueList'] as &$tValue) {
							$tTypes[$tValue['name']] = $tValue['title'];
						}

						$tAttributes = array('name' => $tField['name'], 'class' => 'select');
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag(
							'select',
							$tAttributes,
							html::selectOptions($tTypes, http::post($tField['name'], null))
						) . '</p>';
						break;

					default:
						$tAttributes = array(
							'type' => 'text',
							'name' => $tField['name'],
							'value' => http::post($tField['name'], ''),
							'class' => 'text'
						);
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag('input', $tAttributes) . '</p>';
						break;
					}
				}

				$tViewbag['fields'][] = array(
					'data' => &$tField,
					'html' => $tTag
				);
			}

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

					$tModel = self::getModel();
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

			$tModel = self::getModel();
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