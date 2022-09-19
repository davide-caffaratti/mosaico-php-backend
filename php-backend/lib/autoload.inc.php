<?php
/**
 * Very simple autoloader class
 *
 * This class loads another classes following the Zend naming convention:
 *    - Class name: Foo_Bar
 *    - File name:  Foo/Bar.php
 *
 * @uses DescriptionExample of use:
 * >    require_once('path/to/Autoloader.php');
 * >    Autoloader::init();
 * >    $bar = new Foo_Bar();
 *
 * @package autoload
 **/
class Autoloader {

    /**
     * File extension for the class files
     *
     * @var string
     **/
    protected static $_extension = '.inc.php';

    /**
     * Directory separator for the file names
     *
     * @var string
     **/
    protected static $_dirSeparator = '/';

    /**
     * Name separator for the namespaces in the file names
     *
     * @var string
     **/
    protected static $_nameSeparator = '_';

    /**
     * Initialize the autoload system
     *
     * @param string $autoloader  The class that act as autoloader. Defaults to self.
     * @return boolean
     **/
    public static function init($autoloader = __CLASS__) {
        return spl_autoload_register($autoloader . '::load');
    }

    /**
     * Load the class
     *
     * @param string $class The class to load
     * @return void
     **/
    public static function load($class) {
        $file = self::generateFileName($class);

        if (file_exists($file)) {
            if (!class_exists($class, true)) {
                require_once($file);
            }
        } else {
            throw new Exception('*File*: ' .$file . ' for *Class* ' . $class . ' not exists');
        }
    }

    /**
     * Generate the name of the class file
     *
     * @param string $class The class name
     * @return string The file name
     **/
    protected static function generateFileName($class) {
        $file = str_replace(
            self::$_nameSeparator,
            self::$_dirSeparator,
            $class
        );
        return (dirname(__FILE__)) . '/' . $file . self::$_extension;
    }
}
