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

$countries = array(	array(	'continent' => 'Europe',
							'language' => 'Spanish',
							'name' => 'Spain',
							'cities' => array(	array('name' => 'Madrid'),	
												array('name' => 'Barcelona'),
												array('name' => 'Bilbao')
							)
					),
					array(	'continent' => 'Europe',
							'language' => 'English',
							'name' => 'England',
							'cities' => array(	array('name' => 'London'),	
												array('name' => 'Birmingham'),
												array('name' => 'Manchester')
							)
					),
					array(	'continent' => 'America',
							'language' => 'spanish',
							'name' => 'Mexico',
							'cities' => array(	array('name' => 'Acapulco'),
												array('name' => 'Guadalajara'),
												array('name' => 'Ciudad de Mexico, D.F')
							)
									
					)
);
$nums = range(1, 45);
shuffle($nums);
$tpl->add_variable('number', $nums);
$tpl->add_variable('countries', $countries);
$tpl->add_variable('colors', array('blue', 'green', 'red', 'white', 'yellow'));
$tpl->display_file('foreach.html');