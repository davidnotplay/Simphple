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
*/

//Make the error constants.
if(!defined('E_DEPRECATED')){
	/** @var int */
	define('E_DEPRECATED', 8192);
}

if(!defined('E_RECOVERABLE_ERROR')){
	/** @var int */
	define('E_RECOVERABLE_ERROR', 4096);
}

if(!defined('E_USER_DEPRECATED')){
	/** @var int */
	define('E_USER_DEPRECATED', 16384);
}

/**
 *
 * Class that parses the simphple code.
 * @package simphple
 *
 */
class Sphp_Template_Code{
	
	const VERSION = '1.1.1';
	
	/**
	 * Format for names and function names.
	 * @var string
	 */
	private static $name = "[_a-zA-Z][_a-zA-Z0-9]*";
	
	/**
	 * Errors in the simphple code
	 * @var string[]
	 */
	private $errors;
	
	/**
	 * code line parsed.
	 * @var int
	 */
	private $line;

	/**
	 * Flag indicating the "vars without keys" type.
	 * @var int
	 */
	private $vars_wk;
	
	/**
	 * class construct
	 */
	public function __construct(){
		$this->errors = array();
		$this->line = 0;
		$this->vars_wk = Sphp_Template_Exe::VARS_WK_DISABLED;
	}
	
	/**
	 * Check if there illegal characters in simphple code.
	 * @param string $code Simphple code.
	 * @return boolean True if there illegal characters.
	 */
	private function check_illegal_characters($code){
		$char = array();
		//Illegal characters.
		$search = 	'#(\[|\]|::|\?|:|->|\+=|-=|\*=|/=|\.=|%=|&=|\|=|\^=|<<=|>>=|\+\+|--|\{|\}|@|(?<![<>=!])=(?!=)|'.
					'(?:^[^a-zA-Z0-9_$]*(?:return|for|function|foreach|as|while|if|switch|case|break)[^a-zA-Z0-9_]*$)|'.
					'\$(?:'.self::$name.'\.)?'.self::$name.'[\s]*\()#';
		
		if(preg_match_all($search, $code, $char)){
			$this->errors[] = 'syntax error, illegal string \\\''.$char[0][0].'\\\'.';
			return true;
		}
		
		
		//Incomplete strings
		if(stripos($code, '"')!==false || stripos($code, '\'')!==false){
				$this->errors[] = 'parse error.';
				return true;
			}
			
		return false;
	}
	
	/**
	 * Check if there php tags in simphple code.
	 * @param string $code Simphple code.
	 * @return boolean True if there php tags.
	 */
	private function check_php_code($code){
		$search = '#<\?php[\s]+|<\?[\s]+|[\s]+\?>#';
		if(preg_match($search, $code)){
			$this->errors[] = 'syntax error, illegal php tags';
			return true;
		}
		
		return false;
	}
	
	/**
	 * Parse the code (HTML & Simphple) and transform the simphple code in php code.
	 * @param string $code Simphple code and HTML code.
	 * @param int $vars_wk Flag indicating the "variable without keys" type. 
	 * @return string Php code and HTML code.
	 */
	public function parse($code, $vars_wk){
		$this->errors = array();
		$this->line = 0;
		$this->vars_wk = $vars_wk;
		
		$lines = $php_lines = array();	
		$lines = explode("\n", $code);
		$lines_sz = sizeof($lines);
		unset($code);
		
		do{
			$php_lines[$this->line] = '<?php $this->line='.($this->line+1).';?>'.$this->parse_line($lines[$this->line]);
			$this->line++;
		}while($this->line<$lines_sz && !$this->errors);

		return !$this->errors ?	preg_replace('#\[^;]?>([\s]*)<\?php#', '$1', implode("\n", $php_lines)):
								'<?php $this->sphp_error(E_USER_ERROR,\''.$this->errors[0].'\',false,'.
									$this->line.'); ?>';
	}

