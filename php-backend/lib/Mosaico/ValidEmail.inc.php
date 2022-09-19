<?php
/**
 * @desc Class for Check email formt (basic check)
 *
 * @class Mosaico_ValidEmail
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ValidEmail
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
