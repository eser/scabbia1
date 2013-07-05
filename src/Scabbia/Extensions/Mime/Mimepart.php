<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mime;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Io;

/**
 * Mime Extension: Mimepart Class
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 */
class Mimepart
{
    /**
     * @ignore
     */
    public $headers = array();
    /**
     * @ignore
     */
    public $linesAfterHeaders = 1;
    /**
     * @ignore
     */
    public $type = 'text/plain';
    /**
     * @ignore
     */
    public $transferEncoding = 'base64';
    /**
     * @ignore
     */
    public $filename;
    /**
     * @ignore
     */
    public $content;


    /**
     * @ignore
     */
    public function compileBody()
    {
        $tString = "";

        if ($this->transferEncoding == 'base64') {
            $tString .= chunk_split(base64_encode($this->content));
        } elseif ($this->transferEncoding == 'quoted-printable') {
            $tString .= quoted_printable_encode($this->content);
        } else {
            $tString .= $this->content;
        }

        return $tString;
    }

    /**
     * @ignore
     */
    public function compile($uHeaders = true)
    {
        $tString = "";
        $tBody = $this->compileBody();

        if ($uHeaders) {
            $tHeaders = & $this->headers;
            if (!isset($tHeaders['Content-Id'])) {
                $tHeaders['Content-Id'] = '<' . String::generate(15) . '>';
            }

            if (!isset($tHeaders['Content-Type'])) {
                $tHeaders['Content-Type'] = $this->type;
            }

            if (!isset($tHeaders['Content-Transfer-Encoding'])) {
                $tHeaders['Content-Transfer-Encoding'] = $this->transferEncoding;
            }

            if (!isset($tHeaders['Content-Disposition']) && strlen($this->filename) > 0) {
                $tHeaders['Content-Disposition'] = 'attachment; filename=' . $this->filename;
            }

            if (!isset($tHeaders['Content-Length'])) {
                $tHeaders['Content-Length'] = strlen($tBody);
            }

            foreach ($tHeaders as $tKey => $tValue) {
                $tString .= $tKey . ': ' . $tValue . "\n";
            }

            for ($i = $this->linesAfterHeaders; $i > 0; $i--) {
                $tString .= "\n";
            }
        }

        $tString .= $tBody;

        return $tString;
    }

    /**
     * @ignore
     */
    public function load($uFilename)
    {
        $this->content = Io::read($uFilename);
    }
}
