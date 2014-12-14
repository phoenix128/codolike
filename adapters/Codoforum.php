<?php
/**
 * CodoLike
 * @copyright  Copyright (c) 2015 Riccardo Tempesta (http://www.riccardotempesta.com)
 */

class CodoLikeAdapter {

    public function __construct() {
        
    }

    public function setup_tables() {


    }

    public function get_user() {

        return \CODOF\User\User::get();
    }

    public function add_js($js) {

        add_js($js, array('type' => 'defer'));
    }

    public function add_css($css) {

        add_css($css);
    }

    public function get_abs_path() {

        return PLUGIN_PATH . 'codolike/';
    }

}
