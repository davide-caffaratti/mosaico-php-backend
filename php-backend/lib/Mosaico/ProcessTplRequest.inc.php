<?php
/**
 * @desc Class for process tpl request
 *
 * @class Mosaico_ProcessTplRequest
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ProcessTplRequest
{
    /**
     * handler for tpl requests
     * @access public static
     * @return mixed php header, json strin and php header
     */
    public static function get()
    {
        global $config, $lang;
        
        // Check posted action
        if (!filter_has_var(INPUT_POST, 'action')) {
            Mosaico_ServerError::set(400, 'Missing action value', true);
        }
        // Check if we use save tpl function
        if (!$config['USE_SAVE_TEMPLATES']) {
            Mosaico_ServerError::set(400, 'Save template is disabled, check your config file for correct the value', true);
        }
        
        switch($_POST['action']) {
            case 'save':
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
                    Mosaico_ServerError::set(400);
                    return;
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
                
                $query = $db->query($sql);
                if ($is_update) {
                    if ($query) {
                        $typeMsg = 'success';
                        $msg = sprintf($lang['RESPONSE_UPD_TPL_OK'], $name);
                    } 
                    else {
                        $typeMsg = 'error';
                        $msg = sprintf($lang['RESPONSE_UPD_TPL_KO'], $name);
                    }
                }
                else {
                    $typeMsg =  'success';
                    $msg = sprintf($lang['RESPONSE_INS_TPL_OK'], $name);
                }
                $responseArray = array('type' => $typeMsg, 'message' => $msg);
                Mosaico_ServerResponseOk::set();
                $encoded = json_encode($responseArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                header('Content-Type: application/json');

                echo $encoded;
            break;
            case 'fetch':
                // Check posted hash
                if (!filter_has_var(INPUT_POST, 'hash')) {
                    Mosaico_ServerError::set(400, 'Missing hash value', true);
                }
                // Check empty posted hash
                else if (empty($_POST['hash'])) {
                    Mosaico_ServerError::set(400, 'Empty hash value', true);
                }
                $hash = (string)$_POST['hash'];
                
                $db = new Db();
                $sql = "SELECT `tpl_metadata` AS `metadata`, `tpl_content` AS `content` FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."'";
                if ($row = $db->get_row($sql, ARRAY_A)) {
                    Mosaico_ServerResponseOk::set();
                    $encoded = json_encode( $row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } 
                else {
                    Mosaico_ServerError::set(404);
                    $encoded = json_encode(array( 'msg' => 'Template with the key / hash ' . $hash . ' not found.' ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                header('Content-Type: application/json');
                echo $encoded;
            break;
            case 'tpl-rename':
                // Check posted hash
                if (!filter_has_var(INPUT_POST, 'hash')) {
                    Mosaico_ServerError::set(404, 'Missing hash value', true);
                }
                // Check empty posted hash
                else if (empty($_POST['hash'])) {
                    Mosaico_ServerError::set(404, 'Empty hash value', true);
                }
                // Check posted new name
                if (!filter_has_var(INPUT_POST, 'name')) {
                    Mosaico_ServerError::set(404, 'Missing new name value', true);
                }
                // Check empty posted new name
                else if (empty($_POST['name'])) {
                    Mosaico_ServerError::set(404, 'Empty new name value', true);
                }
                $hash = (string)$_POST['hash'];
                $rename = (string)$_POST['name'];
                
                $db = new Db();
                $sql = "SELECT `tpl_name` FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."'";
                if (!$hold_name = $db->get_var($sql)) {
                    Mosaico_ServerError::set(404);
                }
                else {
                    $hold_name = stripslashes($hold_name);
                    if ($hold_name != $rename) {
                        $sql = " UPDATE `".$config['DB_TABLE']."` SET".
                               " `tpl_name` = '".$db->escape($rename)."',".
                               " `tpl_lastchange` = NOW()".
                               " WHERE `tpl_hash` = '".$db->escape($hash)."'".
                               " AND `user_id` = '".(int)$config['SESSION_USER_ID']."' LIMIT 1";
                        $db->query($sql);
                    }
                    Mosaico_ServerResponseOk::set();
                }                                
            break;
            case 'tpl-duplicate':
                // Check posted hash
                if (!filter_has_var(INPUT_POST, 'hash')) {
                    Mosaico_ServerError::set(404, 'Missing hash value', true);
                }
                // Check empty posted hash
                else if (empty($_POST['hash'])) {
                    Mosaico_ServerError::set(404, 'Empty hash value', true);
                }
                $hash = (string)$_POST['hash'];
                
                $db = new Db();
                $sql = "SELECT * FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."'";
                if (!$row = $db->get_row($sql)) {
                    Mosaico_ServerError::set(404);
                }
                else {
                    $hash = uniqid('', true);
                    $basename = $row->tpl_basename;
                    $name = $row->tpl_name .' - '.$lang['DUPLICATE_TEMPLATE'];
                    $metadata = $row->tpl_metadata;
                    $content = $row->tpl_content;
                    $html = $row->tpl_html;
                    $sql = " INSERT INTO `".$config['DB_TABLE']."`".
                           " (`tpl_hash`, `user_id`, `tpl_basename`, `tpl_name`, `tpl_metadata`, `tpl_content`, `tpl_html`, `tpl_lastchange`)".
                           " VALUES".
                           " ('".$db->escape($hash)."','".(int)$config['SESSION_USER_ID']."','".$db->escape($basename)."','".$db->escape($name)."',".
                           " '".$db->escape($metadata)."','".$db->escape($content)."','".$db->escape($html)."', NOW())";
                    $db->query($sql);
                    Mosaico_ServerResponseOk::set();
                }                                
            break;
            case 'tpl-delete':
                // Check posted hash
                if (!filter_has_var(INPUT_POST, 'hash')) {
                    Mosaico_ServerError::set(404, 'Missing hash value', true);
                }
                // Check empty posted hash
                else if (empty($_POST['hash'])) {
                    Mosaico_ServerError::set(404, 'Empty hash value', true);
                }
                $hash = (string)$_POST['hash'];
                
                $db = new Db();
                $sql = "SELECT `tpl_name` FROM `".$config['DB_TABLE']."` WHERE `tpl_hash` = '".$db->escape($hash)."' AND `user_id` = '".(int)$config['SESSION_USER_ID']."'";
                if (!$hold_name = $db->get_var($sql)) {
                    Mosaico_ServerError::set(404);
                }
                else {
                    $sql = " DELETE FROM `".$config['DB_TABLE']."`".
                           " WHERE `tpl_hash` = '".$db->escape($hash)."'".
                           " AND `user_id` = '".(int)$config['SESSION_USER_ID']."' LIMIT 1";
                    $db->query($sql);
                    Mosaico_ServerResponseOk::set();
                }                                
            break;
            default:
                Mosaico_ServerError::set(400);
        }
    }
}