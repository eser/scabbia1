<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Media;

use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Media\Media;
use Scabbia\Extensions\Mime\Mime;

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
    public $sw;
    /**
     * @ignore
     */
    public $sh;
    /**
     * @ignore
     */
    public $sa;
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

        if ($this->source === null) {
            $this->sa = 1;
        } else {
            $tData = getimagesize($this->source);
            $this->sw = $tData[0];
            $this->sh = $tData[1];
            $this->sa = $this->sw / $this->sh;

            // get the source file extension
            if ($uOriginalFilename === null) {
                $uOriginalFilename = $this->source;
            }
            $this->filename = pathinfo($this->source, PATHINFO_FILENAME);
            $this->extension = pathinfo($uOriginalFilename, PATHINFO_EXTENSION);

            $this->mime = Mime::getType($this->extension);
            $this->size = filesize($this->source);

            // calculate a hash - used for cache files, etc
            $this->hash = Media::calculateHash($this->filename, $this->sw, $this->sh) . '.' . $this->extension;

            if ($this->extension === 'jpeg' || $this->extension === 'jpe' || $this->extension === 'jpg') {
                $this->image = imagecreatefromjpeg($this->source);
            } elseif ($this->extension === 'gif') {
                $this->image = imagecreatefromgif($this->source);
            } elseif ($this->extension === 'png') {
                $this->image = imagecreatefrompng($this->source);
                imagealphablending($this->image, true);
                imagesavealpha($this->image, true);
            }
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        if ($this->image !== null) {
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
    public function resize($uWidth, $uHeight, $uMode = 'fit')
    {
        $tAspectRatio = $uWidth / $uHeight;

        if ($uMode === 'fit') {
            $tSourceX = 0;
            $tSourceY = 0;
            $tSourceW = $this->sw;
            $tSourceH = $this->sh;

            if ($uWidth === null && $uHeight !== null) {
                $uWidth = ceil($uHeight * $this->sa);
            } else {
                if ($uWidth !== null && $uHeight === null) {
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
        } elseif ($uMode === 'crop') {
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
        } elseif ($uMode === 'stretch') {
            $tSourceX = 0;
            $tSourceY = 0;
            $tSourceW = $this->sw;
            $tSourceH = $this->sh;

            $tTargetX = 0;
            $tTargetY = 0;
            $tTargetW = $uWidth;
            $tTargetH = $uHeight;
        }

        if ($this->mime === 'image/jpeg' || $this->mime === 'image/jpg') {
            $tImage = imagecreatetruecolor($uWidth, $uHeight);
            $tBackground = imagecolorallocate(
                $tImage,
                $this->background[0],
                $this->background[1],
                $this->background[2]
            );
            imagefill($tImage, 0, 0, $tBackground);

            imagecopyresampled(
                $tImage,
                $this->image,
                $tTargetX,
                $tTargetY,
                $tSourceX,
                $tSourceY,
                $tTargetW,
                $tTargetH,
                $tSourceW,
                $tSourceH
            );
        } elseif ($this->mime === 'image/gif') {
            $tImage = imagecreate($uWidth, $uHeight);
            $tBackground = imagecolorallocate(
                $tImage,
                $this->background[0],
                $this->background[1],
                $this->background[2]
            );
            imagefill($tImage, 0, 0, $tBackground);

            imagecopyresampled(
                $tImage,
                $this->image,
                $tTargetX,
                $tTargetY,
                $tSourceX,
                $tSourceY,
                $tTargetW,
                $tTargetH,
                $tSourceW,
                $tSourceH
            );
        } elseif ($this->mime === 'image/png') {
            $tImage = imagecreatetruecolor($uWidth, $uHeight);
            $tBackground = imagecolorallocatealpha(
                $tImage,
                $this->background[0],
                $this->background[1],
                $this->background[2],
                $this->background[3]
            );
            imagefill($tImage, 0, 0, $tBackground);

            imagealphablending($tImage, true);
            imagesavealpha($tImage, true);
            imagecopyresampled(
                $tImage,
                $this->image,
                $tTargetX,
                $tTargetY,
                $tSourceX,
                $tSourceY,
                $tTargetW,
                $tTargetH,
                $tSourceW,
                $tSourceH
            );
        }

        if ($this->image !== null) {
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
        if ($uPath !== null) {
            $this->source = $uPath;
        }

        if ($this->mime === 'image/jpeg' || $this->mime === 'image/jpg') {
            imagejpeg($this->image, $this->source);
        } elseif ($this->mime === 'image/gif') {
            imagegif($this->image, $this->source);
        } elseif ($this->mime === 'image/png') {
            imagepng($this->image, $this->source);
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function output()
    {
        Http::sendHeaderCache(-1);
        header('Content-Type: ' . $this->mime, true);
        header('Content-Length: ' . $this->size, true);
        header('Content-Disposition: inline;filename=' . $this->filename . '.' . $this->extension, true);
        // @readfile($this->source);

        if ($this->mime === 'image/jpeg' || $this->mime === 'image/jpg') {
            imagejpeg($this->image);
        } elseif ($this->mime === 'image/gif') {
            imagegif($this->image);
        } elseif ($this->mime === 'image/png') {
            imagepng($this->image);
        }

        return $this;
    }
}
