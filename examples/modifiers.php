<?php

include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/template/template_tools.class.php';

$dsf = new Sphp_Data_Store_File('cache/', true, true);
$options = array('debug' => true, 'template_dir_path' => 'templates/', 'vars_without_keys' => Sphp_Template::VARS_WK_ENABLED_MODIFIERS);
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

$tpl->add_function($func1, 'f');
$tpl->add_function($func2, 'U');
$tpl->add_function($func3, 'l');

$tpl->add_variable('var1', 'first letter in uppercase.');
$tpl->add_variable('var2', 'aLL UPERcase.');
$tpl->add_variable('var3', 'All LOWERcase.');
$tpl->add_variable('var4', 'All LOWERcase, then the FIRST LETTER in uppercase.');
$tpl->display_file('modifiers.html');
