<?php
/**
 * @desc Class for return server error
 *
 * @class Mosaico_ServerResponseOk
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ServerResponseOk
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