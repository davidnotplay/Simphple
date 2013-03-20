<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Casado Martínez <tokkara@gmail.com>
 * @link http://www.simphple.com
 * @package simphple
 */

/**
 * Main class to the simphple framework.
 * @package simphple
*/ 
class Sphp_Template extends Sphp_Template_Exe{
	/**
	 * Array with the options for simphple.
	 * @var array
	 */
	private $ary_options;
	
	/**
	 * Array with the error and warning messages
	 * @var array
	 */
	private $ary_warnings;
	
	/**
	 * Object that implements the Sphp_Data_Store interface.
	 * @var Sphp_Data_Store
	 */
	private $dstore;
	
	/**
	 * class constructor
	 * @param Sphp_Data_Store $dstore Object that implements the Sphp_Data_Store interface.
	 * @param array $ary_options Vector with the Simphple options.
	 */
	public function __construct(Sphp_Data_Store $dstore, $ary_options = array()){
		$this->ary_modifiers = $this->ary_warnings = array();
		$this->dstore = $dstore;
		$this->ary_options = array(	'compact' => false,
									'debug' => false,
									'error_func' => '',
									'template_dir_path' => './',
									'vars_without_keys' => self::VARS_WK_DISABLED
		);		

		foreach($this->ary_options as $name => $value)
			$this->ary_options[$name] = isset($ary_options[$name])? $ary_options[$name]: $value;
		
		parent::__construct();
	}

	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::add_function()
	 */
	public function add_function(Sphp_Template_Function $stf, $modifier = ''){
		parent::add_function($stf, $modifier);	
	}
	
	/**
	 * Add a list to the Simphple framework.
	 * @param Sphp_Template_List $stl
	 */
	public function add_list(Sphp_Template_List $stl){
		parent::add_variable($stl->get_name(), $stl->get_array_data());
	}
	

	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::add_variable()
	 */
	public function add_variable($name, $value){
		parent::add_variable($name, $value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::add_variables()
	 */
	public function add_variables($vars){
		parent::add_variables($vars);
	}

	/**
	 * Compact the php code.
	 * @param string $code Php code.
	 * @return string Php code compacted.
	 */
	private function compact_code($code){
		return str_replace(array("\n", "\r"), "", $code);
	}
	
	/**
	 * Show a template file.
	 * @param string $filename path of the file to be displayed.
	 */
	public function display_file($file){
		$this->ary_warnings = array();

		ob_start();
		$this->start($file, $this->ary_options['vars_without_keys']);

		if($this->ary_options['debug'] && $this->ary_warnings)
			echo implode('', $this->ary_warnings).ob_get_clean();
		else
			ob_end_flush();
	}
	
	/**
	 * Get the cache file name using the file name.
	 * @param string $file File name.
	 * @return string Cache file name.
	 */
	private function get_cache_file($file){
		return str_replace(array('/', '.'), array('_', ''), $file);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::get_code()
	 */
	protected function get_code($file, &$code){
		if(!$this->ary_options['debug'])
			return $this->dstore->get_data($this->get_cache_file($file), $code);
		
		return false;
	}
	
	/**
	 * Return the object that implements the Sphp_Data_Store interface.
	 * @return Sphp_Data_Store
	 */
	public function get_data_store(){
		return $this->dstore;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::get_file()
	 */
	protected function get_file($file){
		return $this->ary_options['template_dir_path'].$file;
	}
	
	/**
	 * Get a Simphple option.
	 * @param string $option_name The option value.
	 * @return mixed The option value. If the option not exists then return null.
	 */
	public function get_option($option_name){
		if(isset($this->ary_options[$option_name]))
			return $this->ary_options[$option_name];
		
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::launch_error_func()
	 */
	protected function launch_error_func($errno, $err_msg, $err_file, $err_line){
		$is_error = $errno==E_USER_ERROR || $errno==E_RECOVERABLE_ERROR;
		
		//DEBUG MODE OFF.
		if(!$this->ary_options['debug']){
			$func = $this->ary_options['error_func'];
			$code = '';
			
			if($func && is_callable($func, $code)){
				call_user_func($func, $errno, $err_msg, $err_file, $err_line);
			}else if($code)
				exit("The error function '$code' is undefined.");

			if($is_error)
				exit("Template error.");
				
			return;
		}
		
		//DEBUG MODE ON.
		if(!$is_error)
			$this->ary_warnings[] = "<b>Template warning: </b>$err_msg in file <b>$err_file</b> on line <b>$err_line</b><br/>";
		else{
			foreach($this->ary_warnings as $warn)
				echo $warn;
			
			echo "<b>Template error: </b> $err_msg in file <b>$err_file</b> on line <b>$err_line</b>";
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Sphp_Template_Exe::set_code()
	 */
	protected function set_code($file, $code){
		if(!$this->ary_options['debug']){
			if($this->ary_options['compact'])
				$code = $this->compact_code($code);
			
			$this->dstore->set_data($this->get_cache_file($file), $code);
		}
	}
	
	/**
	 * Set a simphple option.
	 * @param string $option_name Option name.
	 * @param mixed $option_value New option value.
	 * @return boolean True if the option exists. False if not.
	 */
	public function set_option($option_name, $option_value){
		if(isset($this->ary_options[$option_name])){
			$this->ary_options[$option_name] = $option_value;
			return true;
		}
		
		return false;
	}
}