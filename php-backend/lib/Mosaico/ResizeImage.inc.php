<?php
/**
 * @desc Class for resize images
 *
 * @class Mosaico_ResizeImage
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ResizeImage
{
    /**
     * function to resize images using resize or cover methods
     * @access public static
     * @param string  $file_name 
     * @param string  $method 
     * @param int     $width 
     * @param int     $height 
     * @return        Imagick resource
     */
    public static function exec($file_name, $method, $width, $height)
    {
        global $config;

        $image = new Imagick($config['BASE_PATH'] . $config['UPLOADS_DIR'] . $file_name);

        if ($method == 'resize') {
            $image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1.0);
        }
        // $method == "cover"
        else {
            $image_geometry = $image->getImageGeometry();

            $width_ratio = $image_geometry['width'] / $width;
            $height_ratio = $image_geometry['height'] / $height;

            $resize_width = $width;
            $resize_height = $height;

            if ($width_ratio > $height_ratio) {
                $resize_width = 0;
            }
            else {
                $resize_height = 0;
            }

            $image->resizeImage($resize_width, $resize_height, Imagick::FILTER_LANCZOS, 1.0);

            $image_geometry = $image->getImageGeometry();

            $x = ($image_geometry['width'] - $width) / 2;
            $y = ($image_geometry['height'] - $height) / 2;

            $image->cropImage($width, $height, $x, $y);
        }

        return $image;
    }
}