	/**
	 * Parse a line of code.
	 * @param string $codeline Code line.
	 * @return return Simphple Code transformed in php code.
	 */
	private function parse_line($codeline){
		//Empty line
		if(!trim($codeline))
			return $codeline;

		//Get html comments and key structures.
		$codeline = $this->t_html_comments_in_key($codeline, $htmlc);
		$codeline = $this->t_key_structures_in_key($codeline, $keys);
		
		//Check illegal php tags
		if($this->check_php_code($codeline))
			return 'true';
		
		if($this->vars_wk!==Sphp_Template::VARS_WK_DISABLED)
			$codeline = $this->t_vars_wk($codeline);
		
		//Transform the key in php code.
		$search = array(	'#;;;SPHP_HTML_COMMENT_([0-9]+);;;#e',
							'#;;;SPHP_KEY_STRUCTURE_([0-9]+);;;#e'
		);
		
		$replace = array(	'$htmlc[$1]',
							'$keys[$1]'	
		);
		
		return preg_replace($search, $replace, $codeline);
	}
	
	/**
	 * Store a Simphple code string in array.
	 * @param string[] $strings Array used for store the string.
	 * @param string $quot type of quote (' or ").
	 * @param string $string String stored in array.
	 * @return string Empty string.
	 */
	private function store_string(&$strings, $quot, $string){
		//Delete the \ character
		$quot = strlen($quot)>1? substr($quot, -1): $quot;
	
		//Delete the var parser.
		if($quot!="'")
			$string = preg_replace('#(\\\*)\$#e', '\'$1\'.(strlen(\'$1\')%2!=0? \'$\': \'\\\$\')', $string);
	
		$strings[] = array($quot, str_replace('\\\\"', '"', $string));
		return '';
	}
	
	/**
	 * Trasnform a simphple basic code(function, strings, variables & operators) in php code.
	 * @param string $code Simphple basic code.
	 * @param string $modifiers Modifiers used in the simphple basic code.
	 * @return string Php code.
	 */
	private function t_basic_sphp_code_in_php_code($code, $modifiers = ''){
		//Transform the strings in key
		$code = $this->t_strings_in_key($code, $strings);
		
		//Check illegal php tags
		if($this->check_php_code($code))
			return 'true';
		
		//Check illegal characters
		if($this->check_illegal_characters($code))
			return 'true';
		
		//Transform the variables and functions in key
		$code = $this->t_var_in_key($code, $vars);
		$code = $this->t_function_in_key($code, $functions);

		//Add the modifiers.
		if($modifiers)
			$code = $this->t_modifiers($code, $modifiers);

		//Transform keys in php code.
		$code = $this->t_key_in_function($code, $functions);
		$code = $this->t_key_in_var($code, $vars);
		$code = $this->t_key_in_string($code, $strings);
		
		return $code;
	}
	
	/**
	 * Transform a function in php code.
	 * @param string $func_name Function name.
	 * @param string $args String with all arguments of the function.
	 * @return string Php code.
	 */
	private function t_function($func_name, $args){
		//No function
		if(!$func_name)
			return '('.$args.')';
		
		switch($func_name){
			case 'isset':
				$args = str_replace('SPHP_VARIABLE', 'SPHP_VARIABLE_ISSET', $args);
				return '$this->sphp_func_isset('.$args.')';
				
			case 'empty':
				$args = str_replace('SPHP_VARIABLE', 'SPHP_VARIABLE_EMPTY', $args);
				return '$this->sphp_func_empty('.$args.')';

			case 'array':
				return 'array('.$args.')';
			
			default:
				return '$this->launch_function(\''.$func_name.'\''.($args? ','.$args: '').')';

		}
	}
	
	/**
	 * Transform a simphple function in a simphple function key.
	 * @param string $code Simphple code.
	 * @param Arrary $functions Array with all functions in the code.
	 * @return string Simphple code with simphple function keys.
	 */
	private function t_function_in_key($code, &$functions){
		$functions = array();
		$functions_sz = 0;
		$search = '#('.self::$name.'[ \t]*)?\(([^(]*?)\)#e';
		$replace = '(($functions[$functions_sz] = array(\'$1\',\'$2\'))&&false).\';;;SPHP_FUNCTION_\'.($functions_sz++).\';;;\'';
		
		while(preg_match($search, $code))
			$code = preg_replace($search, $replace, $code);

		return $code;
	}
	
