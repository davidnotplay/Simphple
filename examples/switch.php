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

if(isset($_POST['word']))
	$tpl->add_variable('word', $_POST['word']);

$tpl->display_file('switch.html');