<?php
include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/template/template_tools.class.php';

define('FEMALE', 1);
define('MALE', 2);

$dsf = new Sphp_Data_Store_File('cache/', true, true);
$options = array('debug' => true, 'template_dir_path' => 'templates/', 'vars_without_keys' => Sphp_Template::VARS_WK_ENABLED_MODIFIERS);
$tpl = new Sphp_Template($dsf, $options);

if(isset($_POST['number']))
	$tpl->add_variable('number', (int)$_POST['number']);
if(isset($_POST['gender']))
	$tpl->add_variable('gender', (int)$_POST['gender']);
if(isset($_POST['age']))
	$tpl->add_variable('age', (int)$_POST['age']);

$tpl->add_function(new Sphp_Template_Function('is_numeric'));-

$tpl->display_file('if.html');