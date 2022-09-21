<?php
/**
 * @desc Class for return server error
 *
 * @class Mosaico_ServerError
 * @package Mosaico
 * @author Davide Caffaratti <davcaffa@gmail.com>
 * @version 1.0
 */
class Mosaico_ServerError
{    
    /**
     * Set server response_code and error message if present
     * @access public
     * @param string  $response_code Header code of the error
     * @param string  $msg           If present, contain the error message string
     * @param string  $exit          If true exit from the script
     * @return php header with $response_code and string with error writing inside if present
     */
    public static function set($response_code=500, $msg='', $exit=false)
    {
        http_response_code($response_code);
        if (!empty($msg)) {
            self::html($msg);
        }
        if ($exit) {
            exit;
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