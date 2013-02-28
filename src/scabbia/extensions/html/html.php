<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Html;

use Scabbia\Extensions\Arrays\Arrays;
use Scabbia\Extensions\String\String;
use Scabbia\Framework;

/**
 * Html Extension
 *
 * @package Scabbia
 * @subpackage html
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends string, arrays
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 *
 * @todo form open
 * @todo form fields
 * @todo add callJavascriptFromRepository
 * @todo add callStylesheetFromRepository
 */
class Html
{
    /**
     * @ignore
     */
    public static $attributeOrder = array(
        'action', 'method', 'type', 'id', 'name', 'value',
        'href', 'src', 'width', 'height', 'cols', 'rows',
        'size', 'maxlength', 'rel', 'media', 'accept-charset',
        'accept', 'tabindex', 'accesskey', 'alt', 'title', 'class',
        'style', 'selected', 'checked', 'readonly', 'disabled'
    );


    /**
     * @ignore
     */
    public static function tag($uName, $uAttributes = array(), $uValue = null)
    {
        $tReturn = '<' . $uName;
        if (count($uAttributes) > 0) {
            $tReturn .= ' ' . self::attributes($uAttributes);
        }

        if (is_null($uValue)) {
            $tReturn .= ' />';
        } else {
            $tReturn .= '>' . $uValue . '</' . $uName . '>';
        }

        return $tReturn;
    }

    /**
     * @ignore
     */
    public static function attributes($uAttributes)
    {
        $tAttributes = Arrays::sortByPriority($uAttributes, self::$attributeOrder);

        $tReturn = array();
        foreach ($tAttributes as $tKey => $tValue) {
            if (is_null($tValue)) {
                $tReturn[] = $tKey . '="' . $tKey . '"';
                continue;
            }

            $tReturn[] = $tKey . '="' . String::htmlEscape($tValue) . '"';
        }

        return implode(' ', $tReturn);
    }

