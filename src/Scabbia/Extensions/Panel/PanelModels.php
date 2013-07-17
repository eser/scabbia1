<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Panel;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Helpers\Html;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Models\AutoModel;
use Scabbia\Extensions\Models\AutoModels;
use Scabbia\Config;
use Scabbia\Request;

/**
 * Panel Extension: Models Section
 *
 * @package Scabbia
 * @subpackage Panel
 * @version 1.1.0
 *
 * @todo panel-bootstrap integration on input fields, for example:
 * - required fields in red focus
 * - e-mail fields with prepend icon
 */
class PanelModels
{
    /**
     * @ignore
     */
    public static function menuGenerator(array &$uModules)
    {
        AutoModels::load();

        foreach (AutoModels::$autoModels as $tKey => $tAutoModel) {
            $uModules[$tKey] = array(
                'title' => $tAutoModel['title'],
                'actions' => array(
                    'add' => array(
                        'title' => 'Add ' . $tAutoModel['singularTitle'],
                        'icon' => 'plus',
                        'callback' => 'Scabbia\\Extensions\\Panel\\PanelModels::add'
                    ),
                    'edit' => array(
                        'callback' => 'Scabbia\\Extensions\\Panel\\PanelModels::edit'
                    ),
                    'remove' => array(
                        'callback' => 'Scabbia\\Extensions\\Panel\\PanelModels::remove'
                    ),
                    'index' => array(
                        'title' => 'All ' . $tAutoModel['title'],
                        'icon' => 'list-alt',
                        'callback' => 'Scabbia\\Extensions\\Panel\\PanelModels::index'
                    )
                )
            );
        }
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
            '{core}views/panel/models/sql.php',
            array(
                'sql' => $tSql
            )
        );
    }

    /**
     * @ignore
     */
    public static function index()
    {
        Auth::checkRedirect('editor');

        $tAutoModel = new AutoModel(Panel::$module);
        $tData = $tAutoModel->call('list');

        Views::viewFile(
            '{core}views/panel/models/list.php',
            array(
                'automodel' => $tAutoModel,
                'data' => $tData
            )
        );
    }

    /**
     * @ignore
     */
    public static function add($uAction)
    {
        Auth::checkRedirect('editor');

        $tModule = AutoModels::get(Panel::$module);
        $tAutoModel = new AutoModel($tModule['name']);

        $tFields = $tAutoModel->ddlGetFieldsForMethod('add');

        $tViewbag = array(
            'module' => $tModule,
            'postback' => Http::url('panel/' . Panel::$module . '/add'),
            'fields' => array()
        );

        if (Request::$method === 'post') {
            $tInput = array();

            foreach ($tFields as $tField) {
                $tInput[$tField['name']] = Request::post($tField['name']);

                if (!isset($tField['validation'])) {
                    continue;
                }

                // @todo add validation type as array key
                foreach ($tField['validation'] as $tFieldValidation) {
                    Validation::addRule($tField['name'])->add(
                        $tFieldValidation['type'],
                        isset($tFieldValidation['params']) ? $tFieldValidation['params'] : array()
                    )->errorMessage($tFieldValidation['message']);
                }
            }

            if (Validation::validate($_POST)) {
                $tAutoModel->insert($tInput);

                Session::set('notification', array('info', 'ok-sign', 'Record added.'));
                Http::redirect('panel/' . Panel::$module);

                return;
            }

            Session::set(
                'notification',
                array('error', 'remove-sign', Validation::getErrorMessages(true))
            );
        }

        foreach ($tFields as $tField) {
            if ($tField['type'] === 'enum') {
                $tTypes = array();
                foreach ($tField['valueList'] as $tValue) {
                    $tTypes[$tValue['name']] = $tValue['title'];
                }

                $tAttributes = array(
                    'name' => $tField['name'],
                    'class' => 'input-block-level input_' . $tField['type']
                );
                // if (!$tIsEdit) {
                //    $tAttributes['readonly'] = 'readonly';
                // }

                $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                    'select',
                    $tAttributes,
                    Html::selectOptions($tTypes, Request::post($tField['name'], null))
                ) . '</p>';
            } else {
                $tAttributes = array(
                    'type' => 'text',
                    'name' => $tField['name'],
                    'value' => Request::post($tField['name'], ""),
                    'class' => 'input-block-level input_' . $tField['type']
                );
                // if (!$tIsEdit) {
                //    $tAttributes['readonly'] = 'readonly';
                // }

                $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
            }

            $tViewbag['fields'][] = array(
                'data' => $tField,
                'html' => $tTag
            );
        }

        Views::viewFile('{core}views/panel/models/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function edit($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        $tModule = AutoModels::get(Panel::$module);
        $tViewbag = array(
            'module' => $tModule,
            'fields' => array()
        );

        if (Request::$method === 'post') {
            //! todo: validations
            Validation::addRule('name')->isRequired()->errorMessage('Name shouldn\'t be blank.');
            // Validation::addRule('slug')->isRequired()->errorMessage('Slug shouldn\'t be blank.');

            if (Validation::validate($_POST)) {
                $tSlug = Request::post('slug', "");
                if (strlen(rtrim($tSlug)) === 0) {
                    $tSlug = Request::post('name', "");
                }

                $tInput = array(
                    'type' => Request::post('type'),
                    'name' => Request::post('name'),
                    'slug' => String::slug(String::removeAccent($tSlug))
                );

                $tAutoModel = new AutoModel('categories');
                $tAutoModel->update($uSlug, $tInput);

                Session::set('notification', array('info', 'ok-sign', 'Record modified.'));
                Http::redirect('panel/categories');

                return;
            }

            Session::set('notification', array('error', 'remove-sign', Validation::getErrorMessages(true)));

            foreach ($tModule['fieldList'] as $tField) {
                $tIsView = array_key_exists('view', $tField['methods']);
                $tIsEdit = array_key_exists('edit', $tField['methods']);

                if ($tIsView || $tIsEdit) {
                    if ($tField['type'] === 'enum') {
                        $tTypes = array();
                        foreach ($tField['valueList'] as $tValue) {
                            $tTypes[$tValue['name']] = $tValue['title'];
                        }

                        $tAttributes = array(
                            'name' => $tField['name'],
                            'class' => 'input-block-level input_' . $tField['type']
                        );
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                            'select',
                            $tAttributes,
                            Html::selectOptions($tTypes, Request::post($tField['name'], null))
                        ) . '</p>';
                    } else {
                        $tAttributes = array(
                            'type' => 'text',
                            'name' => $tField['name'],
                            'value' => Request::post($tField['name'], ""),
                            'class' => 'input-block-level input_' . $tField['type']
                        );
                        if (!$tIsEdit) {
                            $tAttributes['readonly'] = 'readonly';
                        }

                        $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
                    }
                }

                $tViewbag['fields'][] = array(
                    'data' => $tField,
                    'html' => $tTag
                );
            }

            Views::viewFile('{core}views/panel/models/form.php', $tViewbag);

            return;
        }

        $tAutoModel = new AutoModel('categories');
        $tCategory = $tAutoModel->getBySlug($tModule['name'], $uSlug);

        foreach ($tModule['fieldList'] as $tField) {
            $tIsView = array_key_exists('view', $tField['methods']);
            $tIsEdit = array_key_exists('edit', $tField['methods']);

            if ($tIsView || $tIsEdit) {
                if ($tField['type'] === 'enum') {
                    $tTypes = array();
                    foreach ($tField['valueList'] as $tValue) {
                        $tTypes[$tValue['name']] = $tValue['title'];
                    }

                    $tAttributes = array(
                        'name' => $tField['name'],
                        'class' => 'input-block-level input_' . $tField['type']
                    );
                    if (!$tIsEdit) {
                        $tAttributes['readonly'] = 'readonly';
                    }

                    $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag(
                        'select',
                        $tAttributes,
                        Html::selectOptions($tTypes, $tCategory[$tField['name']])
                    ) . '</p>';
                } else {
                    $tAttributes = array(
                        'type' => 'text',
                        'name' => $tField['name'],
                        'value' => $tCategory[$tField['name']],
                        'class' => 'input-block-level input_' . $tField['type']
                    );
                    if (!$tIsEdit) {
                        $tAttributes['readonly'] = 'readonly';
                    }

                    $tTag = '<p>' . _($tField['title']) . ': ' . Html::tag('input', $tAttributes) . '</p>';
                }
            }

            $tViewbag['fields'][] = array(
                'data' => $tField,
                'html' => $tTag
            );
        }

        Views::viewFile('{core}views/panel/models/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function remove($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        Session::set('notification', array('info', 'ok-sign', 'Category removed.'));
        Http::redirect('panel/categories');
    }
}
