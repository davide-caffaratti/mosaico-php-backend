<?php
/**
 * @desc Class for return the html email for a template
 *
 * @class Mosaico_PreviewHtmlEmail
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_PreviewHtmlEmail
{    
    /**
     * Set server response_code and error message if present
     * @access public
     * @param string  $hash Contain the hash of the email. If set to false search it in $_POST and $_GET vars the hash value
     * @return json
     */
    public static function get($hash=false)
    {
        global $config, $lang;
        
        // Check if have $_GET hash
        if (!$hash && filter_has_var(INPUT_GET, 'hash') && !empty($_GET['hash'])) { 
            $hash = $_GET['hash'];
        }
        // Check if have $_POST hash
        else if (!$hash && filter_has_var(INPUT_POST, 'hash') && !empty($_POST['hash'])) {
            $hash = $_POST['hash'];
        }
        
        // Default searching hash
        if ($hash) {
            // Autoload the db class
            $db = new Db();
            $sql = "SELECT `tpl_html` FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."' LIMIT 1";
            $html = $db->get_var($sql);
            if (!$html) {
                Mosaico_ServerError::set(404, 'Hash value not founded.');
            }
            else {
                Mosaico_ServerResponseOk::set();
                echo stripslashes($html);
            }
        }
        // No hash founded
        else {
            Mosaico_ServerError::set(404, 'Hash value not founded.', true);
        }
    }
}