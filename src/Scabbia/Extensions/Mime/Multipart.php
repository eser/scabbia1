<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mime;

use Scabbia\Extensions\Mime\Mimepart;

/**
 * Mime Extension: Multipart Class
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 */
class Multipart
{
    /**
     * @ignore
     */
    const RELATED = 0;
    /**
     * @ignore
     */
    const ALTERNATIVE = 1;


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
    public $boundaryName;
    /**
     * @ignore
     */
    public $boundaryType;
    /**
     * @ignore
     */
    public $content = 'This is a multi-part message in MIME format.';
    /**
     * @ignore
     */
    public $parts = array();
    /**
     * @ignore
     */
    public $filename;


    /**
     * @ignore
     */
    public function __construct($uBoundaryName = 'mimeboundary', $uBoundaryType = static::ALTERNATIVE)
    {
        $this->boundaryName = $uBoundaryName;
        $this->boundaryType = $uBoundaryType;
    }

    /**
     * @ignore
     */
    public function compileBody()
    {
        $tString = $this->content . "\n\n";

        foreach ($this->parts as $tPart) {
            $tString .= '--' . $this->boundaryName . "\n" . $tPart->compile(true) . "\n";
        }

        $tString .= '--' . $this->boundaryName . '--';

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
            if (!isset($tHeaders['MIME-Version'])) {
                $tHeaders['MIME-Version'] = '1.0';
            }

            if (count($this->parts) > 0) {
                $tPart = $this->parts[0];

                if (!isset($tHeaders['Content-Type'])) {
                    if ($this->boundaryType === static::ALTERNATIVE) {
                        $tHeaders['Content-Type'] = 'multipart/alternative; boundary=' . $this->boundaryName;
                    } else {
                        $tHeaders['Content-Type'] = 'multipart/related; boundary=' . $this->boundaryName;

                        if (isset($tPart->headers['Content-Id'])) {
                            $tHeaders['Content-Type'] .= '; start="' . $tPart->headers['Content-Id'] . '"';
                        }
                    }

                    if (isset($tPart->headers['Content-Type'])) {
                        $tContentType = explode(';', $tPart->headers['Content-Type'], 2);
                        $tHeaders['Content-Type'] .= '; type="' . $tContentType[0] . '"';
                    }
                }
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
    public function addPart()
    {
        $tNewPart = new Mimepart();
        $this->parts[] = $tNewPart;

        return $tNewPart;
    }
}
