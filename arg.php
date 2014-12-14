<?php
/**
 * CodoLike
 * @copyright  Copyright (c) 2015 Riccardo Tempesta (http://www.riccardotempesta.com)
 */

class codolike {

    public static $path = "";
    public static $language = "english";
    public static $like_post_path = "";
    public static $like_update_path = "";
    public static $trans = array();
    public static $db;
    public static $db_prefix;

    public static function get_lang() {

        //$codopm_trans is declared in all language files
        //For backward compatibility purposes always include english.php
        require 'lang/english.php';

        if (self::$language != "english") {

            //Overwrite english with the new language
            require 'lang/' . self::$language . '.php';
        }

        return $codolike_trans;
    }

    public static function t($index) {
        echo self::s($index);
    }

    public static function j($index) {
        echo json_encode(self::s($index));
    }

    public static function s($index) {

        if (!self::$trans) self::$trans = self::get_lang();

        if (isset(self::$trans[$index]))
            return self::$trans[$index];

        return $index;
    }
}