	/**
	 * Transform the html comments in keys.
	 * @param string $code Simphple code
	 * @param string[] $htmlc Array with all html comments transformed into php code.
	 * @return string Simphple code.
	 */
	private function t_html_comments_in_key($code, &$htmlc){
		$htmlc = array();
		$htmlc_sz = 0;
		$search = '#(<!--.*?(?:(\'|\\\\*")(.*?)(?<!\\\\)\2.*?)*?-->)#e';
		$replace = '(($htmlc[$htmlc_sz]=\'$1\')&&false).\';;;SPHP_HTML_COMMENT_\'.($htmlc_sz++).\';;;\'';

		$code = preg_replace($search, $replace, $code); 
		
		//Transform the html comments in php code.
		$search = array(	'#<!-- INCLUDE (.+?) -->#e',
							'#<!-- IF (.+?) -->#e',
							'#<!-- ELSEIF (.+?) -->#e',
							'#<!-- ELSE -->#',
							'#<!-- ENDIF -->#',
							'#<!-- SWITCH (.+) CASE (.+) -->#e',
							'#<!-- ENDSWITCH -->#',
							'#<!-- CASE (.+?) -->#e',
							'#<!-- DEFAULT -->#',
							'#<!-- BREAK -->#',
							'#<!-- FOREACH ('.self::$name.')[\t ]*=[\t ]*(.+?) -->#e',
							'#<!-- ENDFOREACH -->#',
							'#<!-- EXIT -->#',
							'#<!-- CONTINUE -->#',
		);
		$replace = array( 	'$this->t_struct_include(\'$1\')',
							'$this->t_struct_if(\'$1\', false)',
							'$this->t_struct_if(\'$1\', true)',
							'<?php else:$this->line='.($this->line+1).'; ?>',
							'<?php endif; ?>',
							'$this->t_struct_switch(\'$1\', \'$2\')',
							'<?php endswitch; ?>',
							'$this->t_struct_case(\'$1\')',
							'<?php default: ?>',
							'<?php break; ?>',
							'$this->t_struct_foreach(\'$1\', \'$2\')',
							'<?php endfor; ?>',
							'<?php return true; ?>',
							'<?php continue; ?>'
		);
		
		for($i = 0; $i<$htmlc_sz; $i++)
			$htmlc[$i] = preg_replace($search, $replace, $htmlc[$i]);
		
		return $code;
	}
	
	/**
	 * Transform the simphple keys in php functions.
	 * @param string $code Simphple code.
	 * @param array $functions Function list.
	 * @return string Php code.
	 */
	private function t_key_in_function($code, &$functions){
		$search = '#;;;SPHP_FUNCTION_([0-9]+);;;#e';
		$replace =  '$this->t_function($functions[$1][0], $functions[$1][1])';
	
		while(preg_match($search, $code))
			$code = preg_replace($search, $replace, $code);
	
		return $code;
	}
	
	/**
	 * Transform the simphple keys in php strings.
	 * @param string $code Simphple code.
	 * @param string[] $strings String list.
	 * @return string Php code.
	 */
	private function t_key_in_string($code, $strings){
		$strings_i = 0;
		$search = '#;;;SPHP_STRING;;;#e';
		$replace = '$strings[$strings_i][0].$strings[$strings_i][1].$strings[$strings_i++][0]';
		return preg_replace($search, $replace, $code);
	}
	
	/**
	 * Transform the simphple keys in Php vars.
	 * @param string $code Simple code.
	 * @param array $vars variable list.
	 * @return string Php code.
	 */
	private function t_key_in_var($code, $vars){
		$search = array(	'#;;;SPHP_VARIABLE_ISSET_([0-9]+);;;#e',
				'#;;;SPHP_VARIABLE_EMPTY_([0-9]+);;;#e',
				'#;;;SPHP_VARIABLE_([0-9]+);;;#e'
		);
		$replace = array(	'$this->t_sphp_var_in_php_var($vars[$1][0], $vars[$1][1], \'isset\')',
				'$this->t_sphp_var_in_php_var($vars[$1][0], $vars[$1][1], \'empty\')',
				'$this->t_sphp_var_in_php_var($vars[$1][0], $vars[$1][1])'
	
		);
		return preg_replace($search, $replace, $code);
	}
	
