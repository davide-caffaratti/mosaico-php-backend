<?php
/**
 * @desc Class for use custom button functions
 *
 * @class Mosaico_ProcessSendRequest
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ProcessSendRequest
{    
    /**
     * Insert/Update email datas 
     * @access public static
     */
    public static function get()
    {
        global $config, $lang;
        
        // Check if we use save tpl function
        if (!$config['USE_CUSTOM_BUTTON']) {
            Mosaico_ServerError::set(400, 'Custom button is disabled, check your config file for correct the value', true);
        }
        else { 
            // Check posted hash
            if (!filter_has_var(INPUT_POST, 'hash')) {
                Mosaico_ServerError::set(400, 'Missing hash value', true);
            }
            // Check empty posted hash
            else if (empty($_POST['hash'])) {
                Mosaico_ServerError::set(400, 'Empty hash value', true);
            }              
            // Check $_POST datas
            else if (!filter_has_var(INPUT_POST, 'metadata') || !filter_has_var(INPUT_POST, 'content') || !filter_has_var(INPUT_POST, 'html')) {
                Mosaico_ServerError::set(400, 'missing datas', true);
            }
            else {
                $hash = (string)$_POST['hash'];
                $name = (filter_has_var(INPUT_POST, 'name') ? (string)$_POST['name'] : $lang['NEW_TPL_DEF_NAME']);
                $basename = (filter_has_var(INPUT_POST, 'basename')) ? (string)$_POST['basename'] : $config['DEF_TEMPLATE_BASEDIR'];
                $metadata = $_POST['metadata'];
                $content = $_POST['content'];
                $html = $_POST['html'];
            }         
            // Autoload the db class
            $db = new Db();

            // Save the template datas in db
            $is_update = false;
            $sql = "SELECT `tpl_hash` FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."' LIMIT 1";
            if ($db->get_var($sql)) {
                $is_update = true;
                $sql = " UPDATE `".$config['DB_TABLE']."` SET".
                       " `tpl_basename` = '".$db->escape($basename)."',".
                       " `tpl_name` = '".$db->escape($name)."',".
                       " `tpl_metadata` = '".$db->escape($metadata)."',".
                       " `tpl_content` = '".$db->escape($content)."',".
                       " `tpl_html` = '".$db->escape($html)."',".
                       " `tpl_lastchange` = NOW()".
                       " WHERE `tpl_hash` = '".$db->escape($hash)."'".
                       " AND `user_id` = '".(int)$config['SESSION_USER_ID']."' LIMIT 1";
            } 
            else {
                $sql = " INSERT INTO `".$config['DB_TABLE']."`".
                       " (`tpl_hash`, `user_id`, `tpl_basename`, `tpl_name`, `tpl_metadata`, `tpl_content`, `tpl_html`, `tpl_lastchange`)".
                       " VALUES".
                       " ('".$db->escape($hash)."','".(int)$config['SESSION_USER_ID']."','".$db->escape($basename)."','".$db->escape($name)."',".
                       " '".$db->escape($metadata)."','".$db->escape($content)."','".$db->escape($html)."', NOW())";
            }
            Mosaico_ServerResponseOk::set();
            echo "<div style=\"margin:0 auto;text-align:center;display:block;width:700px;\">\n".
                 "    <div style=\"margin-bottom:20px;border:1px dashed #ffffff;padding:10px;text-align:center;color:#ffffff;\">\n".
                 "        <button id=\"email-back\" class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary\" type=\"button\"><span class=\"ui-button-icon-primary ui-icon fa fa-fw fa-arrow-left\"></span><span class=\"ui-button-text\">".$lang['BTN_CUSTOM_ACTION_BACK_EDITOR']."</span></button>\n".
                 //"        <a href=\"\">\n".
                 "            <button id=\"email-send\" class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary\" type=\"button\"><span class=\"ui-button-icon-primary ui-icon fa fa-fw fa-envelope\"></span><span class=\"ui-button-text\">".$lang['BTN_CUSTOM_ACTION_SEND']."</span></button>\n".
                 //"        </a>\n".
                 "    </div>\n".
                 "    <iframe transparency=\"true\" frameborder=\"0\" width=\"700\" height=\"700\" scrolling=\"auto\" style=\"margi:0 auto;border: 0;\" src=\"".$config['PHP_SERVER_URL']."preview/?hash=".$hash."\"></iframe >\n".
                 "</div>\n";
        }
    }
    /**
     * Return basic html page with the error message using bootstrap for render better html message
     * @access public
     * @param string $msg  Text of the error to present
     * @return string
     */
    public static function urlRefresh($msg)
    {
        
    }
}