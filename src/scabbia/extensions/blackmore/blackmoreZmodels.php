<?php

	namespace Scabbia\Extensions\Blackmore;

	use Scabbia\Extensions\Auth\auth;
	use Scabbia\Extensions\Html\html;
	use Scabbia\Extensions\Http\request;
	use Scabbia\Extensions\Mvc\controllers;
	use Scabbia\Extensions\Mvc\mvc;
	use Scabbia\Extensions\Session\session;
	use Scabbia\Extensions\String\string;
	use Scabbia\Extensions\Validation\validation;
	use Scabbia\Extensions\Views\views;
	use Scabbia\Extensions\Zmodels\zmodel;
	use Scabbia\Extensions\Zmodels\zmodels;

	/**
	 * @ignore
	 */
	class blackmoreZmodels {
		/**
		 * @ignore
		 */
		public static function blackmoreRegisterModules($uParms) {
			$uParms['modules']['index']['submenus'] = true;

			$uParms['modules']['index']['actions'][] = array(
				'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::generateSql',
				'menutitle' => 'Generate Zmodel SQL',
				'action' => 'generateSql'
			);

			foreach(zmodels::$zmodels as $tKey => $tZmodel) {
				$uParms['modules'][$tKey] = array(
					'title' => $tZmodel['title'],
					'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::all',
					'submenus' => true,
					'actions' => array(
						array(
							'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::add',
							'menutitle' => 'Add ' . $tZmodel['singularTitle'],
							'action' => 'add'
						),
						array(
							'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::edit',
							'action' => 'edit'
						),
						array(
							'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::remove',
							'action' => 'remove'
						),
						array(
							'callback' => 'Scabbia\\Extensions\\Blackmore\\blackmoreZmodels::all',
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
		public static function getModel() {
			return controllers::load('blackmoreZmodelModel', null, config::get('/blackmore/database', null));
		}

		/**
		 * @ignore
		 */
		public static function generateSql() {
			auth::checkRedirect('admin');

			$tZmodel = new zmodel('categories');
			$tSql = $tZmodel->ddlCreateSql();

			views::viewFile('{vendor}views/blackmore/zmodels/sql.php', array(
			                                                              'sql' => $tSql
			                                                         ));
		}

		/**
		 * @ignore
		 */
		public static function all() {
			auth::checkRedirect('editor');

			$tModel = self::getModel();
			$tModule = & zmodels::$zmodels[blackmore::$module];

			$tRows = $tModel->getAll($tModule['name']);

			views::viewFile('{vendor}views/blackmore/zmodels/list.php', array(
			                                                               'module' => $tModule,
			                                                               'rows' => $tRows
			                                                          ));
		}

		/**
		 * @ignore
		 */
		public static function add($uAction) {
			auth::checkRedirect('editor');

			$tModule = & zmodels::$zmodels[blackmore::$module];
			$tViewbag = array(
				'module' => $tModule,
				'fields' => array()
			);

			if(request::$method == 'post') {
				// todo: validations
				validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
				// validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

				if(validation::validate($_POST)) {
					$tSlug = request::post('slug', '');
					if(strlen(rtrim($tSlug)) == 0) {
						$tSlug = request::post('name', '');
					}

					$tInput = array(
						'type' => request::post('type'),
						'name' => request::post('name'),
						'slug' => string::slug(string::removeAccent($tSlug))
					);

					$tModel = self::getModel();
					$tModel->insert($tInput);

					session::setFlash('notification', 'Record added.');
					mvc::redirect('blackmore/categories');

					return;
				}

				$tViewbag['error'] = implode('<br />', validation::getErrorMessages(true));
			}

			foreach($tModule['fieldList'] as $tField) {
				$tIsView = array_key_exists('view', $tField['methods']);
				$tIsEdit = array_key_exists('edit', $tField['methods']);

				if($tIsView || $tIsEdit) {
					switch($tField['type']) {
					case 'enum':
						$tTypes = array();
						foreach($tField['valueList'] as $tValue) {
							$tTypes[$tValue['name']] = $tValue['title'];
						}

						$tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag(
							'select',
							$tAttributes,
							html::selectOptions($tTypes, request::post($tField['name'], null))
						) . '</p>';
						break;

					default:
						$tAttributes = array(
							'type' => 'text',
							'name' => $tField['name'],
							'value' => request::post($tField['name'], ''),
							'class' => 'input input_' . $tField['type']
						);
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag('input', $tAttributes) . '</p>';
						break;
					}
				}

				$tViewbag['fields'][] = array(
					'data' => $tField,
					'html' => $tTag
				);
			}

			views::viewFile('{vendor}views/blackmore/zmodels/form.php', $tViewbag);
		}

		/**
		 * @ignore
		 */
		public static function edit($uAction, $uSlug) {
			auth::checkRedirect('editor');

			$tModule = & zmodels::$zmodels[blackmore::$module];
			$tViewbag = array(
				'module' => $tModule,
				'fields' => array()
			);

			if(request::$method == 'post') {
				// todo: validations
				validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
				// validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

				if(validation::validate($_POST)) {
					$tSlug = request::post('slug', '');
					if(strlen(rtrim($tSlug)) == 0) {
						$tSlug = request::post('name', '');
					}

					$tInput = array(
						'type' => request::post('type'),
						'name' => request::post('name'),
						'slug' => string::slug(string::removeAccent($tSlug))
					);

					$tModel = self::getModel();
					$tModel->update($uSlug, $tInput);

					session::setFlash('notification', 'Record modified.');
					mvc::redirect('blackmore/categories');

					return;
				}

				$tViewbag['error'] = implode('<br />', validation::getErrorMessages(true));

				foreach($tModule['fieldList'] as $tField) {
					$tIsView = array_key_exists('view', $tField['methods']);
					$tIsEdit = array_key_exists('edit', $tField['methods']);

					if($tIsView || $tIsEdit) {
						switch($tField['type']) {
						case 'enum':
							$tTypes = array();
							foreach($tField['valueList'] as $tValue) {
								$tTypes[$tValue['name']] = $tValue['title'];
							}

							$tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
							if(!$tIsEdit) {
								$tAttributes['readonly'] = 'readonly';
							}

							$tTag = '<p>' . _($tField['title']) . ': ' . html::tag(
								'select',
								$tAttributes,
								html::selectOptions($tTypes, request::post($tField['name'], null))
							) . '</p>';
							break;

						default:
							$tAttributes = array(
								'type' => 'text',
								'name' => $tField['name'],
								'value' => request::post($tField['name'], ''),
								'class' => 'input input_' . $tField['type']
							);
							if(!$tIsEdit) {
								$tAttributes['readonly'] = 'readonly';
							}

							$tTag = '<p>' . _($tField['title']) . ': ' . html::tag('input', $tAttributes) . '</p>';
							break;
						}
					}

					$tViewbag['fields'][] = array(
						'data' => $tField,
						'html' => $tTag
					);
				}

				views::viewFile('{vendor}views/blackmore/zmodels/form.php', $tViewbag);

				return;
			}

			$tModel = self::getModel();
			$tCategory = $tModel->getBySlug($tModule['name'], $uSlug);

			foreach($tModule['fieldList'] as $tField) {
				$tIsView = array_key_exists('view', $tField['methods']);
				$tIsEdit = array_key_exists('edit', $tField['methods']);

				if($tIsView || $tIsEdit) {
					switch($tField['type']) {
					case 'enum':
						$tTypes = array();
						foreach($tField['valueList'] as $tValue) {
							$tTypes[$tValue['name']] = $tValue['title'];
						}

						$tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag(
							'select',
							$tAttributes,
							html::selectOptions($tTypes, $tCategory[$tField['name']])
						) . '</p>';
						break;

					default:
						$tAttributes = array(
							'type' => 'text',
							'name' => $tField['name'],
							'value' => $tCategory[$tField['name']],
							'class' => 'input input_' . $tField['type']
						);
						if(!$tIsEdit) {
							$tAttributes['readonly'] = 'readonly';
						}

						$tTag = '<p>' . _($tField['title']) . ': ' . html::tag('input', $tAttributes) . '</p>';
						break;
					}
				}

				$tViewbag['fields'][] = array(
					'data' => $tField,
					'html' => $tTag
				);
			}

			views::viewFile('{vendor}views/blackmore/zmodels/form.php', $tViewbag);
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