	/**
	 * Transform the 'key' structures in simphple keys.
	 * @param string $code Simphple code.
	 * @param array[] $keys Array with all 'key' structures transformed into php code.
	 * @return string Simphple code.
	 */
	private function t_key_structures_in_key($code, &$keys){
		$keys = array();
		$keys_sz = 0;
		$search = '#\{([^\n\r{]*?(?:(\'|\\\\*")(?:.*?)(?<!\\\\)\2.*?)*?)\:([a-zA-Z]*)\}#e';
		$replace = '(($keys[$keys_sz]=array(\'$1\', \'$3\'))&&false).\';;;SPHP_KEY_STRUCTURE_\'.($keys_sz++).\';;;\'';
	
		$code = preg_replace($search, $replace, $code);
	
		for($i = 0; $i<$keys_sz; $i++)
			$keys[$i] = '<?php echo '.$this->t_basic_sphp_code_in_php_code($keys[$i][0], $keys[$i][1]).'; ?>';
	
			return preg_replace($search, $replace, $code);
	}
	
	/**
	 * Add the modifiers to the simphple code.
	 * @param string $code Simphple code.
	 * @param string $modifiers modifier characters.
	 * @return string Php code.
	 */
	private function t_modifiers($code, $modifiers){
		$modifiers_sz = strlen($modifiers);
		$code_parts = array_map('trim', explode(',', $code));
		$format = '$this->launch_modifier(\'%1$s\', %2$s)';
		foreach($code_parts as $i => $code){
			for($j = 0; $j<$modifiers_sz; $j++)
				$code = sprintf($format, $modifiers[$j], $code);
	
				$code_parts[$i] = $code;
		}
	
		return implode(',', $code_parts);
	}

	/**
	 * Transform a simphple variable in a php variable.
	 * @param string $prefix Prefix of a simphple variable.
	 * @param string $name Name of a simphple variable.
	 * @param string $function_name String indicating if this variable will use in a special function (isset or empty)
	 * @return string Php variable.
	 */
	private function t_sphp_var_in_php_var($prefix, $name, $function_name = ''){
		//$_sphp_._FILE_ and $_sphp_._LINE_ variables.
		if($prefix=='_sphp_' && ($name=='LINE' || $name=='FILE' || $name=='VERSION')){
			if($function_name=='isset')
				return 'true';
			if($function_name=='empty')
				return 'false';
				
			return $name=='LINE'? '$this->line': ($name=='VERSION'? 'Sphp_Template_Code::VERSION': '$this->file');
		}
	
		$error_format = '$this->sphp_error(E_USER_NOTICE, \'Undefined $%1$s variable\')';
		$var_format =	($function_name=='isset' || $function_name=='empty')?
		$function_name.'(%2$s)':
		'(isset(%2$s)?%3$s:'.$error_format.')';
	
		//Simple variable
		if(!$prefix){
			$var = '$this->vars[\''.$name.'\']';
			return sprintf($var_format, $name, $var, $var);
		}
			
	
		//Include arguments
		if($prefix=='_sphp_')
			if(preg_match('#^arg[0-9]+$#', $name)){
			$var = '$__f_args__['.substr($name, 3).']';
			return sprintf($var_format, $prefix.'.'.$name, $var, $var);
		}else
			return '('.sprintf($error_format, $prefix.'.'.$name).')';
	
		//Foreach variables.
		if($prefix){
			switch($name){
				case '_CUR_':
					$var = '$'.$prefix.'_cur';
					return sprintf($var_format, $prefix.'._CUR_', $var, $var);
	
				case '_EVEN_':
					$var_iss = '$'.$prefix.'_cur';
					$expr = '$'.$prefix.'_cur%2!=0';
					return sprintf($var_format, $prefix.'._EVEN_', $var_iss, $expr);
	
				case '_FIRST_':
					$var_iss = '$'.$prefix.'_cur';
					$expr = '$'.$prefix.'_cur==0';
					return sprintf($var_format, $prefix.'._FIRST_', $var_iss, $expr);
	
				case '_LAST_':
					$var_iss = '$'.$prefix.'_cur';
					$expr = '$'.$prefix.'_cur+1==$'.$prefix.'_max';
					return sprintf($var_format, $prefix.'._LAST_', $var_iss, $expr);
						
				case '_MAX_':
					$var = '$'.$prefix.'_max';
					return sprintf($var_format, $prefix.'._MAX_', $var, $var);
	
				case '_VAL_':
					$var = '$'.$prefix.'_var';
					return sprintf($var_format, $prefix.'_VAL_', $var, $var);
	
				default:
					$var = '$'.$prefix.'_var';
					$var_name = $var.'[\''.$name.'\']';
					if($function_name=='empty')
						return 'empty('.$var_name.') || !is_array('.$var.')';
	
					$iss = 'isset('.$var_name.')&&is_array('.$var.')';
					return $function_name=='isset'?	'('.$iss.')':
					'('.$iss.'?'.$var_name.':'.sprintf($error_format, $prefix.'.'.$name).')';
			}
		}
	}
	
