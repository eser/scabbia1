<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mime;

use Scabbia\Extensions\String\String;

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
        switch (String::toLower($uExtension)) {
            case 'pdf':
                $tType = 'application/pdf';
                break;
            case 'exe':
                $tType = 'application/octet-stream';
                break;
            case 'dll':
                $tType = 'application/x-msdownload';
                break;
            case 'zip':
                $tType = 'application/zip';
                break;
            case 'rar':
                $tType = 'application/x-rar-compressed';
                break;
            case 'gz':
            case 'gzip':
            case 'tgz':
                $tType = 'application/x-gzip';
                break;
            case 'tar':
                $tType = 'application/x-tar';
                break;
            case 'jar':
                $tType = 'application/java-archive';
                break;
            case 'deb':
                $tType = 'application/x-deb';
                break;
            case 'dmg':
                $tType = 'application/x-apple-diskimage';
                break;
            case 'csv':
                $tType = 'text/csv';
                break;
            case 'txt':
            case 'text':
            case 'log':
            case 'ini':
                $tType = 'text/plain';
                break;
            case 'rtf':
                $tType = 'text/rtf';
                break;
            case 'odt':
                $tType = 'application/vnd.oasis.opendocument.text';
                break;
            case 'smil':
                $tType = 'application/smil';
                break;
            case 'eml':
                $tType = 'message/rfc822';
                break;
            case 'xml':
            case 'xsl':
                $tType = 'text/xml';
                break;
            case 'doc':
            case 'dot':
            case 'word':
                $tType = 'application/msword';
                break;
            case 'dotx':
            case 'docx':
                $tType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xls':
                $tType = 'application/vnd.ms-excel';
                break;
            case 'xl':
                $tType = 'application/excel';
                break;
            case 'xlsx':
                $tType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'ppt':
                $tType = 'application/vnd.ms-powerpoint';
                break;
            case 'ics':
                $tType = 'text/calendar';
                break;
            case 'bmp':
                $tType = 'image/x-ms-bmp';
                break;
            case 'gif':
                $tType = 'image/gif';
                break;
            case 'png':
                $tType = 'image/png';
                break;
            case 'jpeg':
            case 'jpe':
            case 'jpg':
                $tType = 'image/jpeg';
                break;
            case 'webp':
                $tType = 'image/webp';
                break;
            case 'tif':
            case 'tiff':
                $tType = 'image/tiff';
                break;
            case 'psd':
                $tType = 'image/vnd.adobe.photoshop';
                break;
            case 'ai':
            case 'eps':
            case 'ps':
                $tType = 'application/postscript';
                break;
            case 'cdr':
                $tType = 'application/cdr';
                break;
            case 'mid':
            case 'midi':
                $tType = 'audio/midi';
                break;
            case 'mpga':
            case 'mp2':
            case 'mp3':
                $tType = 'audio/mpeg';
                break;
            case 'aif':
            case 'aiff':
            case 'aifc':
                $tType = 'audio/x-aiff';
                break;
            case 'wav':
                $tType = 'audio/x-wav';
                break;
            case 'aac':
                $tType = 'audio/aac';
                break;
            case 'ogg':
                $tType = 'audio/ogg';
                break;
            case 'wma':
                $tType = 'audio/x-ms-wma';
                break;
            case 'm4a':
                $tType = 'audio/x-m4a';
                break;
            case 'mpeg':
            case 'mpg':
            case 'mpe':
                $tType = 'video/mpeg';
                break;
            case 'mp4':
            case 'f4v':
                $tType = 'application/mp4';
                break;
            case 'qt':
            case 'mov':
                $tType = 'video/quicktime';
                break;
            case 'avi':
                $tType = 'video/x-msvideo';
                break;
            case 'wmv':
                $tType = 'video/x-ms-wmv';
                break;
            case 'webm':
                $tType = 'video/webm';
                break;
            case 'swf':
                $tType = 'application/x-shockwave-flash';
                break;
            case 'flv':
                $tType = 'video/x-flv';
                break;
            case 'htm':
            case 'html':
            case 'shtm':
            case 'shtml':
                $tType = 'text/html';
                break;
            case 'php':
                $tType = 'application/x-httpd-php';
                break;
            case 'phps':
                $tType = 'application/x-httpd-php-source';
                break;
            case 'css':
                $tType = 'text/css';
                break;
            case 'js':
                $tType = 'application/x-javascript';
                break;
            case 'json':
                $tType = 'application/json';
                break;
            case 'c':
            case 'h':
                $tType = 'text/x-c';
                break;
            case 'py':
                $tType = 'application/x-python';
                break;
            case 'sh':
                $tType = 'text/x-shellscript';
                break;
            case 'pem':
                $tType = 'application/x-x509-user-cert';
                break;
            case 'crt':
            case 'cer':
                $tType = 'application/x-x509-ca-cert';
                break;
            case 'pgp':
                $tType = 'application/pgp';
                break;
            case 'gpg':
                $tType = 'application/gpg-keys';
                break;
            default:
                $tType = $uDefault;
        }

        return $tType;
    }
}
