<?php

// Eser - Grabbed from http://adamhopkinson.co.uk/classes/resize/

class Resize {

	var $source;						// the uri of the source image
	var $sx, $sy, $sw, $sh, $sa;		// values for the position and size of the source image
	var $tx, $ty, $tw, $th, $ta;		// values for the position and size of the target image
	var $quality;						// the output quality
	var $mode;							// the mode - fit, stretch or crop
	var $errors;						// an array to store error messages
	var $gc_threshold;					// the chance of running garbage collection (from 0 to 1)
	var $caching;						// whether to use caching

	function Resize() {

		define('BASE_PATH', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
		define('BASE_URI', dirname($_SERVER['REQUEST_URI']));

		$this->gc_threshold = 0.1;
		$this->source = (array_key_exists('source', $_GET)) ? $_GET['source'] : null;
		$this->sx = 0;
		$this->sy = 0;
		$this->tx = 0;
		$this->ty = 0;
		$this->tw = (array_key_exists('width', $_GET)) ? $_GET['width'] : null;
		$this->th = (array_key_exists('height', $_GET)) ? $_GET['height'] : null;
		$this->quality = (array_key_exists('quality', $_GET)) ? $_GET['quality'] : 80;
		$this->mode = (array_key_exists('mode', $_GET)) ? $_GET['mode'] : 'fit';
		$this->cache_folder = 'cache';
		$this->cache_age = 15;

		// check that the source file exists
		if(!is_file($this->source)) {
			$this->errors[] = 'File not found: ' . $this->source;
			$this->fail(404, 'The requested image could not be found: ' . $this->source);
			return false;
		}

		// check that the cache folder is writable
		if(!is_writeable($this->cache_folder)) {
			$this->errors[] = 'Caching disabled - the cache folder could not be written. Try chmodding it to 766';
			$this->fail(500, 'The cache folder could not be written');
			return false;
			$this->caching = false;
		} else {
			$this->caching = true;
		}

		// get the source file extension
		$this->extension = pathinfo($this->source, PATHINFO_EXTENSION);

		// calculate a hash - used for cache files, etc
		$this->getHash();

		// set the cache filename using the hash
		$this->cache_fn = $this->cache_folder . '/' . $this->hash . '.' . $this->extension;

		// if the file exists in the cache and is less than cache_age seconds old,
		// don't bother regenerating it
		if(!$this->checkCache()) {
			$this->getFile();
			$this->getDimensions();
			$this->create();
		}

		if($this->gc_threshold > rand(0,1)) {
			$this->garbageCollection();
		}

	}

	function getHash() {
		$this->hash = md5(serialize($this));
	}

	function checkCache() {
		if(file_exists($this->cache_fn)) {
			$age = time() - filectime($this->cache_fn);
			if($age < $this->cache_age) {
				$this->errors[] = 'Using file from cache: age is ' . $age;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getFile() {
		$data = getimagesize($this->source);
		$this->sw = $data[0];
		$this->sh = $data[1];
		$this->sa = $this->sw / $this->sh;
		$this->mime = $data['mime'];
	}

	function getDimensions() {
		switch($this->mode) {
			case 'fit':
				if($this->tw == null && $this->th == null) {
					$this->errors[] = 'Please specify either width or height (or both) when mode is fit';
					return false;
				} elseif ($this->tw == null && $this->th != null) {
					$this->tw = ceil($this->th * $this->sa);
				} elseif ($this->tw != null && $this->th == null) {
					$this->th = ceil($this->tw / $this->sa);
				} elseif ($this->tw != null && $this->th != null) {
					$this->ta = $this->tw / $this->th;
					if($this->sa == $this->ta) {
						// don't do anything - the source and target aspect ratios are the same
					} elseif ($this->sa > $this->ta) {
						$this->th = $this->tw / $this->sa;
					} elseif ($this->sa < $this->ta) {
						$this->tw = $this->th * $this->sa;
					}
				}
			break;
			case 'crop':
				if($this->tw == null && $this->th == null) {
					$this->errors[] = 'Please specify either width or height (or both) when mode is fit';
					return false;
				}
				$this->ta = $this->tw / $this->th;
				if($this->ta >= 1) {
					// fit to width, crop top & bottom
					if($this->sw >= $this->sh) {
						$w = $this->sh * $this->ta;
						$d = $this->sw - $w;
						$this->sx = $d / 2;
						$this->sw = $w;
//						$this->errors[] = 'w is ' . $w;
					} else {
						$h = $this->sw / $this->ta;
						$d = $this->sh - $h;
						$this->sy = $d / 2;
						$this->sh = $h;
					}
				} else {
					// fit to height, crop sides
					$this->tw = $this->th * $this->sa;
				}
			break;
			case 'stretch':
				if($this->tw == null && $this->th == null) {
					$this->errors[] = 'Please specify either width or height (or both) when mode is fit';
					return false;
				}
				$this->ta = $this->tw / $this->th;
			break;
		}
	}

	function create() {
		$canvas = imagecreatetruecolor($this->tw, $this->th);
		switch($this->mime) {
			case 'image/jpeg':
			case 'image/jpg':
				$image = imagecreatefromjpeg($this->source);
				imagecopyresampled($canvas, $image, $this->tx, $this->ty, $this->sx, $this->sy, $this->tw, $this->th, $this->sw, $this->sh);
				imagejpeg($canvas, $this->cache_fn, $this->quality);
			break;
			case 'image/gif':
				$image = imagecreatefromgif($this->source);
				imagecopyresampled($canvas, $image, $this->tx, $this->ty, $this->sx, $this->sy, $this->tw, $this->th, $this->sw, $this->sh);
				imagegif($canvas, $this->cache_fn, $this->quality);
			break;
			case 'image/png':
				$this->quality = floor($this->quality / 10);
				$image = imagecreatefrompng($this->source);
				imagealphablending($canvas, FALSE);
				imagesavealpha($canvas, TRUE);
				imagecopyresampled($canvas, $image, $this->tx, $this->ty, $this->sx, $this->sy, $this->tw, $this->th, $this->sw, $this->sh);
				imagepng($canvas, $this->cache_fn, $this->quality);
			break;
		}
		imagedestroy($canvas);
		imagedestroy($image);
		$this->filesize = filesize($this->cache_fn);
	}

	function output() {
		header('Content-Type: ' . $this->mime);
		header('Content-Length: ' . $this->filesize);
		readfile($this->cache_fn);
	}

	function debug() {
		echo '<pre>';
		print_r($this);
		echo '</pre>';
		echo '<a href="' . $this->cache_fn . '">Open</a>';
	}

	function fail($err, $message) {
		header('HTTP/1.1 ' . $err);
		echo($message);
		die();
	}

	function garbageCollection() {
		$d = dir($this->cache_folder);
		$counter = 0;
		clearstatcache();
		while (false !== ($entry = $d->read())) {
			if(is_file($entry)) {
				$age = time() - filectime($entry);
				if($age > $this->cache_age) {
					unlink($entry);
					$counter++;
				}
			}
		}
		$this->errors[] = 'Removed ' . $counter . ' files from cache';
		$d->close();
	}

}

?>