	/**
	 * Transform the Simphple strings in simphple keys.
	 * @param string $code Simphple code.
	 * @param string[] $strings String list.
	 * @return string Simphple code.
	 */
	private function t_strings_in_key($code, &$strings){
		$strings = array();
		$search ='#(\'|\\\\*")(.*?)(?<!\\\\)\1#e';
		$replace = '($this->store_string($strings, \'$1\', \'$2\')).\';;;SPHP_STRING;;;\'';
		return preg_replace($search, $replace, $code);
	}
	
	/**
	 * Transform the 'case' structure in php code.
	 * @param string $code Code for 'case' structure.
	 * @return string Php code.
	 */
	private function t_struct_case($code){
		return '<?php case (($this->line='.($this->line+1).')&&false).'.$this->t_basic_sphp_code_in_php_code($code).': ?>';
	}
	
	/**
	 * Trasnform the 'foreach' structure in php code.
	 * @param string $name Foreach name.
	 * @param string $code Code for 'foreach' structure.
	 * @return string Php code.
	 */
	private function t_struct_foreach($name, $code){
		$ary = '$'.$name.'_ary';
		$cur = '$'.$name.'_cur';
		$max = '$'.$name.'_max';
		$var = '$'.$name.'_var';
		$code = $this->t_basic_sphp_code_in_php_code($code);
		
		return	'<?php '.
				$ary.'='.$code.';'.
				'if(is_array('.$ary.')){'.
				$ary.'=array_values('.$ary.');'.
				'}else{'.
				$ary.'=array();'.
				'$this->sphp_error(E_USER_WARNING, "The foreach argument should be an array");'.
				'}'.
				$max.'=sizeof('.$ary.');'.
				'for('.$cur.'=0; '.$cur.'<'.$max.'; '.$cur.'++):'.
				$var.'='.$ary.'['.$cur.'];'.
				'?>';
	}
	
	/**
	 * Transform the 'if' structure in php code.
	 * @param string $code Code for 'if' structure.
	 * @param boolean $elseif Flag indicating if the structure is 'if' or 'elseif'.
	 * @return string Php code.
	 */
	private function t_struct_if($code, $elseif){
		$code = $this->t_basic_sphp_code_in_php_code($code);
		$else = '';
		if($elseif){
			$else = 'else';
			$code = '($this->line='.($this->line+1).')&&'.$code;
		}
		return '<?php '.$else.'if('.$code.'): ?>';
	}
	
	/**
	 * Transform the 'include' structure in php code.
	 * @param string $code Code for 'include' structure.
	 * @return string Php code.
	 */
	private function t_struct_include($code){
		return '<?php $this->include_file('.$this->t_basic_sphp_code_in_php_code($code).');?>';
	}
	

	/**
	 * Transform the 'switch' structure in php code.
	 * @param string $code_switch Code for the 'switch' structure.
	 * @param unknown $code_case Code for the first 'case' structure.
	 * @return string Php code.
	 */
	private function t_struct_switch($code_switch, $code_case){
		$code_switch = $this->t_basic_sphp_code_in_php_code($code_switch);
		$code_case = $this->t_basic_sphp_code_in_php_code($code_case);
		return '<?php switch('.$code_switch.'): case (($this->line='.($this->line+1).')&&false).'.$code_case.': ?>';
	}
	
	/**
	 * Transform the simphple variables in a simphple keys
	 * @param string $code Simphple code.
	 * @param array $vars Variable list.
	 * @return string Simphple code.
	 */
	private function t_var_in_key($code, &$vars){
		$vars_sz = 0;
		$vars = array();
		$search = '#\$(?:('.self::$name.')\.)?('.self::$name.')#e';
		$replace = '(($vars[$vars_sz]=array(\'$1\', \'$2\'))&&false).\';;;SPHP_VARIABLE_\'.($vars_sz++).\';;;\'';
		
		return preg_replace($search, $replace, $code);		
	}
	
