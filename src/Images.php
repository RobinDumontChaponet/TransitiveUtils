<?php

namespace Transitive\Utils;

class Images2
{
//	finfo::file();

    public static function createFromString(): ?self
    {
        return new self();
    }

    public static function open(): ?self
    {
        return new self();
    }

    public function __construct()
    {
    }
}

// functions from Transit, 2014

abstract class Images
{
	/**
	 * scaleImageRessource function, Transit - Robin Dumont-Chaponet_.
	 *
	 * return scaled resource image (conserve image ratio)
	 *
	 * @param mixed $src
	 * @param int   $max_width  (default: 0 keep original size)
	 * @param int   $max_height (default: 0 keep original size)
	 *
	 * @return scaled image ressource or false if scaling wasn't necessary or couldn't happen
	 */
	public static function scaleImageRessource($src, $max_width = 0, $max_height = 0) {
		$width = imagesx($src);
		$height = imagesy($src);

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if(0 == $max_width || 0 == $max_height || ($width <= $max_width && $height <= $max_height)) {
			$tn_width = $width;
			$tn_height = $height;
		} elseif (($x_ratio * $height) < $max_height) {
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
		} else{
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
		}

		$tmp = imagecreatetruecolor($tn_width, $tn_height);

		if(imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height))
			return $tmp;
		else
			return false;
	}

	/**
	 * saveScaledImageRessourceToFile function, Transit - Robin Dumont-Chaponet_.
	 *
	 * Scale an image ressource and save it to disk (conserve image ratio)
	 *
	 * @param ressource $src            : image ressource
	 * @param string    $pathToNewImage
	 * @param int       $max_width      (default: 0 keep original size)
	 * @param int       $max_height     (default: 0 keep original size)
	 * @param string    $type           (default: 'jpeg')
	 *
	 * @return 0 when saving fails, 1 when saving at original size or 2 when saving scaled image
	 */
	public static function saveScaledImageRessourceToFile($src, $pathToNewImage, $max_width = 0, $max_height = 0, $type = 'jpeg', $quality = 75) {
		$tmp = self::scaleImageRessource($src, $max_width, $max_width);

		if(false === $tmp) {
			$tmp = $src;
			$scalling = false;
		} else
			$scalling = true;

		switch ($type) {
			case 'gif':
				$pathToNewImage .= '.gif';
				imagegif($tmp, $pathToNewImage);
			break;
			case 'jpeg': case 'jpg':
				$pathToNewImage .= '.jpg';
				imagejpeg($tmp, $pathToNewImage, $quality);
			break;
			case 'png':
				$pathToNewImage .= '.png';
				imagepng($tmp, $pathToNewImage);
			break;
			default:
				echo '';
			break;
		}

		imagedestroy($tmp);

		if(file_exists($pathToNewImage))
			return ($scalling) ? 2 : 1;
		else
			return 0;
	}

	/**
	 * saveScaledImageRessourceToFile function, Transit - Robin Dumont-Chaponet_.
	 *
	 * return a new scaled image (conserve image ratio)
	 * or original if no scaling
	 *
	 * @param ressource $src        : image ressource
	 * @param int       $max_width  (default: 0 keep original size)
	 * @param int       $max_height (default: 0 keep original size)
	 * @param string    $type       (default: 'jpeg')
	 *
	 * @return scaled image or original if no scaling necessary
	 */
	public static function scaledImageRessource2Image($src, $max_width = 0, $max_height = 0, $type = 'jpeg', $quality = 75) {
		$tmp = self::scaleImageRessource($src, $max_width, $max_width);

		if(false === $tmp) {
			$tmp = $src;
			$scalling = false;
		} else
			$scalling = true;

		ob_start();
		switch ($type) {
			case 'gif':
				imagegif($tmp);
			break;
			case 'jpeg': case 'jpg':
				imagejpeg($tmp, null, $quality);
			break;
			case 'png':
				imagepng($tmp);
			break;
			default:
				echo '';
			break;
		}
		$buffer = ob_get_contents();

		ob_end_clean();
		imagedestroy($tmp);

		return $buffer;
	}

	/**
	 * saveScaledImageRessourceToFile function, Transit - Robin Dumont-Chaponet_.
	 *
	 * return dominant color as color index ['red', 'green', 'blue', 'alpha']
	 *
	 * @param ressource $src : image ressource
	 *
	 * @return ['red', 'green', 'blue', 'alpha']
	 */
	public static function getDominantColor($src)
	{
		$width = imagesx($src);
		$height = imagesy($src);
		$pixel = imagecreatetruecolor(1, 1);
		imagecopyresampled($pixel, $src, 0, 0, 0, 0, 1, 1, $width, $height);
		$rgb = imagecolorat($pixel, 0, 0);
		$color = imagecolorsforindex($pixel, $rgb); //you are getting the most common colors in the image

		imagedestroy($pixel);
		return $color;
	}
}
