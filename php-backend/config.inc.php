<?php      
/**
* config.inc.php (Configuration for Mosaico - Responsive Email Template Editor)
*/

/*
|--------------------------------------------------------------------------
| USE THIS FUNCTION FOR PROTECT YOUR IMAGE UPLOAD SCRIPT
|--------------------------------------------------------------------------
*/
function WY_Protect() 
{      
   if (!isset($_SESSION['your_user_id_logged_in'])) {
       trigger_error('Invalid user try to access the images manager system !!', E_USER_WARNING);
       exit;
   }
}
/*
|--------------------------------------------------------------------------
| ACTIVATE THE FUNCTION ABOVE
|--------------------------------------------------------------------------
*/
//WY_Protect();

$config = array(
    /*
    |--------------------------------------------------------------------------
    | Mosaico lib version
    |--------------------------------------------------------------------------
    */
    'MOSAICO_LIB_VERSION'=> '0.18.6',  
    /*
    |--------------------------------------------------------------------------
    | Base Url for Mosaico main folder with final /
    |--------------------------------------------------------------------------
    */
    'BASE_URL' => 'http://localhost/newsletter/',
    /*
    |--------------------------------------------------------------------------
    | Base path for Mosaico main folder with final /
    |--------------------------------------------------------------------------
    */
    'BASE_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/newsletter/',
    /*
    |--------------------------------------------------------------------------
    | Url where the Mosaico server backend folder is located with final /
    |--------------------------------------------------------------------------
    */
    'PHP_SERVER_URL' => 'http://localhost/newsletter/backend-php/',
    /*
    |--------------------------------------------------------------------------
    | Url for the folder of the newsletters public images with final /
    |--------------------------------------------------------------------------
    */
    'SERVE_IMG_URL' => 'http://localhost/newsletter/media/',
    /*
    |--------------------------------------------------------------------------
    | Path for the folder of the newsletters public images with final /
    |--------------------------------------------------------------------------
    */
    'SERVE_IMG_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/newsletter/media/',
    /*
    |---------------------------------------------------------------------------------------
    | Path for mosaico library directory (relative to BASE_URL and BASE_PATH) with final /
    |---------------------------------------------------------------------------------------
    */
    'LIBRARY_DIR'=> 'dist/rs/',
    /*
    |-------------------------------------------------------------------------------
    | Resources images directory (relative to BASE_URL and BASE_PATH) with final /
    |-------------------------------------------------------------------------------
    */
    'IMAGES_DIR'=>'dist/rs/img/',
    /*
    |----------------------------------------------------------------------------------
    | Resources languages directory (relative to BASE_URL and BASE_PATH) with final /
    |----------------------------------------------------------------------------------
    */
    'LANG_DIR'=>'dist/rs/lang/',
    /*
    |--------------------------------------------------------------------------
    | Flag for activate the save templates function
    |--------------------------------------------------------------------------
    */
    'USE_SAVE_TEMPLATES' => true,    
    /*
    |--------------------------------------------------------------------------
    | Flag for activate the download html email function
    |--------------------------------------------------------------------------
    */
    'USE_DOWNLOAD_HTML' => true,      
    /*
    |------------------------------------------------------------------------------------------
    | Flag for activate the custom button function in the editor.php page
    | This call the class /php-backend/lib/Mosaico/ProcessSendRequest.inc.php 
    | That is a base class that need to be implemented according to the needs of your program
    |------------------------------------------------------------------------------------------
    */
    'USE_CUSTOM_BUTTON' => true, 
    /*
    |--------------------------------------------------------------------------
    | Full url where the main program can send the edited email
    |--------------------------------------------------------------------------
    */
    'PHP_SENDEMAIL_URL' => 'http://localhost/cataloga-arte/cat-admin/cat-newsletter-send.php',
    /*
    |--------------------------------------------------------------------------
    | Used language for editor.php and index.php
    |--------------------------------------------------------------------------
    */
    'USED_LANG'=>'en',
    /*
    |-------------------------------------------------------------------------------------
    | Used language file full filename for json translation in the "LANG_DIR" directory
    | (if set empty, mosaico intreface, use the default language)
    |-------------------------------------------------------------------------------------
    */
    'LANG_FILE'=>'',
    /*
    |-------------------------------------------------------------------------------------------------------
    | Local file system path to the static images folder (relative to BASE_URL and BASE_PATH) with final /
    |-------------------------------------------------------------------------------------------------------
    */
    'STATIC_DIR' => 'static/',
    /*
    |-------------------------------------------------------------------------------------------------------
    | Local file system path to the images upload folder (relative to BASE_URL and BASE_PATH) with final /
    |-------------------------------------------------------------------------------------------------------
    */
    'UPLOADS_DIR' => 'uploads/',
    /*
    |-----------------------------------------------------------------------------------------------------------------
    | Local file system path to the thumbnail images upload folder (relative to BASE_URL and BASE_PATH) with final /
    |-----------------------------------------------------------------------------------------------------------------
    */
    'THUMBNAILS_DIR' => 'uploads/thumb/',
    /*
    |--------------------------------------------------------------------------
    | Width and Height of generated thumbnails
    |--------------------------------------------------------------------------
    */
    'THUMBNAIL_WIDTH' => 90,
    'THUMBNAIL_HEIGHT' => 90,
    /*
    |--------------------------------------------------------------------------
    | Default template dir (relative to BASE_URL and BASE_PATH) without final /
    |--------------------------------------------------------------------------
    */
    'DEF_TEMPLATE_BASEDIR'=> 'versafix-1.it',  
    /*
    |-------------------------------------------------------------------------------------------------
    | Field name for user_id that we get from $_SESSION in the save templates function
    | If we set to 0, system set the user_id to 0 in the Mosaico template database
    | Setting: 
    |         Set in your script the var $_SESSION['your_user_id_logged_in'] with the actual user_id
    |         'SESSION_USER_ID' => (isset($_SESSION['your_user_id_logged_in']) ? $_SESSION['your_user_id_logged_in'] : 0), 
    |         OR The script set in the database the user_id => 0 (Only one user for all templates)
    |         'SESSION_USER_ID' => 0,    
    | Use in the script:     $user_id = $config['SESSION_USER_ID'];
    |-------------------------------------------------------------------------------------------------
    */
    'SESSION_USER_ID' => (isset($_SESSION['your_user_id_logged_in']) ? $_SESSION['your_user_id_logged_in'] : 0),  
    /*
    |--------------------------------------------------------------------------
    | Database configuration
    | Used only if USE_SAVE_TEMPLATES is set to true
    |--------------------------------------------------------------------------
    */
    'DB_USE_UTF_8'=>true,
    'DB' => 'malvanomarchesini',
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_TABLE' => 'cat_mosaico_tpl',
    'DB_DATE_FORMAT' => '%d/%m/%Y %h:%i %p' // Mysql date format use by the query for extract templates DATE_FORMAT(date,'%d/%m/%Y  %h:%i %p') AS 
);