	/**
	 * Transform the "variables without keys" in php code.
	 * @param string $code Simphple code.
	 * @return string Php code.
	 */
	private function t_vars_wk($code){
		$search =	'#(\$(?:'.self::$name.'\.)?'.self::$name.')'.
					($this->vars_wk==Sphp_Template_Exe::VARS_WK_ENABLED_MODIFIERS? '(?:\:([a-zA-Z]+))?': '').'#e';
		$replace = 	'\'<?php echo \'.($this->t_basic_sphp_code_in_php_code(\'$1\', \'$2\')).\'; ?>\'';
		
		return preg_replace($search, $replace, $code);
	}
}

/**
 * Execute the php code resulting of the simphple code.
 * @package simphple
 *
 */
abstract class Sphp_Template_Exe{
	/**
	 * Constant indicating that you use variables without keys
	 * @var int
	 */
	const VARS_WK_ENABLED = 1;

	/**
	 * Constant indicating that you use the variables and modifiers without keys.
	 * @var int
	 */
	const VARS_WK_ENABLED_MODIFIERS = 2;

	/**
	 * Constnant indicating thay you not use the variables and modifiers without keys.
	 * @var int
	 */
	const VARS_WK_DISABLED = 3;

	/**
	 * Name of the simphple file that is executing.
	 * @var string
	 */
	private $file;
	
	/**
	 * Array with all function in simphple code.
	 * @var Sphp_Template_Function[]
	 */
	private $functions;
	
	/**
	 * Line of code.
	 * @var unknown
	 */
	private $line;
	
	/**
	 * Array with the modifiers.
	 * @var string[]
	 */
	private $modifiers;
	
	/**
	 * Instance of the Sphp_Template_Code class. it is used for transform the simphple code.
	 * @var Sphp_Template_Code
	 */
	private $sphp_code;
	
	/**
	 * Array with all variables of the simphple code.
	 * @var array
	 */
	private $vars;
	
	/**
	 * Flag indicating the "variable without keys" type.
	 * @var int
	 */
	private $vars_wk;

	/**
	 * Get the php code of a cache handler.
	 * @param string $file Simphple file name. You use the name how id for the cache handler.
	 * @param string $code Out parameter. Php code stored in the cache
	 * @return boolean True if the file name is correct. False if not.
	 */
	abstract protected function get_code($file, &$code);
	
	/**
	 * Get the Simphple file path using the file name.
	 * @param string $file File name.
	 * @return string Simphple file path.
	 */
	abstract protected function get_file($file);
	
	/**
	 * Launch the error function when detect a error in simphple code.
	 * @link http://php.net/manual/en/errorfunc.constants.php
	 * @param int $errno Error id.
	 * @param string $err_msg Error message.
	 * @param unknown $err_file Error file.
	 * @param unknown $err_line Error line.
	 */
	abstract protected function launch_error_func($errno, $err_msg, $err_file, $err_line);
	
	/**
	 * Put the php code in a cache.
	 * @param string $file Simphple file name. We use the name how id for the cache handler.
	 * @param unknown $code Code that you want store in the cache.
	 */
	abstract protected function set_code($file, $code);

	/**
	 * class constructor.
	 */
	public function __construct(){
		$this->vars = $this->functions = $this->modifiers = array();
		$this->file = '';
		$this->line = 0;
		$this->sphp_code = new Sphp_Template_Code();
		$this->vars_wk = self::VARS_WK_DISABLED;
	}

	/**
	 * Add a new simphple function to the framework.
	 * @param Sphp_Template_Function $stf Object with the new function.
	 * @param string $modifier Modifier associated a this function.
	 */
	protected function add_function(Sphp_Template_Function $stf, $modifier = ''){
		$name = $stf->get_name();
		$this->functions[$name] = $stf;

		//Delete previous modifier
		if(($pos = array_search($name, $this->modifiers))!==false)
			unset($this->modifiers[$pos]);

		//Add the new modifier.
		if($modifier)
			$this->modifiers[$modifier[0]] = $name;
	}

	/**
	 * Add a variable to the Simphple framework.
	 * @param string $name Variable name.
	 * @param mixed $value Variable value
	 */
	protected function add_variable($name, $value){
		$this->vars[$name] = $value;
	}

