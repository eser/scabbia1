<?php

	/**
	* Blackmore CMS Extension: Categories Section
	*
	* @package Scabbia
	* @subpackage blackmorecms_categories
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, auth, validation, http
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmorecms_categories {
		/**
		* @ignore
		*/
		public static function blackmore_registerModules($uParms) {
			$uParms['modules']['categories'] = array(
				'title' => 'Categories',
				'callback' => 'blackmorecms_categories::all',
				'submenus' => true,
				'actions' => array(
					array(
						'callback' => 'blackmorecms_categories::add',
						'menutitle' => 'Add Category',
						'action' => 'add'
					),
					array(
						'callback' => 'blackmorecms_categories::edit',
						'action' => 'edit'
					),
					array(
						'callback' => 'blackmorecms_categories::remove',
						'action' => 'remove'
					),
					array(
						'callback' => 'blackmorecms_categories::all',
						'menutitle' => 'All Categories',
						'action' => 'all'
					)
				)
			);
		}
		
		/**
		* @ignore
		*/
		public static function all() {
			auth::checkRedirect('editor');

			$tModel = new blackmoreCmsCategoriesModel();
			$tCategories = $tModel->getAll();

			mvc::viewFile('{core}views/blackmorecms/categories/list.php', array(
				'categories' => &$tCategories
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

			if(http::$isPost) {
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

					$tModel = new blackmoreCmsCategoriesModel();
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

			mvc::viewFile('{core}views/blackmorecms/categories/form.php', $tViewbag);
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

			if(http::$isPost) {
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

					$tModel = new blackmoreCmsCategoriesModel();
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
				
				mvc::viewFile('{core}views/blackmorecms/categories/form.php', $tViewbag);
				return;
			}

			$tModel = new blackmoreCmsCategoriesModel();
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

			mvc::viewFile('{core}views/blackmorecms/categories/form.php', $tViewbag);
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