<?php
include '../simphple/data_store/data_store.intfc.php';
include '../simphple/data_store/data_store_file.class.php';

include '../simphple/template/template_code.class.php';
include '../simphple/template/template.class.php';
include '../simphple/template/template_function.class.php';
include '../simphple/template/template_tools.class.php';

function custom_error_function($errno, $err_msg, $err_file, $err_line){
	$is_error = $errno==E_USER_ERROR || $errno==E_RECOVERABLE_ERROR;
	$error_msg =	date('d-m-Y H:i').' - Template '.(!$is_error? 'warning': 'error').': '.$err_msg.' in file '.$err_file.' in line '. 
					$err_line."\n";
	@file_put_contents('errors.log', $error_msg, FILE_APPEND);
	
	if($is_error){
		echo	"<b>Error in the template. </b>See the <a href=\"errors.log\" title=\"errors.log\">log</a> for more information.<br/>".
				"<a href=\"custom_error_function.php\">Back</a>";
		exit;
	}
}

$dsf = new Sphp_Data_Store_File('cache/', true, true);
$options = array(	'debug' => false, 'error_func' => 'custom_error_function', 
					'template_dir_path' => 'templates/', 'vars_without_keys' => Sphp_Template::VARS_WK_ENABLED_MODIFIERS
);
$tpl = new Sphp_Template($dsf, $options);


if(isset($_GET['page'])){
	$tpl->add_variable('error_file', 'error_templates/'.$_GET['page'].'.html');
}

$tpl->add_function(new Sphp_Template_Function('strlen'));

$tpl->display_file('custom_error_function.html');