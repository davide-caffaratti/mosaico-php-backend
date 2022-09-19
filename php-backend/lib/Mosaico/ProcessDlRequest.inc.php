<?php
/**
 * @desc Class for process dl request
 *
 * @class Mosaico_ProcessDlRequest
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ProcessDlRequest
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


                        $html = str_ireplace( $config['BASE_URL'] . $matches[1][$i], $config['SERVE_IMG_URL'] . $config['STATIC_DIR'] . urlencode($static_file_name), $html);

                        $image = ResizeImage::exec($file_name, $method, $width, $height);
                        $image->writeImage($config['SERVE_IMG_PATH'] . $config['STATIC_DIR'] . $static_file_name);
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
                        Mosaico_ServerError::set(400, 'Missing parameter for download the compiled email!!');
                    }
                break;            
                case 'email':
                    if (filter_has_var(INPUT_POST, 'rcpt') && ValidEmail::check($_POST['rcpt']) && filter_has_var(INPUT_POST, 'subject')) {
                        $to = $_POST['rcpt'];
                        $subject = (empty($_POST['subject']) ? '[test email]' : $_POST['subject']);

                        $headers = array();

                        $headers[] = 'MIME-Version: 1.0';
                        $headers[] = 'Content-type: text/html; charset=utf-8';
                        $headers[] = 'To: '.$to;
                        $headers[] = 'Subject: '.$subject;

                        $headers = implode("\r\n", $headers);

                        if (mail($to, $subject, $html, $headers) === false) {
                            Mosaico_ServerError::set(500);
                            return;
                        }
                        else {
                            Mosaico_ServerResponseOk::set();
                        }
                    }
                    else {
                        Mosaico_ServerError::set(400);
                    }
                break;
                default:                    
                    Mosaico_ServerError::set(400);
            }
        }
        else { 
            Mosaico_ServerError::set(400, 'Missing parameter for elaborate current request!!');
        }
    }
}
