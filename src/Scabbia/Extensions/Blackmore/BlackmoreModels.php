<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Blackmore\Blackmore;
use Scabbia\Extensions\Html\Html;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Mvc\Mvc;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\String\String;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Models\AutoModel;
use Scabbia\Extensions\Models\AutoModels;
use Scabbia\Config;

/**
 * Blackmore Extension: Models Section
 *
 * @package Scabbia
 * @subpackage Blackmore
 * @version 1.1.0
 */
class BlackmoreModels
{
    /**
     * @ignore
     */
    public static function blackmoreRegisterModules($uParms)
    {
        $uParms['modules'][Blackmore::DEFAULT_MODULE_INDEX]['actions']['generateSql'] = array(
            'icon' => 'list-alt',
            'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreModels::generateSql',
            'menutitle' => 'Generate AutoModel SQL'
        );

        AutoModels::load();
        foreach (AutoModels::$autoModels as $tKey => $tAutoModel) {
            $uParms['modules'][$tKey] = array(
                'title' => $tAutoModel['title'],
                'actions' => array(
                    'add' => array(
                        'icon' => 'plus',
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreModels::add',
                        'menutitle' => 'Add ' . $tAutoModel['singularTitle']
                    ),
                    'edit' => array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreModels::edit'
                    ),
                    'remove' => array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreModels::remove'
                    ),
                    'all' => array(
                        'icon' => 'list-alt',
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreModels::all',
                        'menutitle' => 'All ' . $tAutoModel['title']
                    )
                )
            );
        }
    }

    /**
     * @ignore
     */
    public static function getModel()
    {
        return Controllers::load('Scabbia\\Extensions\\Blackmore\\BlackmoreAutoModel', null, Config::get('blackmore/database', null));
    }

    /**
     * @ignore
     */
    public static function generateSql()
    {
        Auth::checkRedirect('admin');

        $tAutoModel = new AutoModel('categories');
        $tSql = $tAutoModel->ddlCreateSql();

        Views::viewFile(
            '{core}views/blackmore/models/sql.php',
            array(
                'sql' => $tSql
            )
        );
    }

    /**
     * @ignore
     */
    public static function all()
    {
        Auth::checkRedirect('editor');

        $tModel = self::getModel();
        $tModule = AutoModels::get(Blackmore::$module);

        $tRows = $tModel->getAll($tModule['name']);

        Views::viewFile(
            '{core}views/blackmore/models/list.php',
            array(
                'module' => $tModule,
                'rows' => $tRows
            )
        );
    }

    /**
     * @ignore
     */
    public static function add($uAction)
    {
        Auth::checkRedirect('editor');

        $tModule = AutoModels::get(Blackmore::$module);
        $tViewbag = array(
            'module' => $tModule,
            'fields' => array()
        );

        if (Request::$method == 'post') {
            // todo: validations
            Validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
            // Validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

            if (Validation::validate($_POST)) {
                $tSlug = Request::post('slug', '');
                if (strlen(rtrim($tSlug)) == 0) {
                    $tSlug = Request::post('name', '');
                }

                $tInput = array(
                    'type' => Request::post('type'),
                    'name' => Request::post('name'),
                    'slug' => String::slug(String::removeAccent($tSlug))
                );

                $tModel = self::getModel();
                $tModel->insert($tInput);

                Session::setFlash('notification', 'Record added.');
                Http::redirect('blackmore/categories');

                return;
            }

            $tViewbag['error'] = implode('<br />', Validation::getErrorMessages(true));
        }

        foreach ($tModule['fieldList'] as $tField) {
            $tIsView = array_key_exists('view', $tField['methods']);
            $tIsEdit = array_key_exists('edit', $tField['methods']);

            if ($tIsView || $tIsEdit) {
                switch ($tField['type']) {
                    case 'enum':
                        $tTypes = array();
                        foreach ($tField['valueList'] as $tValue) {
                            $tTypes[$tValue['name']] = $tValue['title'];
                        }

                        $tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                            'select',
                            $tAttributes,
                            Html::selectOptions($tTypes, Request::post($tField['name'], null))
                        ) . '</p>';
                        break;

                    default:
                        $tAttributes = array(
                            'type' => 'text',
                            'name' => $tField['name'],
                            'value' => Request::post($tField['name'], ''),
                            'class' => 'input input_' . $tField['type']
                        );
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
                        break;
                }
            }

            $tViewbag['fields'][] = array(
                'data' => $tField,
                'html' => $tTag
            );
        }

        Views::viewFile('{core}views/blackmore/models/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function edit($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        $tModule = AutoModels::get(Blackmore::$module);
        $tViewbag = array(
            'module' => $tModule,
            'fields' => array()
        );

        if (Request::$method == 'post') {
            // todo: validations
            Validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
            // Validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

            if (Validation::validate($_POST)) {
                $tSlug = Request::post('slug', '');
                if (strlen(rtrim($tSlug)) == 0) {
                    $tSlug = Request::post('name', '');
                }

                $tInput = array(
                    'type' => Request::post('type'),
                    'name' => Request::post('name'),
                    'slug' => String::slug(String::removeAccent($tSlug))
                );

                $tModel = self::getModel();
                $tModel->update($uSlug, $tInput);

                Session::setFlash('notification', 'Record modified.');
                Http::redirect('blackmore/categories');

                return;
            }

            $tViewbag['error'] = implode('<br />', Validation::getErrorMessages(true));

            foreach ($tModule['fieldList'] as $tField) {
                $tIsView = array_key_exists('view', $tField['methods']);
                $tIsEdit = array_key_exists('edit', $tField['methods']);

                if ($tIsView || $tIsEdit) {
                    switch ($tField['type']) {
                        case 'enum':
                            $tTypes = array();
                            foreach ($tField['valueList'] as $tValue) {
                                $tTypes[$tValue['name']] = $tValue['title'];
                            }

                            $tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
                            if (!$tIsEdit) {
                                $tAttributes['readonly'] = 'readonly';
                            }

                            $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                                'select',
                                $tAttributes,
                                Html::selectOptions($tTypes, Request::post($tField['name'], null))
                            ) . '</p>';
                            break;
                        default:
                            $tAttributes = array(
                                'type' => 'text',
                                'name' => $tField['name'],
                                'value' => Request::post($tField['name'], ''),
                                'class' => 'input input_' . $tField['type']
                            );
                            if (!$tIsEdit) {
                                $tAttributes['readonly'] = 'readonly';
                            }

                            $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
                            break;
                    }
                }

                $tViewbag['fields'][] = array(
                    'data' => $tField,
                    'html' => $tTag
                );
            }

            Views::viewFile('{core}views/blackmore/models/form.php', $tViewbag);

            return;
        }

        $tModel = self::getModel();
        $tCategory = $tModel->getBySlug($tModule['name'], $uSlug);

        foreach ($tModule['fieldList'] as $tField) {
            $tIsView = array_key_exists('view', $tField['methods']);
            $tIsEdit = array_key_exists('edit', $tField['methods']);

            if ($tIsView || $tIsEdit) {
                switch ($tField['type']) {
                    case 'enum':
                        $tTypes = array();
                        foreach ($tField['valueList'] as $tValue) {
                            $tTypes[$tValue['name']] = $tValue['title'];
                        }

                        $tAttributes = array('name' => $tField['name'], 'class' => 'input input_' . $tField['type']);
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                            'select',
                            $tAttributes,
                            Html::selectOptions($tTypes, $tCategory[$tField['name']])
                        ) . '</p>';
                        break;
                    default:
                        $tAttributes = array(
                            'type' => 'text',
                            'name' => $tField['name'],
                            'value' => $tCategory[$tField['name']],
                            'class' => 'input input_' . $tField['type']
                        );
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
                        break;
                }
            }

            $tViewbag['fields'][] = array(
                'data' => $tField,
                'html' => $tTag
            );
        }

        Views::viewFile('{core}views/blackmore/models/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function remove($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        Session::setFlash('notification', 'Category removed.');
        Http::redirect('blackmore/categories');
    }
}
