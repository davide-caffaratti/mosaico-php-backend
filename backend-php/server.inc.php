<?php
require (dirname(__FILE__).'/config.inc.php');
/**
 * @desc Class for manage php backend for mosaico
 *
 * @class Mosaico_Server
 * @package Mosaico_Backend
 * @use Mosaico_Process_Upload_Request, Mosaico_Process_Img_Request, Mosaico_Process_Dl_Request, Mosaico_Resize_Image, Mosaico_Check_Email, Mosaico_Server_Error
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Server
{        
    /**
     * Public constructor
     * @access public static
     * @param $dir    The current directory to process
     */
    public static function get($dir)
    {
        global $config;
        
        switch($dir) {
            case 'uploads':
                Mosaico_Process_Upload_Request::get();
            break;
            case 'img':
                Mosaico_Process_Img_Request::get();
            break;
            case 'dl':
                Mosaico_Process_Dl_Request::get();
            break;
            default:
                Mosaico_Server_Error::set(500);
        }        
    }
}   
/**
 * @desc Class for process Images upload request
 *
 * @class Mosaico_Process_Upload_Request
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Process_Upload_Request
{
    /**
    * handler for upload requests
     * @access public static
     */
    public static function get()
    {
        global $config;

        $files = array();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $dir = scandir($config['BASE_PATH'] . $config['UPLOADS_DIR']);
            foreach ($dir as $file_name) {
                $file_path = $config['BASE_PATH'] . $config['UPLOADS_DIR'] . $file_name;
                if (is_file($file_path) && $file_name !== 'index.php') {
                    $size = filesize($file_path);

                    $file = [
                        'name' => $file_name,
                        'url' => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
                        'size' => $size
                   ];

                    if (file_exists($config['BASE_PATH'] . $config['THUMBNAILS_DIR'] . $file_name)) {
                        $file['thumbnailUrl'] = $config['BASE_URL'] . $config['THUMBNAILS_DIR'] . $file_name;
                    } 

                    $files[] = $file;
                }
            }
        }
        else if (!empty($_FILES)) {
            foreach ($_FILES['files']['error'] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['files']['tmp_name'][$key];

                    $file_name = $_FILES['files']['name'][$key];

                    $file_path = $config['BASE_PATH'] . $config['UPLOADS_DIR'] . $file_name;

                    if (move_uploaded_file($tmp_name, $file_path) === true) {
                        $size = filesize($file_path);

                        $image = new Imagick($file_path);

                        $image->resizeImage($config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT'], Imagick::FILTER_LANCZOS, 1.0, true);
                        $image->writeImage($config['BASE_PATH'] . $config['THUMBNAILS_DIR'] . $file_name);
                        $image->destroy();

                        $file = array(
                            'name' => $file_name,
                            'url' => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
                            'size' => $size,
                            'thumbnailUrl' => $config['BASE_URL'] . $config['THUMBNAILS_DIR'] . $file_name
                       );

                        $files[] = $file;
                    }
                    else {
                        Mosaico_Server_Error::set(500);
                        return;
                    }
                }
                else {
                    Mosaico_Server_Error::set(400);
                    return;
                }
            }
        }

        Mosaico_Server_Response_Ok::set();

        header('Content-Type: application/json; charset=utf-8');
        header('Connection: close');
        
        echo json_encode(array('files' => $files));
    }
}    
/**
 * @desc Class for process Images request
 *
 * @class Mosaico_Process_Img_Request
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Process_Img_Request
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
                Mosaico_Server_Error::set(500);
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
                    
                    Mosaico_Server_Response_Ok::set();
                    
                    echo $image;
                break;
                case 'resize':
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

                    $image = Mosaico_Resize_Image::exec($file_name, $method, $width, $height);

                    header('Content-type: ' . $mime_type);

                    Mosaico_Server_Response_Ok::set();
                    
                    echo $image;
                break;
                default:
                    Mosaico_Server_Error::set(400);
            }
        }
        else {
            Mosaico_Server_Error::set(400);
        }
    }
}
/**
 * @desc Class for process dl request
 *
 * @class Mosaico_Process_Dl_Request
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Process_Dl_Request
{
    /**
     * handler for dl requests
     * @access public static
     */
    public static function get()
    {
        global $config;

        if (filter_has_var(INPUT_POST, 'action') && filter_has_var(INPUT_POST, 'html') && !empty($_POST['html'])) {
            $html = $_POST['html'];
            $action = $_POST['action'];

            /* create static versions of resized images */
            $matches = [];

            $num_full_pattern_matches = preg_match_all('#<img.*?src=".*(img[^"]*)#i', $html, $matches); 


            for ($i = 0; $i < $num_full_pattern_matches; $i++) {

                if (stripos($matches[1][$i], 'img/?src=') !== FALSE) {

                    $src_matches = [];

                    if (preg_match('#.*src=(.*)&amp;method=(.*)&amp;params=(.*)#i', $matches[1][$i], $src_matches) !== FALSE) {

                        $file_name = urldecode($src_matches[1]);
                        $file_name = substr($file_name, strlen($config['BASE_URL'] . $config['UPLOADS_DIR']));

                        $method = urldecode($src_matches[2]);

                        $params = urldecode($src_matches[3]);
                        $params = explode(",", $params);
                        $width = (int) $params[0];
                        $height = (int) $params[1];

                        if ($width == 0 || $height == 0) {
                            $image = new Imagick($config['BASE_PATH'] . $config['UPLOADS_DIR'] . $file_name);
                            $image_geometry = $image->getImageGeometry();
                            $image_ratio =  (double) $image_geometry['width'] / $image_geometry['height'];
                            if ($width == 0) {
                                $width =  $height * $image_ratio;
                                $width = (int) $width;
                            } 
                            else {
                                $height = $width / $image_ratio;
                                $height = (int) $height;
                            }
                        }


                        $static_file_name = $method . '_' . $width . 'x' . $height . '_' . $file_name;


                        $html = str_ireplace( $config['BASE_URL'] . $matches[1][$i], $config['SERVE_URL'] . $config['STATIC_DIR'] . urlencode($static_file_name), $html);

                        $image = Mosaico_Resize_Image::exec($file_name, $method, $width, $height);
                        $image->writeImage($config['SERVE_PATH'] . $config['STATIC_DIR'] . $static_file_name);
                    }

                }
            }

            /* perform the requested action */
            switch ($action){
                case 'download':
                    if (filter_has_var(INPUT_POST, 'filename')) {
                        header('Content-Type: application/force-download');
                        header('Content-Disposition: attachment; filename=\"' . $_POST['filename'] . "\"");
                        header('Content-Length: ' . strlen($html));

                        echo $html;
                    }
                    else {
                        Mosaico_Server_Error::set(400, 'Missing parameter for download the compiled email!!');
                    }
                break;            
                case 'email':
                    if (filter_has_var(INPUT_POST, 'rcpt') && Mosaico_Valid_Email::check($_POST['rcpt']) && filter_has_var(INPUT_POST, 'subject')) {
                        $to = $_POST['rcpt'];
                        $subject = (empty($_POST['subject']) ? '[test email]' : $_POST['subject']);

                        $headers = array();

                        $headers[] = 'MIME-Version: 1.0';
                        $headers[] = 'Content-type: text/html; charset=utf-8';
                        $headers[] = 'To: '.$to;
                        $headers[] = 'Subject: '.$subject;

                        $headers = implode("\r\n", $headers);

                        if (mail($to, $subject, $html, $headers) === false) {
                            Mosaico_Server_Error::set(500);
                            return;
                        }
                        else {
                            Mosaico_Server_Response_Ok::set();
                        }
                    }
                    else {
                        Mosaico_Server_Error::set(400);
                    }
                break;
                default:                    
                    Mosaico_Server_Error::set(400);
            }
        }
        else { 
            Mosaico_Server_Error::set(400, 'Missing parameter for elaborate current request!!');
        }
    }
}
/**
 * @desc Class for resize images
 *
 * @class Mosaico_Resize_Image
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Resize_Image
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
/**
 * @desc Class for return server error
 *
 * @class Mosaico_Server_Response_Ok
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Server_Response_Ok
{    
    /**
     * Set server response_code and error message if present
     * @access public
     * @return php header 200
     */
    public static function set()
    {
        http_response_code(200);
    }
}
/**
 * @desc Class for return server error
 *
 * @class Mosaico_Server_Error
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Server_Error
{    
    /**
     * Set server response_code and error message if present
     * @access public
     * @param string  $msg If present, contain the error message string
     * @return php header with $response_code and string with error writing inside if present
     */
    public static function set($response_code=500, $msg='')
    {
        http_response_code($response_code);
        if (!empty($msg)) {
            self::html($msg);
        }
    }
    /**
     * Return basic html page with the error message using bootstrap for render better html message
     * @access private
     * @param string $msg  Text of the error to present
     * @return string
     */
    private static function html($msg)
    {
        echo "<!doctype html>\n".
        "<html lang=\"en\">".
        "<head>\n".
        "<meta charset=\"utf-8\">\n".
        "<meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">\n".
        "<meta name=\"robots\" content=\"noindex, nofollow\">\n".
        "<title>Mosaico Server Error</title>\n".
        "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC\" crossorigin=\"anonymous\">\n".
        "</head>\n".
        "<body>\n".
        "<div id=\"page-container\">\n".
        "<main id=\"container\" class=\"ms-3 me-3 mb-3 p-3\">\n".
        "<div class=\"alert alert-danger align-items-baseline border-secondary rounded-3\" role=\"alert\">\n".
        "<h1 class=\"border-bottom border-danger w-100 text-danger p-2\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"30\" height=\"30\" class=\"me-3 mb-1\" fill=\"currentColor\" class=\"bi bi-exclamation-triangle-fill flex-shrink-0 me-2\" viewBox=\"0 0 16 16\" role=\"img\" aria-label=\"Warning:\">\n<path d=\"M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z\"/></svg>Mosaico Server Error</h1>\n".
        "<div>\n".
        $msg.
        "</div>\n".
        "</div>\n".
        "<main>\n".
        "<div>\n".
        "</body>\n".
        "</html>";
    }
}
/**
 * @desc Class for Check email formt (basic check)
 *
 * @class Mosaico_Valid_Email
 * @package Mosaico_Backend
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Valid_Email
{
    /**
     * @desc Check email
     * @access public static
     * @param string $val
     * @return bolean
     */
    public static function check($email)
    {
        if (empty($email)) {
            return false;
        }
        return (!filter_var($email, FILTER_VALIDATE_EMAIL) === false);
    }
}
