<?php
include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/template/template_tools.class.php';
include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

$options = array('debug' => false, 'template_dir_path' => 'templates/', 'vars_without_keys' => Sphp_Template::VARS_WK_ENABLED);
$tpl = new Sphp_Template(new Sphp_Data_Store_File('cache/', true, true), $options);

$list_countries = new Sphp_Template_List('list_countries');
$list_cities = new Sphp_Template_List('cities');

$list_cities->create_row()->add_data('name', 'Madrid');
$list_cities->create_row()->add_data('name', 'Barcelona');
$list_cities->create_row()->add_data('name', 'Bilbao');
$list_countries->create_row()->add_data('name', 'Spain')->add_list($list_cities);

$list_cities->empty_list();
$list_cities->create_row()->add_data('name', 'Paris');
$list_cities->create_row()->add_data('name', 'Niza');
$list_cities->create_row()->add_data('name', 'Marsella');
$list_countries->create_row()->add_data('name', 'France')->add_list($list_cities);

$list_cities->empty_list();
$list_cities->create_row()->add_data('name', 'London');
$list_cities->create_row()->add_data('name', 'Liverpool');
$list_cities->create_row()->add_data('name', 'Manchester');
$list_countries->create_row()->add_data('name', 'England')->add_list($list_cities);

$tpl->add_list($list_countries);
$tpl->display_file('list_and_rows.html');
