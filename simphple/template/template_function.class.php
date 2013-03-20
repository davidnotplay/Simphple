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

* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @author	David Casado Martínez <tokkara@gmail.com>
* @link	http://www.simphple.com
* @package simphple
*/

/**
 * Class used for declare a simphple function.
 * @package simphple
 *
 */
class Sphp_Template_Function{
	/**
	 * Number of arguments.
	 * @var int
	 */
	private $args_number;
	
	/**
	 * Declared arguments from php.
	 * @var array
	 */
	private $ary_args;
	
	/**
	 * Flag indicating if the function is callable.
	 * @var bool
	 */
	private $callable;
	
	/**
	 * Callable code.
	 * @var string
	 */
	private $callable_code;
	
	/**
	 * Class name.
	 * @var string
	 */
	private $class_name;

	/**
	 * Object for the php method. If the function is not a method then this variable is null.
	 * @var mixed
	 */
	private $class_object;
	
	/**
	 * The php function name.
	 * @var string
	 */
	private $function_name;
	
	/**
	 * The id or the simphple function name.
	 * @var string
	 */
	private $name;
	
	/**
	 * class constructor.
	 * @param string $name The id or simphple function name.
	 * @param string $function_name The php function name.
	 * @param mixed If the php function name is a method of a class then this parameter stores a object that class. If not the value is null. 
	 */
	public function __construct($name, $function_name = '', $class_object = null){
		$this->ary_args = array();
		$this->args_number = 0;
		$this->class_object = $class_object;
		$this->name = $name;
		$this->function_name = $function_name? $function_name: $this->name;
		
		$check_f = '';
		$p = stripos($this->function_name, '::');

		if($this->class_object!==null){
			$check_f = array($this->class_object, $this->function_name);
			$this->class_name = get_class($this->class_object);
		}elseif($p!==false){
			$this->class_name = substr($this->function_name, 0, $p);
			$check_f = array($this->class_name, substr($this->function_name, $p+2));
		}else{
			$this->class_name = '';
			$check_f = $this->function_name;
		}

		//Check if the function exists.
		$this->callable = is_callable($check_f, false, $this->callable_code);
	}
	
	/**
	 * Add an automatic argument to the php function.
	 * @param value for this argument.
	 * @return Sphp_Template_Function
	 */
	public function add_arg($arg){
		$this->ary_args[$this->args_number++] = $arg;
		return $this;
	}
	
	/**
	 * Add an automatic template argument to the simphple function.
	 * @param int $size number of arguments.
	 * @return Sphp_Template_Function
	 */
	public function add_tpl_arg($size = 1){
		$this->args_number += $size;
		return $this;
	}
	
	/**
	 * Return the number of automatic arguments stored in the class.
	 * @return int
	 */
	public function get_args_number(){
		return $this->args_number;
	}
	
	/**
	* Return an array with all automatic arguments.
	* @return array
	*/	
	public function get_ary_args(){
		return $this->ary_args;
	}
	
	/**
	 * Return the class name stored in Sphp_Template_Function::$class_object
	 * @return string
	 */
	public function get_class_name(){
		return $this->class_name;
	}
	
	/**
	 * Return Sphp_Template_Function::$class_object
	 * @return mixed
	 */
	public function get_class_object(){
		return $this->class_object;
	}
	
	/**
	 * Return the php function name.
	 * @return string
	 */
	public function get_function_name(){
		return $this->function_name;
	}
	
	/**
	 * Return the id or simphple function name.
	 * @return string
	 */
	public function get_name(){
		return $this->name;
	}
	
	/**
	 * Check if the php function name saved in this class is callable from php.
	 * @param string $code php code with the function declaration.
	 * @return boolean True if is callable, false if not.
	 */
	public function is_callable(&$code){
		$code = $this->callable_code;
		return $this->callable;
	}
}
?>