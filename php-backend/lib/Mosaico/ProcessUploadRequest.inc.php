<?php
/**
 * @desc Class for process Images upload request
 *
 * @class Mosaico_ProcessUploadRequest
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ProcessUploadRequest
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
                        Mosaico_ServerError::set(500);
                        return;
                    }
                }
                else {
                    Mosaico_ServerError::set(400);
                    return;
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Connection: close');
        
        Mosaico_ServerResponseOk::set();
        
        echo json_encode(array('files' => $files));
    }
}