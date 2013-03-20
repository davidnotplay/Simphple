<?php

include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

$options = array('template_dir_path' => 'templates/', 'vars_without_keys' => Sphp_Template::VARS_WK_ENABLED, 'debug' =>false);
$dsf = new Sphp_Data_Store_File('cache/', true, true);
$tpl = new Sphp_Template($dsf, $options);


$created = false;
$loaded = false;
$deleted = false;
$name = '';
$value = '';
$exists = false;

if(isset($_POST['send'])){
	$name = $_POST['name'];
	$value = $_POST['value'];
	$ttl = (int) $_POST['ttl'];
	$created = true;
	
	$dsf->set_data($name, $value, $ttl);
}

if(isset($_POST['send_load'])){
	$name = $_POST['name'];
	$loaded = true;
	$exists = $dsf->get_data($name, $value);
}

if(isset($_POST['send_delete'])){
	$name = $_POST['name'];
	$deleted = true;
	$exists = $dsf->delete_data($name);
}

$vars = array(	'created' => $created,
				'deleted' => $deleted,
				'exists' => $exists,
				'loaded' => $loaded,
				'name' => $name,
				'value' => $value

);

$tpl->add_variables($vars);
$tpl->display_file('cache.html');