    /**
     * @ignore
     */
    public static function selectOptions($uOptions, $uDefault = null, $uField = null)
    {
        $tOutput = '';

        foreach ($uOptions as $tKey => $tVal) {
            $tOutput .= '<option value="' . String::dquote($tKey) . '"';
            if ($uDefault == $tKey) {
                $tOutput .= ' selected="selected"';
            }

            $tOutput .= '>' . (!is_null($uField) ? $tVal[$uField] : $tVal) . '</option>';
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function selectOptionsArray($uOptions, $uDefault = null, $uField = null)
    {
        $tOutput = array();

        foreach ($uOptions as $tKey => $tVal) {
            $tItem = '<option value="' . String::dquote($tKey) . '"';
            if ($uDefault == $tKey) {
                $tItem .= ' selected="selected"';
            }

            $tItem .= '>' . (!is_null($uField) ? $tVal[$uField] : $tVal) . '</option>';
            $tOutput[] = $tItem;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function radioOptions($uName, $uOptions, $uDefault = null, $uField = null)
    {
        $tOutput = '';

        foreach ($uOptions as $tKey => $tVal) {
            $tOutput .= '<label';

            if ($uDefault == $tKey) {
                $tOutput .= ' class="selected"';
            }

            $tOutput .= '><input type="radio" name="' . String::dquote($uName) . '" value="' . String::dquote($tKey) . '"';

            if ($uDefault == $tKey) {
                $tOutput .= ' checked="checked"';
            }

            $tOutput .= ' />' . (!is_null($uField) ? $tVal[$uField] : $tVal) . '</label>';
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function radioOptionsArray($uName, $uOptions, $uDefault = null, $uField = null)
    {
        $tOutput = array();

        foreach ($uOptions as $tKey => $tVal) {
            $tItem = '<label';

            if ($uDefault == $tKey) {
                $tItem .= ' class="selected"';
            }

            $tItem .= '><input type="radio" name="' . String::dquote($uName) . '" value="' . String::dquote($tKey) . '"';

            if ($uDefault == $tKey) {
                $tItem .= ' checked="checked"';
            }

            $tItem .= ' />' . (!is_null($uField) ? $tVal[$uField] : $tVal) . '</label>';
            $tOutput[] = $tItem;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function textBox($uName, $uValue = '', $uAttributes = array())
    {
        $uAttributes['name'] = $uName;
        $uAttributes['value'] = $uValue;

        $tOutput = '<input type="text" ' . self::attributes($uAttributes) . ' />';

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function checkBox($uName, $uValue, $uCurrentValue = null, $uText = null, $uAttributes = array())
    {
        $uAttributes['name'] = $uName;
        $uAttributes['value'] = $uValue;

        if ($uCurrentValue == $uValue) {
            $uAttributes['checked'] = 'checked';
        }

        $tOutput = '<label><input type="checkbox" ' . self::attributes($uAttributes) . ' />';

        if (!is_null($uText)) {
            $tOutput .= $uText;
        }

        $tOutput .= '</label>';

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function pager($uOptions)
    {
        $tPages = ceil($uOptions['total'] / $uOptions['pagesize']);

        if (!isset($uOptions['divider'])) {
            $uOptions['divider'] = '';
        }

        if (!isset($uOptions['dots'])) {
            $uOptions['dots'] = ' ... ';
        }

        // if (!isset($uOptions['link'])) {
        //     $uOptions['link'] = '<a href="{root}?home/index/{page}" class="pagerlink">{pagetext}</a>';
        // }

        if (!isset($uOptions['passivelink'])) {
            $uOptions['passivelink'] = $uOptions['link'];
        }

        if (!isset($uOptions['activelink'])) {
            $uOptions['activelink'] = $uOptions['passivelink'];
        }

        if (!isset($uOptions['firstlast'])) {
            $uOptions['firstlast'] = true;
        }

        if (isset($uOptions['current'])) {
            $tCurrent = (int)$uOptions['current'];
            if ($tCurrent <= 0) { // || $tCurrent > $tPages
                $tCurrent = 1;
            }
        } else {
            $tCurrent = 1;
        }

        if (isset($uOptions['numlinks'])) {
            $tNumLinks = (int)$uOptions['numlinks'];
        } else {
            $tNumLinks = 10;
        }

        $tStart = $tCurrent - floor($tNumLinks * 0.5);
        $tEnd = $tCurrent + floor($tNumLinks * 0.5) - 1;

        if ($tStart < 1) {
            $tEnd += abs($tStart) + 1;
            $tStart = 1;
        }

        if ($tEnd > $tPages) {
            if ($tStart - $tEnd - $tPages > 0) {
                $tStart -= $tEnd - $tPages;
            }
            $tEnd = $tPages;
        }

        $tResult = '';

        if ($tPages > 1) {
            if ($tCurrent <= 1) {
                if ($uOptions['firstlast']) {
                    $tResult .= String::format($uOptions['passivelink'], array('root' => Framework::$siteroot, 'page' => '1', 'pagetext' => '&lt;&lt;'));
                }
                $tResult .= String::format($uOptions['passivelink'], array('root' => Framework::$siteroot, 'page' => '1', 'pagetext' => '&lt;'));
            } else {
                if ($uOptions['firstlast']) {
                    $tResult .= String::format($uOptions['link'], array('root' => Framework::$siteroot, 'page' => '1', 'pagetext' => '&lt;&lt;'));
                }
                $tResult .= String::format($uOptions['link'], array('root' => Framework::$siteroot, 'page' => $tCurrent - 1, 'pagetext' => '&lt;'));
            }

            if ($tStart > 1) {
                $tResult .= $uOptions['dots'];
            } else {
                $tResult .= $uOptions['divider'];
            }
        }

        for ($i = $tStart; $i <= $tEnd; $i++) {
            if ($tCurrent == $i) {
                $tResult .= String::format($uOptions['activelink'], array('root' => Framework::$siteroot, 'page' => $i, 'pagetext' => $i));
            } else {
                $tResult .= String::format($uOptions['link'], array('root' => Framework::$siteroot, 'page' => $i, 'pagetext' => $i));
            }

            if ($i != $tEnd) {
                $tResult .= $uOptions['divider'];
            }
        }

        if ($tPages > 1) {
            if ($tEnd < $tPages) {
                $tResult .= $uOptions['dots'];
            } else {
                $tResult .= $uOptions['divider'];
            }

            if ($tCurrent >= $tPages) {
                $tResult .= String::format($uOptions['passivelink'], array('root' => Framework::$siteroot, 'page' => $tPages, 'pagetext' => '&gt;'));
                if ($uOptions['firstlast']) {
                    $tResult .= String::format($uOptions['passivelink'], array('root' => Framework::$siteroot, 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
                }
            } else {
                $tResult .= String::format($uOptions['link'], array('root' => Framework::$siteroot, 'page' => $tCurrent + 1, 'pagetext' => '&gt;'));
                if ($uOptions['firstlast']) {
                    $tResult .= String::format($uOptions['link'], array('root' => Framework::$siteroot, 'page' => $tPages, 'pagetext' => '&gt;&gt;'));
                }
            }
        }

        return $tResult;
    }

    /**
     * @ignore
     */
    public static function doctype($uType = 'html5')
    {
        switch ($uType) {
            case 'html5':
            case 'xhtml5':
                return '<!DOCTYPE html>' . PHP_EOL;
                break;
            case 'xhtml11':
            case 'xhtml1.1':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . PHP_EOL;
                break;
            case 'xhtml1':
            case 'xhtml1-strict':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL;
                break;
            case 'xhtml1-trans':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL;
                break;
            case 'xhtml1-frame':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">' . PHP_EOL;
                break;
            case 'html4-strict':
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . PHP_EOL;
                break;
            case 'html4':
            case 'html4-trans':
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . PHP_EOL;
                break;
            case 'html4-frame':
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">' . PHP_EOL;
                break;
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function table($uOptions)
    {
        if (!isset($uOptions['table'])) {
            $uOptions['table'] = '<table>';
        }

        if (!isset($uOptions['cell'])) {
            $uOptions['cell'] = '<td>{value}</td>';
        }

        if (!isset($uOptions['header'])) {
            $uOptions['header'] = '<th>{value}</th>';
        }

        $tResult = String::format($uOptions['table'], array());

        if (isset($uOptions['headers'])) {
            $tResult .= '<tr>';
            foreach ($uOptions['headers'] as $tColumn) {
                $tResult .= String::format($uOptions['header'], array('value' => $tColumn));
            }
            $tResult .= '</tr>';
        }

        $tCount = 0;
        foreach ($uOptions['data'] as $tRow) {
            if (isset($uOptions['rowFunc'])) {
                $tResult .= call_user_func($uOptions['rowFunc'], $tRow, $tCount++);
            } else {
                if (isset($uOptions['row'])) {
                    $tResult .= String::format($uOptions['row'], $tRow);
                } else {
                    $tResult .= '<tr>';

                    foreach ($tRow as $tColumn) {
                        $tResult .= String::format($uOptions['cell'], array('value' => $tColumn));
                    }

                    $tResult .= '</tr>';
                }
            }
        }

        $tResult .= '</table>';

        return $tResult;
    }
}
