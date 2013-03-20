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

function position($p1, $p2, $p3, $p4, $p5, $p6, $p7){
	return $p1.' '.$p2.' '.$p3.' '.$p4.' '.$p5.' '.$p6.' '.$p7;
}

$func1 = new Sphp_Template_Function('pos', 'position');
$func1->add_tpl_arg(1)->add_arg('p2')->add_arg('p3')->add_tpl_arg(2)->add_arg('p6');

$tpl->add_function($func1);
$tpl->display_file('sphp_function_class.html');
