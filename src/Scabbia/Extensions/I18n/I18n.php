<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\I18n;

use Scabbia\Extensions\I18n\Gettext;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;

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
 * @todo {app}translations/en_section.mo
 * @todo {app}translations/en_section.php
 */
class I18n
{
    /**
     * @ignore
     */
    const GETTEXT_EXTENSION = 0;
    /**
     * @ignore
     */
    const GETTEXT_IMPLEMENTATION = 1;


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
    public static $gettextType = self::GETTEXT_IMPLEMENTATION;
    /**
     * @ignore
     */
    public static $gettextInstance;


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
    public static function load()
    {
        if (self::$languages === null) {
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
    }

    /**
     * @ignore
     */
    public static function getLanguages()
    {
        self::load();

        return self::$languages;
    }

    /**
     * @ignore
     */
    public static function setLanguage($uLanguage, $uLastChoice = false)
    {
        self::load();

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

        if (self::$language !== null) {
            // if (DIRECTORY_SEPARATOR === '\\') {
            //     $tLocale = explode('.', self::$language['localewin'], 2);
            // }
            // else {
            $tLocale = explode('.', self::$language['locale'], 2);
            // }

            $tLocale['all'] = implode('.', $tLocale);

            // mb_internal_encoding(self::$language['internalEncoding']);
            mb_http_output(self::$language['internalEncoding']);

            putenv('LANG=' . $tLocale[0]);
            setlocale(LC_ALL, $tLocale[0]);

            // @todo path confusion
            if (Framework::$application !== null) {
                $tLocalePath = Framework::$application->path . 'locale';
                $tMoFile = $tLocalePath . '/' . $tLocale[0] . '/LC_MESSAGES/application.mo';
                $tPoFile = $tLocalePath . '/' . $tLocale[0] . '/LC_MESSAGES/application.po';

                if (!Framework::$readonly &&
                    (!Io::isReadable($tMoFile) || Io::isReadableAndNewer($tPoFile, filemtime($tMoFile)))) {
                    $tCompiler = new \TrekkSoft\Potomoco\Compiler();
                    $tCompiler->compile($tPoFile, $tMoFile);
                }

                if (self::$gettextType === self::GETTEXT_EXTENSION) {
                    // bindtextdomain('core', Framework::$corepath . 'locale');
                    // bind_textdomain_codeset('core', self::$language['internalEncoding']);

                    bindtextdomain('application', $tLocalePath);
                    bind_textdomain_codeset('application', self::$language['internalEncoding']);

                    textdomain('application');
                } else {
                    self::$gettextInstance = new Gettext($tMoFile);
                }
            }

            Framework::$variables['lang'] = self::$language['key'];

            return true;
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function _($uMessage)
    {
        if (self::$gettextType === self::GETTEXT_EXTENSION) {
            return \_($uMessage);
        }

        return self::$gettextInstance->gettext($uMessage);
    }

    /**
     * @ignore
     */
    public static function gettext($uMessage)
    {
        if (self::$gettextType === self::GETTEXT_EXTENSION) {
            return \gettext($uMessage);
        }

        return self::$gettextInstance->gettext($uMessage);
    }

    /**
     * @ignore
     */
    public static function ngettext($uMessage, $uMessagePlural, $uCount)
    {
        if (self::$gettextType === self::GETTEXT_EXTENSION) {
            return \ngettext($uMessage, $uMessagePlural, $uCount);
        }

        return self::$gettextInstance->ngettext($uMessage, $uMessagePlural, $uCount);
    }
}
