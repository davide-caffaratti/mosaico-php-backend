<?php
/**
 * @desc Main class for manage Mosaico php backend
 *
 * @class Mosaico_Server
 * @package Mosaico
 * @use Mosaico_ProcessUploadRequest, Mosaico_ProcessImgRequest, Mosaico_ProcessDlRequest, Mosaico_ProcessTplRequest, Mosaico_ResizeImage, Mosaico_CheckEmail, Mosaico_ServerError
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_Server
{        
    /**
     * Public constructor
     * @access public static
     */
    public static function get()
    {
        global $config;
        
        $url = parse_url($_SERVER['REQUEST_URI']);
        $dir = substr($url['path'], strlen(dirname($url['path'])));
        
        switch($dir) {
            case '/uploads/':
                Mosaico_ProcessUploadRequest::get();
            break;
            case '/img/':
                Mosaico_ProcessImgRequest::get();
            break;
            case '/dl/':
                Mosaico_ProcessDlRequest::get();
            break;
            case '/tpl/':
                Mosaico_ProcessTplRequest::get();
            break;
            case '/send/':
                Mosaico_ProcessSendRequest::get();
            break;
            case '/preview/':
                Mosaico_PreviewHtmlEmail::get();
            break;
            default:
                Mosaico_ServerError::set(404);
        }        
    }
}