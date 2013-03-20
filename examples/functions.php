<?php

include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/template/template_tools.class.php';

$dsf = new Sphp_Data_Store_File('cache/', true, true);
$options = array('debug' => true, 'template_dir_path' => 'templates/');
$tpl = new Sphp_Template($dsf, $options);

class Test{
	public static function uppercase($str){
		return strtoupper($str);
	}
	
	public function lowercase($str){
	 	return strtolower($str);
	}
}

$func1 = new Sphp_Template_Function('first_uppercase', 'ucfirst');
$func2 = new Sphp_Template_Function('static_method', 'Test::uppercase');
$func3 = new Sphp_Template_Function('method', 'lowercase', new Test());

$tpl->add_function($func1);
$tpl->add_function($func2);
$tpl->add_function($func3);
$tpl->add_variable('var1', 'All LOWERcase');

$tpl->display_file('functions.html');