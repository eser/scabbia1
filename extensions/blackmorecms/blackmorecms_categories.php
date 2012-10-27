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
						'callback' => 'blackmorecms_categories::new',
						'menutitle' => 'New Category',
						'action' => 'new'
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

			mvc::viewFile('{core}views/blackmorecms/categories/all.php', array(
				'categories' => &$tCategories
			));
		}

		/*
		public function category($uSlug = '', $uDeleteTag = '') {
			$user = shared::requireAuthentication($this);

			if($uDeleteTag == 'delete') {
				session::setFlash('notification', 'Category deleted.');
				$this->redirect('editor/categories');

				return;
			}

			$tTypes = array(
				'post' => 'Post',
				'page' => 'Page',
				'link' => 'Link',
				'file' => 'File'
			);

			if(http::$isPost || strlen($uSlug) == 0) {
				$this->set(
					'inputId',
					html::tag('input', array(
						'type' => 'hidden',
						'name' => 'categoryid',
						'value' => http::post('categoryid', '')
					))
				);

				$this->set(
					'inputType',
					html::tag(
						'select',
						array('name' => 'type', 'class' => 'select'),
						html::selectOptions($tTypes, http::post('type', null))
					)
				);

				$this->set(
					'inputName',
					html::tag('input', array(
						'type' => 'text',
						'name' => 'name',
						'value' => http::post('name', ''),
						'class' => 'text'
					))
				);

				$this->set(
					'inputSlug',
					html::tag('input', array(
						'type' => 'text',
						'name' => 'slug',
						'value' => http::post('slug', ''),
						'class' => 'text'
					))
				);
			}
			else {
				$this->load('blackmoreCategoriesModel');
				$tCategory = $this->categoryModel->getBySlug($uSlug);

				$this->set(
					'inputId',
					html::tag('input', array(
						'type' => 'hidden',
						'name' => 'categoryid',
						'value' => $tCategory['categoryid']
					))
				);

				$this->set(
					'inputType',
					html::tag(
						'select',
						array('name' => 'type', 'class' => 'select'),
						html::selectOptions($tTypes, $tCategory['type'])
					)
				);

				$this->set(
					'inputName',
					html::tag('input', array(
						'type' => 'text',
						'name' => 'name',
						'value' => $tCategory['name'],
						'class' => 'text'
					))
				);

				$this->set(
					'inputSlug',
					html::tag('input', array(
						'type' => 'text',
						'name' => 'slug',
						'value' => $tCategory['slug'],
						'class' => 'text'
					))
				);
			}

			$this->view();
		}

		public function category_post() {
			$user = shared::requireAuthentication($this);

			//validations
			validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
			// validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

			if(!validation::validate($_POST)) {
				$this->set('error', implode('<br />', validation::getErrorMessages(true)));
				return $this->category();
			}

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

			$this->load('blackmoreCategoriesModel');
			if(strlen($tCategoryId) == 0) {
				$this->categoryModel->insert($tInput);
			}
			else {
				$this->categoryModel->update($tCategoryId, $tInput);
			}

			session::setFlash('notification', 'Category sent.');
			$this->redirect('editor/categories');
		}
		*/
	}

?>