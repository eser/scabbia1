<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;
use Scabbia\Framework;

/**
 * Datasources Extension: RestSource class
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 *
 * @todo headers w/ CURLOPT_HEADERFUNCTION
 * @todo auth
 * @todo multipart data post & content-length
 * @todo exceptions
 * @todo useragent
 * @todo timeouts
 */
class RestSource implements IDataInterface
{
    /**
     * @ignore
     */
    public static $type = 'rest';


    /**
     * @ignore
     */
    public $curlObject = null;
    /**
     * @ignore
     */
    public $baseUrl;
    /**
     * @ignore
     */
    public $auth;
    /**
     * @ignore
     */
    public $lastResponse;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->baseUrl = (isset($uConfig['baseUrl'])) ? rtrim($uConfig['baseUrl'], '/') : '';

        if (isset($uConfig['auth'])) {
            $this->auth = $uConfig['auth'];
        }
    }

    /**
     * @ignore
     */
    public function makeRequest($uMethod, $uUrl, $uPostFields = null, array $uHeaders = null)
    {
        if ($this->curlObject === null) {
            $this->curlObject = curl_init();
            curl_setopt_array(
                $this->curlObject,
                array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT => '',
                    CURLOPT_AUTOREFERER => true,
                    CURLOPT_CONNECTTIMEOUT => 120,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_VERBOSE => 1,
                    CURLOPT_HEADER => 0,
                    CURLOPT_FRESH_CONNECT => false,
                    CURLOPT_FORBID_REUSE => false
                )
            );
        }

        curl_setopt($this->curlObject, CURLOPT_URL, $this->baseUrl . $uUrl);
        curl_setopt($this->curlObject, CURLOPT_CUSTOMREQUEST, $uMethod);

        if ($uPostFields !== null) {
            curl_setopt($this->curlObject, CURLOPT_POST, 1);
            curl_setopt($this->curlObject, CURLOPT_POSTFIELDS, $uPostFields);
        } else {
            curl_setopt($this->curlObject, CURLOPT_POST, 0);
            // curl_setopt($this->curlObject, CURLOPT_POSTFIELDS, '');
        }

        if ($uHeaders !== null) {
            $tHeaders = array();
            foreach ($uHeaders as $tHeaderKey => $tHeader) {
                $tHeaders[] = $tHeaderKey . ': ' . $tHeader;
            }

            curl_setopt($this->curlObject, CURLOPT_HTTPHEADER, $tHeaders);
        }

        $tReturn = curl_exec($this->curlObject);
        $tInformation = curl_getinfo($this->curlObject);
        $tInformation['return'] = $tReturn;

        // curl_close($this->curlObject);

        return $tInformation;
    }

    /**
     * @ignore
     */
    public function __call($uMethod, $uArgs)
    {
        array_unshift($uArgs, $uMethod);
        return call_user_func_array(array(&$this, 'makeRequest'), $uArgs);
    }
}
