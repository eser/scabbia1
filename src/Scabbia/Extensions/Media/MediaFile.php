<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Media;

use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\Media\Media;
use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions;

/**
 * Media Extension: MediaFile Class
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 */
class MediaFile
{
    /**
     * @ignore
     */
    public $source;
    /**
     * @ignore
     */
    public $filename;
    /**
     * @ignore
     */
    public $extension;
    /**
     * @ignore
     */
    public $mime;
    /**
     * @ignore
     */
    public $hash;
    /**
     * @ignore
     */
    public $sw, $sh, $sa;
    /**
     * @ignore
     */
    public $size;
    /**
     * @ignore
     */
    public $image = null;
    /**
     * @ignore
     */
    public $background;


    /**
     * @ignore
     */
    public function __construct($uSource = null, $uOriginalFilename = null)
    {
        $this->source = $uSource;
        $this->background = array(255, 255, 255, 0);

        if (is_null($this->source)) {
            $this->sa = 1;
        } else {
            $tData = getimagesize($this->source);
            $this->sw = $tData[0];
            $this->sh = $tData[1];
            $this->sa = $this->sw / $this->sh;

            // get the source file extension
            if (is_null($uOriginalFilename)) {
                $uOriginalFilename = $this->source;
            }
            $this->filename = pathinfo($this->source, PATHINFO_FILENAME);
            $this->extension = pathinfo($uOriginalFilename, PATHINFO_EXTENSION);

            $this->mime = Mime::getType($this->extension);
            $this->size = filesize($this->source);

            // calculate a hash - used for cache files, etc
            $this->hash = Media::calculateHash($this->filename, $this->sw, $this->sh) . '.' . $this->extension;

            switch ($this->extension) {
                case 'jpeg':
                case 'jpe':
                case 'jpg':
                    $this->image = imagecreatefromjpeg($this->source);
                    break;
                case 'gif':
                    $this->image = imagecreatefromgif ($this->source);
                    break;
                case 'png':
                    $this->image = imagecreatefrompng($this->source);
                    imagealphablending($this->image, true);
                    imagesavealpha($this->image, true);
                    break;
            }
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        if (!is_null($this->image)) {
            imagedestroy($this->image);
        }
    }

    /**
     * @ignore
     */
    public function background()
    {
        $this->background = func_get_args();

        return $this;
    }

    /**
     * @ignore
     */
    public function write($uX, $uY, $uSize, $uColor, $uText)
    {
        return $this;
    }

    /**
     * @ignore
     */
    public function rotate($uDegree, $uBackground = 0)
    {
        $this->image = imagerotate($this->image, $uDegree, $uBackground);
        $this->sw = imagesx($this->image);
        $this->sh = imagesy($this->image);
        $this->sa = $this->sw / $this->sh;

        return $this;
    }

    /**
     * @ignore
     */
    public function getCache($uTag)
    {
        $tCachePath = Media::$cachePath . '/' . $uTag;

        if (file_exists($tCachePath)) {
            $tAge = time() - filemtime($tCachePath);

            if ($tAge < Media::$cacheAge) {
                return new mediaFile($tCachePath);
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public function resize($uWidth, $uHeight, $uMode = 'fit')
    {
        $tAspectRatio = $uWidth / $uHeight;

        switch ($uMode) {
            case 'fit':
                $tSourceX = 0;
                $tSourceY = 0;
                $tSourceW = $this->sw;
                $tSourceH = $this->sh;

                if ($uWidth == null && $uHeight != null) {
                    $uWidth = ceil($uHeight * $this->sa);
                } else {
                    if ($uWidth != null && $uHeight == null) {
                        $uHeight = ceil($uWidth / $this->sa);
                    } else {
                        if ($this->sa > $tAspectRatio) {
                            $uHeight = $uWidth / $this->sa;
                        } else {
                            if ($this->sa < $tAspectRatio) {
                                $uWidth = $uHeight * $this->sa;
                            }
                        }
                    }
                }

                $tTargetX = 0;
                $tTargetY = 0;
                $tTargetW = $uWidth;
                $tTargetH = $uHeight;
                break;
            case 'crop':
                $tSourceX = ($this->sw - $uWidth) / 2;
                if ($tSourceX < 0) {
                    $tSourceX = 0;
                }

                $tSourceY = ($this->sh - $uHeight) / 2;
                if ($tSourceY < 0) {
                    $tSourceY = 0;
                }

                $tSourceW = $this->sw;
                $tSourceH = $this->sh;

                $tTargetX = 0;
                $tTargetY = 0;
                $tTargetW = $this->sw;
                $tTargetH = $this->sh;
                break;
            case 'stretch':
                $tSourceX = 0;
                $tSourceY = 0;
                $tSourceW = $this->sw;
                $tSourceH = $this->sh;

                $tTargetX = 0;
                $tTargetY = 0;
                $tTargetW = $uWidth;
                $tTargetH = $uHeight;
                break;
        }

        switch ($this->mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $tImage = imagecreatetruecolor($uWidth, $uHeight);
                $tBackground = imagecolorallocate($tImage, $this->background[0], $this->background[1], $this->background[2]);
                imagefill($tImage, 0, 0, $tBackground);

                imagecopyresampled($tImage, $this->image, $tTargetX, $tTargetY, $tSourceX, $tSourceY, $tTargetW, $tTargetH, $tSourceW, $tSourceH);
                break;
            case 'image/gif':
                $tImage = imagecreate($uWidth, $uHeight);
                $tBackground = imagecolorallocate($tImage, $this->background[0], $this->background[1], $this->background[2]);
                imagefill($tImage, 0, 0, $tBackground);

                imagecopyresampled($tImage, $this->image, $tTargetX, $tTargetY, $tSourceX, $tSourceY, $tTargetW, $tTargetH, $tSourceW, $tSourceH);
                break;
            case 'image/png':
                $tImage = imagecreatetruecolor($uWidth, $uHeight);
                $tBackground = imagecolorallocatealpha($tImage, $this->background[0], $this->background[1], $this->background[2], $this->background[3]);
                imagefill($tImage, 0, 0, $tBackground);

                imagealphablending($tImage, true);
                imagesavealpha($tImage, true);
                imagecopyresampled($tImage, $this->image, $tTargetX, $tTargetY, $tSourceX, $tSourceY, $tTargetW, $tTargetH, $tSourceW, $tSourceH);
                break;
        }

        if (!is_null($this->image)) {
            imagedestroy($this->image);
        }

        $this->image = $tImage;
        // $this->size = filesize($this->source);

        $this->sw = $uWidth;
        $this->sh = $uHeight;
        $this->sa = $tAspectRatio;

        return $this;
    }

    /**
     * @ignore
     */
    public function save($uPath = null)
    {
        if (!is_null($uPath)) {
            $this->source = $uPath;
        }

        switch ($this->mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($this->image, $this->source);
                break;
            case 'image/gif':
                imagegif($this->image, $this->source);
                break;
            case 'image/png':
                imagepng($this->image, $this->source);
                break;
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function output()
    {
        Response::sendHeaderCache(-1);
        header('Content-Type: ' . $this->mime, true);
        header('Content-Length: ' . $this->size, true);
        header('Content-Disposition: inline;filename=' . $this->filename . '.' . $this->extension, true);
        // @readfile($this->source);

        switch ($this->mime) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($this->image);
            break;
        case 'image/gif':
            imagegif ($this->image);
            break;
        case 'image/png':
            imagepng($this->image);
            break;
        }

        return $this;
    }
}
