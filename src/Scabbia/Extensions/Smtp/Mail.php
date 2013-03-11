<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Smtp;

use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions\Mime\Mimepart;
use Scabbia\Extensions\Mime\Multipart;
use Scabbia\Extensions\Smtp\Smtp;

/**
 * Smtp Extension: Mail Class
 *
 * @package Scabbia
 * @subpackage Smtp
 * @version 1.1.0
 */
class Mail
{
    /**
     * @ignore
     */
    public $from;
    /**
     * @ignore
     */
    public $to;
    /**
     * @ignore
     */
    public $subject;
    /**
     * @ignore
     */
    public $headers = array();
    /**
     * @ignore
     */
    public $content;
    /**
     * @ignore
     */
    public $parts = array();


    /**
     * @ignore
     */
    public function addPart($uFilename, $uContent, $uEncoding = '8bit', $uType = null)
    {
        $tMimepart = new Mimepart();
        $tMimepart->filename = $uFilename;

        if (!is_null($uType)) {
            $tMimepart->type = $uType;
        } else {
            $tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
            $tMimepart->type = Mime::getType($tExtension, 'text/plain');
        }


        $tMimepart->transferEncoding = $uEncoding;
        $tMimepart->content = $uContent;

        $this->parts[] = $tMimepart;

        return $tMimepart;
    }

    /**
     * @ignore
     */
    public function addAttachment($uFilename, $uPath, $uEncoding = 'base64', $uType = null)
    {
        $tMimepart = new Mimepart();
        $tMimepart->filename = $uFilename;

        if (!is_null($uType)) {
            $tMimepart->type = $uType;
        } else {
            $tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
            $tMimepart->type = Mime::getType($tExtension, 'application/octet-stream');
        }

        $tMimepart->transferEncoding = $uEncoding;
        $tMimepart->load($uPath);

        $this->parts[] = $tMimepart;

        return $tMimepart;
    }

    /**
     * @ignore
     */
    public function getContent()
    {
        $tHeaders = $this->headers;

        if (!array_key_exists('From', $tHeaders)) {
            $tHeaders['From'] = $this->from;
        }
        if (!array_key_exists('To', $tHeaders)) {
            $tHeaders['To'] = $this->to;
        }
        if (!array_key_exists('Subject', $tHeaders)) {
            $tHeaders['Subject'] = $this->subject;
        }

        if (count($this->parts) > 0) {
            $tMain = new Multipart('mail', Multipart::ALTERNATIVE);
            $tMain->filename = 'mail.eml';
            $tMain->content = $this->content;
            $tMain->headers = $tHeaders;

            foreach ($this->parts as $tPart) {
                $tMain->parts[] = $tPart;
            }

            return $tMain->compile();
        }

        $tString = '';
        foreach ($tHeaders as $tKey => $tValue) {
            $tString .= $tKey . ': ' . $tValue . "\n";
        }
        $tString .= "\n" . $this->content;

        return $tString;
    }

    /**
     * @ignore
     */
    public function send()
    {
        Smtp::send($this->from, $this->to, $this->getContent());
    }
}
