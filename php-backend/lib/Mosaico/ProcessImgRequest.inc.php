<?php   
/**
 * @desc Class for process Images request
 *
 * @class Mosaico_ProcessImgRequest
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ProcessImgRequest
{
    /**
     * handler for img requests
     * @access public static
     */
    public static function get()
    {
        if (filter_has_var(INPUT_GET, 'method') && filter_has_var(INPUT_GET, 'params')) {
            $method = $_GET['method'];            
            $params = explode(',', $_GET['params']);
            
            if (!is_array($params)) {
                Mosaico_ServerError::set(500);
                return;
            }
            
            $width = (int) $params[0];
            $height = (int) $params[1];

            switch ($method) {
                case 'placeholder':
                    $image = new Imagick();

                    $image->newImage($width, $height, '#707070');
                    $image->setImageFormat('png');

                    $x = 0;
                    $y = 0;
                    $size = 40;

                    $draw = new ImagickDraw();

                    while ($y < $height) {
                        $draw->setFillColor('#808080');

                        $points = [
                                ['x' => $x, 'y' => $y],
                                ['x' => $x + $size, 'y' => $y],
                                ['x' => $x + $size * 2, 'y' => $y + $size],
                                ['x' => $x + $size * 2, 'y' => $y + $size * 2]
                       ];

                        $draw->polygon($points);

                        $points = [
                                ['x' => $x, 'y' => $y + $size],
                                ['x' => $x + $size, 'y' => $y + $size * 2],
                                ['x' => $x, 'y' => $y + $size * 2]
                       ];

                        $draw->polygon($points);

                        $x += $size * 2;

                        if ($x > $width) {
                            $x = 0;
                            $y += $size * 2;
                        }
                    }

                    $draw->setFillColor('#B0B0B0');
                    $draw->setFontSize($width / 5);
                    $draw->setFontWeight(800);
                    $draw->setGravity(Imagick::GRAVITY_CENTER);
                    $draw->annotation(0, 0, $width . ' x ' . $height);

                    $image->drawImage($draw);

                    header('Content-type: image/png');
                    
                    Mosaico_ServerResponseOk::set();
                    
                    echo $image;
                break;
                case 'resize':
                case 'cover':
                    if (filter_has_var(INPUT_GET, 'src'))
                    $file_name = $_GET['src'];

                    $path_parts = pathinfo($file_name);

                    switch ($path_parts["extension"]) {
                        case 'png':
                            $mime_type = 'image/png';
                        break;

                        case 'gif':
                            $mime_type = 'image/gif';
                        break;

                        default:
                            $mime_type = 'image/jpeg';
                        break;
                    }

                    $file_name = $path_parts['basename'];

                    $image = Mosaico_ResizeImage::exec($file_name, $method, $width, $height);

                    header('Content-type: ' . $mime_type);

                    Mosaico_ServerResponseOk::set();
                    
                    echo $image;
                break;
                default:
                    Mosaico_ServerError::set(400);
            }
        }
        else {
            Mosaico_ServerError::set(400);
        }
    }
}