	/**
	 * Add one or more variables to the Simphle framework.
	 * @param array $vars 	Array with the variables that you storing in the simphple framework.
	 * 						The key of the array is the varialbe name, and the data is the variable value.
	 */
	protected function add_variables($vars){
		$this->vars = array_merge($this->vars, $vars);
	}

	/**
	 * Check if a Simphple file exists.
	 * @param string $file File name. Out parameter is the file complete path.
	 * @return string If there is a error then return the error message. If not return empty string.
	 */
	private function check_file(&$file){
		if(!$file || !is_string($file))
			return 'File path is invalid';

		$file = $this->get_file($file);
		if(!is_readable($file))
			return "Failed to open file '$file'";

		return '';
	}

	/**
	 * Execute a simphple file.
	 * @param string $file Simphple file name.
	 * @param array $__f_args__ Arguments for the file.
	 */
	private function execute_file($file, $__f_args__){
		$old_file = $this->file;
		$old_line = $this->line;
		$this->file = $file;
		$code = '';

		//Get the code.
		$code_stored = $this->get_code($this->file, $code);
		if(!$code_stored){
			//Extract the simphple code and parse.
			$code = file_get_contents($file);
			$code = $this->sphp_code->parse($code, $this->vars_wk);
		}

		//Execute php code.
		ob_start();
		if(!eval('?>'.$code.'<?php return true; ?>')){
			$err_msg = ob_get_clean();
			$this->get_eval_error($err_msg, $err_line);
			$err_msg = str_replace("';'", "':}'", $err_msg);
			$this->sphp_error(E_USER_ERROR, $err_msg, $this->file, $err_line);
		}
		ob_end_flush();

		//Store the data in the cache.
		if(!$code_stored)
			$this->set_code($this->file, $code);

		$this->line = $old_line;
		$this->file = $old_file;
	}

	/**
	 * Parse and get the message and the line of a eval error.
	 * @param string $err_msg Eval error message. Out parameter is the error message in the eval error message.
	 * @param int $err_line Line in the eval error message.
	 * @return boolean True if is a eval message error false if not.
	 */
	private function get_eval_error(&$err_msg, &$err_line){
		//The msg isnt eval error.
		if(stripos($err_msg, "eval()'d")===false)
			return false;

		$data = array();
		$err_msg = str_replace("$", '&#36;', strip_tags($err_msg));

		$search = '#(.+?) in .+? eval\(\)\'d code on line ([0-9]+).*#e';
		$replace = '(string)$data = array("$1", $2);';

		preg_replace($search, $replace, $err_msg);
		list($err_msg, $err_line) = $data;
		$err_msg = str_replace("\\'", "'", $err_msg);

		return true;
	}

	/**
	 * Transform a simphple code in php code.
	 * @param string $file Simphple file name.
	 * @param int $vars_wk Flag indicating the "variable without keys" type. 
	 * @return string Php code.
	 */
	public function get_php_code($file, $vars_wk=self::VARS_WK_DISABLED){
		$err_msg = $this->check_file($file);
		if($err_msg)
			trigger_error($err_msg, E_USER_ERROR);

		return $this->sphp_code->parse(file_get_contents($file), $vars_wk);
	}

	/**
	 * Check and execute a Simphple file. This method is used in the simphple code.
	 * @param string $file Simphple file name.
	 */
	private function include_file($file){
		$error = $this->check_file($file);
		if($error)
			$this->sphp_error(E_USER_ERROR, $error);

		$args = func_get_args();
		$this->execute_file($file, $args);
	}
	
	/**
	 * Execute a simphple function
	 * @param string $func_name Function name.
	 * @param mixed $... Simphple parameters for the function.
	 * @return mixed Return the data returned for the function.
	 */
	private function launch_function($func_name){
		//The tpl function is undefined.
		if(!isset($this->functions[$func_name]))
			$this->sphp_error(E_USER_ERROR, "Tpl function '$func_name()' is undefined.");
	
		$function = $this->functions[$func_name];
		//The php funcion is undefined
		if(!$function->is_callable($c_callable))
			$this->sphp_error(E_USER_ERROR, "Tpl funcion '".$func_name."()' not callable (PHP function '$c_callable()' is undefined.)");
	
		//Make the params.
		$args = array();
		$tpl_args = func_get_args();
		$tpl_args = array_slice($tpl_args, 1);
		$php_args = $function->get_ary_args();
		$php_args_l = $function->get_args_number();
	
		for($i = 0; $i<$php_args_l; $i++)
			if(isset($php_args[$i]))		// Use a php argument.
			$args[] = $php_args[$i];
			elseif($tpl_args)				//Use a tpl argument.
			$args[] = array_shift($tpl_args);
	
			//Add the last tpl arguments.
			if($tpl_args)
			$args = array_merge($args, $tpl_args);
	
			//Make the function code.
			$c_func_name = ($function->get_class_object()? '$function->get_class_object()->': '').$function->get_function_name();
			$c_func_args = $args? '$args['.(implode('],$args[', range(0, sizeof($args)-1, 1))).']': '';
	
			return eval("return $c_func_name($c_func_args);");
	}
	
