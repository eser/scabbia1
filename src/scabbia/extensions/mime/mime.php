<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mime;

use Scabbia\Extensions\Helpers\String;

/**
 * Mime Extension
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 */
class Mime
{
    /**
     * @ignore
     */
    public static function getType($uExtension, $uDefault = 'application/octet-stream')
    {
        $tExtension = String::toLower($uExtension);

        if ($tExtension === 'pdf') {
            return 'application/pdf';
        } elseif ($tExtension === 'exe') {
            return 'application/octet-stream';
        } elseif ($tExtension === 'dll') {
            return 'application/x-msdownload';
        } elseif ($tExtension === 'zip') {
            return 'application/zip';
        } elseif ($tExtension === 'rar') {
            return 'application/x-rar-compressed';
        } elseif ($tExtension === 'gz' || $tExtension === 'gzip' || $tExtension === 'tgz') {
            return 'application/x-gzip';
        } elseif ($tExtension === 'tar') {
            return 'application/x-tar';
        } elseif ($tExtension === 'jar') {
            return 'application/java-archive';
        } elseif ($tExtension === 'deb') {
            return 'application/x-deb';
        } elseif ($tExtension === 'deb') {
            return 'application/x-apple-diskimage';
        } elseif ($tExtension === 'deb') {
            return 'text/csv';
        } elseif ($tExtension === 'txt' || $tExtension === 'text' || $tExtension === 'log' || $tExtension === 'ini') {
            return 'text/plain';
        } elseif ($tExtension === 'rtf') {
            return 'text/rtf';
        } elseif ($tExtension === 'odt') {
            return 'application/vnd.oasis.opendocument.text';
        } elseif ($tExtension === 'smil') {
            return 'application/smil';
        } elseif ($tExtension === 'eml') {
            return 'message/rfc822';
        } elseif ($tExtension === 'xml' || $tExtension === 'xsl') {
            return 'text/xml';
        } elseif ($tExtension === 'doc' || $tExtension === 'dot' || $tExtension === 'word') {
            return 'application/msword';
        } elseif ($tExtension === 'docx' || $tExtension === 'dotx') {
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        } elseif ($tExtension === 'xls') {
            return 'application/vnd.ms-excel';
        } elseif ($tExtension === 'xl') {
            return 'application/excel';
        } elseif ($tExtension === 'xlsx') {
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } elseif ($tExtension === 'ppt') {
            return 'application/vnd.ms-powerpoint';
        } elseif ($tExtension === 'ics') {
            return 'text/calendar';
        } elseif ($tExtension === 'bmp') {
            return 'image/x-ms-bmp';
        } elseif ($tExtension === 'gif') {
            return 'image/gif';
        } elseif ($tExtension === 'png') {
            return 'image/png';
        } elseif ($tExtension === 'jpeg' || $tExtension === 'jpe' || $tExtension === 'jpg') {
            return 'image/jpeg';
        } elseif ($tExtension === 'webp') {
            return 'image/webp';
        } elseif ($tExtension === 'tif' || $tExtension === 'tiff') {
            return 'image/tiff';
        } elseif ($tExtension === 'psd') {
            return 'image/vnd.adobe.photoshop';
        } elseif ($tExtension === 'ai' || $tExtension === 'eps' || $tExtension === 'ps') {
            return 'application/postscript';
        } elseif ($tExtension === 'cdr') {
            return 'application/cdr';
        } elseif ($tExtension === 'mid' || $tExtension === 'midi') {
            return 'audio/midi';
        } elseif ($tExtension === 'mpga' || $tExtension === 'mp2' || $tExtension === 'mp3') {
            return 'audio/mpeg';
        } elseif ($tExtension === 'aif' || $tExtension === 'aiff' || $tExtension === 'aifc') {
            return 'audio/x-aiff';
        } elseif ($tExtension === 'wav') {
            return 'audio/x-wav';
        } elseif ($tExtension === 'aac') {
            return 'audio/aac';
        } elseif ($tExtension === 'ogg') {
            return 'audio/ogg';
        } elseif ($tExtension === 'wma') {
            return 'audio/x-ms-wma';
        } elseif ($tExtension === 'm4a') {
            return 'audio/x-m4a';
        } elseif ($tExtension === 'mpeg' || $tExtension === 'mpg' || $tExtension === 'mpe') {
            return 'video/mpeg';
        } elseif ($tExtension === 'mp4' || $tExtension === 'f4v') {
            return 'application/mp4';
        } elseif ($tExtension === 'qt' || $tExtension === 'mov') {
            return 'video/quicktime';
        } elseif ($tExtension === 'avi') {
            return 'video/x-msvideo';
        } elseif ($tExtension === 'wmv') {
            return 'video/x-ms-wmv';
        } elseif ($tExtension === 'webm') {
            return 'video/webm';
        } elseif ($tExtension === 'swf') {
            return 'application/x-shockwave-flash';
        } elseif ($tExtension === 'flv') {
            return 'video/x-flv';
        } elseif ($tExtension === 'htm' || $tExtension === 'html' || $tExtension === 'shtm' ||
            $tExtension === 'shtml') {
            return 'text/html';
        } elseif ($tExtension === 'php') {
            return 'application/x-httpd-php';
        } elseif ($tExtension === 'phps') {
            return 'application/x-httpd-php-source';
        } elseif ($tExtension === 'css') {
            return 'text/css';
        } elseif ($tExtension === 'js') {
            return 'application/x-javascript';
        } elseif ($tExtension === 'json') {
            return 'application/json';
        } elseif ($tExtension === 'c' || $tExtension === 'h') {
            return 'text/x-c';
        } elseif ($tExtension === 'py') {
            return 'application/x-python';
        } elseif ($tExtension === 'sh') {
            return 'text/x-shellscript';
        } elseif ($tExtension === 'pem') {
            return 'application/x-x509-user-cert';
        } elseif ($tExtension === 'crt' || $tExtension === 'cer') {
            return 'application/x-x509-ca-cert';
        } elseif ($tExtension === 'pgp') {
            return 'application/pgp';
        } elseif ($tExtension === 'gpg') {
            return 'application/gpg-keys';
        }

        return $uDefault;
    }
}
