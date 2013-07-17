<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Media;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Session\Session;
use Scabbia\Config;
use Scabbia\Io;

/**
 * Media Extension: Captcha class
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 */
class Captcha
{
    /**
     * Generates and outputs a captcha image
     *
     * @param string $uCookieName name of the cookie which will be stored on the client side
     *
     * @return string generated captcha code
     */
    public static function generate($uCookieName = 'captcha')
    {
        $tFontFile = Io::translatePath(Config::get('captcha/fontFile', '{core}resources/fonts/KabobExtrabold.ttf'));
        $tFontSize = (int)Config::get('captcha/fontSize', 45);
        $tLength = (int)Config::get('captcha/length', 8);

        // pick a random word
        $tCode = String::generatePassword($tLength);

        // create a random gray shade
        $tColorScale = rand(40, 120);

        // allocate the image and colors
        $tImageCanvas = imagecreatetruecolor(300, 80);
        $tColorBackground = imagecolorallocate($tImageCanvas, 255, 255, 255);
        $tColorBackgroundChars = imagecolorallocatealpha($tImageCanvas, $tColorScale, $tColorScale, $tColorScale, 80);
        $tColorTextShadow = imagecolorallocatealpha($tImageCanvas, 255, 255, 255, 20);
        $tColorText = imagecolorallocatealpha(
            $tImageCanvas,
            $tColorScale + 25,
            $tColorScale + 10,
            $tColorScale + 10,
            30
        );

        // clear the background
        imagefilledrectangle($tImageCanvas, 0, 0, 300, 80, $tColorBackground);

        // create the background letters
        $tBackgroundChars = 'abcdefghijklmnopqrstuvwxyz';

        for ($i = 0; $i < rand(60, 120); $i++) {
            // randomize the place and angle
            $x = rand(-50, 300);
            $y = rand(-50, 80);
            $tAngle = rand(-90, 90);

            imagettftext(
                $tImageCanvas,
                $tFontSize,
                $tAngle,
                $x,
                $y,
                $tColorBackgroundChars,
                $tFontFile,
                $tBackgroundChars[rand(0, strlen($tBackgroundChars) - 1)]
            );
        }

        // randomize the start of the code
        $x = 50 + rand(-40, 30 - (strlen($tCode) - 6) * 24);
        $y = 56 + rand(-8, 8);

        // write the code letter-by-letter
        for ($i = 0; $i < strlen($tCode); $i++) {
            // angle is random
            $tAngle = rand(-10, 10);

            // create the shadow for the letter
            for ($ax = -1; $ax < 0; $ax++) {
                for ($ay = -1; $ay < 0; $ay++) {
                    imagettftext(
                        $tImageCanvas,
                        $tFontSize,
                        $tAngle,
                        $x + $ax,
                        $y + $ay,
                        $tColorTextShadow,
                        $tFontFile,
                        $tCode[$i]
                    );
                }
            }

            // create the letter
            imagettftext($tImageCanvas, $tFontSize, $tAngle, $x, $y, $tColorText, $tFontFile, $tCode[$i]);

            // calculate the place of the next letter
            $y += rand(-2, 2);
            $tTemp = imagettfbbox($tFontSize, 0, $tFontFile, $tCode[$i]);
            $x += $tTemp[2] + rand(-4, 0);
        }

        // fancy border
        imagerectangle($tImageCanvas, 0, 0, 299, 79, $tColorText);
        imagerectangle($tImageCanvas, 1, 1, 298, 78, $tColorBackground);

        // store the code in session

        Session::set($uCookieName, $tCode);

        // try to avoid caching
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT', true);
        header('Pragma: public', true);
        header('Cache-Control: no-store, no-cache, must-revalidate', true);
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Type: image/png', true);
        header('Content-Disposition: inline;filename=' . $uCookieName . '.png', true);

        // clean up
        imagepng($tImageCanvas);
        imagedestroy($tImageCanvas);

        // return the code
        return $tCode;
    }

    /**
     * @ignore
     */
    public static function check($uCode, $uCookieName = 'captcha')
    {
        // check the supplied code
        $tResult = (Session::getFlash($uCookieName, "") === strtolower($uCode));

        // return the result
        return $tResult;
    }
}
