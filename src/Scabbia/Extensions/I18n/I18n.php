<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\I18n;

use Scabbia\Config;
use Scabbia\Framework;

/**
 * I18n Extension
 *
 * @package Scabbia
 * @subpackage I18n
 * @version 1.1.0
 *
 * @todo translitIt
 * @todo intl extension methods
 * @todo localeconv methods
 */
class I18n
{
    /**
     * @ignore
     */
    public static $languages = null;
    /**
     * @ignore
     */
    public static $language = null;


    /**
     * @ignore
     */
    /*
    public static function extensionLoad()
    {
        // Use the Universal Coordinated Time and most common English standards
        date_default_timezone_set('UTC');

        //! todo: determine language by browser's language priorities.
    }
    */

    /**
     * @ignore
     */
    public static function setLanguage($uLanguage, $uLastChoice = false)
    {
        if (is_null(self::$languages)) {
            self::$languages = array();

            foreach (Config::get('i18n/languageList', array()) as $tLanguage) {
                self::$languages[$tLanguage['id']] = array(
                    'key' => $tLanguage['id'],
                    'locale' => $tLanguage['locale'],
                    // 'localewin' => $tLanguage['@localewin'],
                    'internalEncoding' => $tLanguage['internalEncoding'],
                    'name' => $tLanguage['name']
                );
            }
        }

        if (isset(self::$languages[$uLanguage])) {
            self::$language = self::$languages[$uLanguage];
        } else {
            if ($uLastChoice) {
                $tExploded = explode('-', $uLanguage, 2);

                if (isset(self::$languages[$tExploded[0]])) {
                    self::$language = self::$languages[$tExploded[0]];
                }
            }
        }

        if (!is_null(self::$language)) {
            // if (DIRECTORY_SEPARATOR == '\\') {
            //     $tLocale = explode('.', self::$language['localewin'], 2);
            // }
            // else {
            $tLocale = explode('.', self::$language['locale'], 2);
            // }

            $tLocale['all'] = implode('.', $tLocale);

            // putenv('LC_ALL=' . $tLocale[0]);
            if (!Framework::$readonly) {
                putenv('LANG=' . $tLocale[0]);
            }
            setlocale(LC_ALL, $tLocale[0]);

            mb_internal_encoding(self::$language['internalEncoding']);
            mb_http_output(self::$language['internalEncoding']);

            // bindtextdomain('core', Framework::$corepath . 'locale');
            // bind_textdomain_codeset('core', self::$language['internalEncoding']);

            bindtextdomain('application', Framework::$apppath . 'locale');
            bind_textdomain_codeset('application', self::$language['internalEncoding']);

            textdomain('application');

            return true;
        }

        return false;
    }
}