	/**
	 * Execute a modifier.
	 * @param string $modifier modifier character
	 * @param mixed $expr1 Parameter for the function associate to the modifier.
	 * @return mixed Return the data returned for the function.
	 */
	private function launch_modifier($modifier, $expr1){
		//Check if the modifier exists.
		if(!isset($this->modifiers[$modifier]))
			$this->sphp_error(E_USER_ERROR, "The modifier '$modifier' not exists.");

		return $this->launch_function($this->modifiers[$modifier], $expr1);
	}

	/**
	 * Show a simphple error.
	 * @link http://php.net/manual/en/errorfunc.constants.php
	 * @param int $errno Error id
	 * @param string $err_msg Error message
	 * @param string|boolean $err_file Error file. If is false then you use the 'file' property.
	 * @param string|boolean $err_line Error line. If is false then you use the 'line' property.
	 */
	private function sphp_error($errno, $err_msg, $err_file = false, $err_line = false){
		$is_error = $errno==E_USER_ERROR || $errno==E_RECOVERABLE_ERROR;
		$err_file = $err_file!==false? $err_file: realpath($this->file);
		$err_line = $err_line!==false? $err_line: $this->line;
		
		//If is a error then clean all buffers.
		if($is_error)
			while(ob_get_level()>0)
			ob_end_clean();
		
		$this->launch_error_func($errno, $err_msg, $err_file, $err_line);
		
		//If is a error then exit of php.
		if($is_error)
			exit;
	}
	
	/**
	 * Simphple empty function. This method is used in the simphple code.
	 * @param boolean $... All parameters of the simphple empty function.
	 * @return boolean
	 */
	private function sphp_func_empty(){
		$args = func_get_args();
		return in_array(true, $args);
	}

	/**
	 * Simphple isset function. This method is used in the simphple code.
	 * @param boolean $... All parameters of the simphple isset function.
	 * @return boolean
	 */
	private function sphp_func_isset(){
		$args = func_get_args();
		return !in_array(false, $args);
	}
	
	/**
	 * Execute the Simphple file. This method is used for start the simphple framework.
	 * @param string $file File name
	 * @param int $vars_wk Flag indicating the "variable without keys" type.
	 */
	final protected function start($file, $vars_wk = self::VARS_WK_DISABLED){
		$this->vars_wk = $vars_wk;
		set_error_handler(array($this, 'template_error_handler'));

		$err_msg = $this->check_file($file);
		if($err_msg){
			$btrace = debug_backtrace();
			$btrace = $btrace[0];
			$this->sphp_error(E_USER_ERROR, $err_msg, $btrace['file'], $btrace['line']);
		}
		
		$this->file= $file;
		$this->execute_file($file, array());
		restore_error_handler();
	}

	/**
	 * Error Simphple handler.
	 * @link http://php.net/manual/en/errorfunc.constants.php
	 * @param int $errno Error id.
	 * @param string $err_msg Error message
	 * @param string $err_file Error file
	 * @param int $err_line Error line.
	 * @return boolean True if is a simphple error. False if is other error.
	 */
	final public function template_error_handler($errno, $err_msg, $err_file, $err_line){
		if(stripos($err_file, "eval()'d")!==false || $this->get_eval_error($err_msg, $err_line)){
			$err_file = realpath($this->file);
			$err_line = $this->line;
			$err_msg = str_replace("';'", "':}'", $err_msg);
		}else
			return false;
				
		$this->sphp_error($errno, $err_msg, $err_file, $err_line);
		return true;
	}
}
?>