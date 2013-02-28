<?php

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Html\Html;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Mvc\Mvc;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\String\String;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Zmodels\Zmodel;
use Scabbia\Extensions\Zmodels\Zmodels;
use Scabbia\Config;

/**
 * @ignore
 */
class BlackmoreZmodels
{
    /**
     * @ignore
     */
    public static function blackmoreRegisterModules($uParms)
    {
        $uParms['modules']['index']['submenus'] = true;

        $uParms['modules']['index']['actions'][] = array(
            'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::generateSql',
            'menutitle' => 'Generate Zmodel SQL',
            'action' => 'generateSql'
        );

        foreach (Zmodels::$zmodels as $tKey => $tZmodel) {
            $uParms['modules'][$tKey] = array(
                'title' => $tZmodel['title'],
                'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::all',
                'submenus' => true,
                'actions' => array(
                    array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::add',
                        'menutitle' => 'Add ' . $tZmodel['singularTitle'],
                        'action' => 'add'
                    ),
                    array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::edit',
                        'action' => 'edit'
                    ),
                    array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::remove',
                        'action' => 'remove'
                    ),
                    array(
                        'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreZmodels::all',
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
    public static function getModel()
    {
        return Controllers::load('BlackmoreZmodelModel', null, Config::get('blackmore/database', null));
    }

    /**
     * @ignore
     */
    public static function generateSql()
    {
        Auth::checkRedirect('admin');

        $tZmodel = new Zmodel('categories');
        $tSql = $tZmodel->ddlCreateSql();

        Views::viewFile(
            '{core}views/blackmore/zmodels/sql.php',
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
        $tModule = & Zmodels::$zmodels[Blackmore::$module];

        $tRows = $tModel->getAll($tModule['name']);

        Views::viewFile(
            '{core}views/blackmore/zmodels/list.php',
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

        $tModule = & Zmodels::$zmodels[Blackmore::$module];
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
                Mvc::redirect('blackmore/categories');

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

        Views::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function edit($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        $tModule = & Zmodels::$zmodels[Blackmore::$module];
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
                Mvc::redirect('blackmore/categories');

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

            Views::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);

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

        Views::viewFile('{core}views/blackmore/zmodels/form.php', $tViewbag);
    }

    /**
     * @ignore
     */
    public static function remove($uAction, $uSlug)
    {
        Auth::checkRedirect('editor');

        Session::setFlash('notification', 'Category removed.');
        Mvc::redirect('blackmore/categories');
    }
}
