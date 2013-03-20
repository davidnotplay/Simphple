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
 * @author David Casado Martínez <tokkara@gmail.com>
 * @link http://www.simphple.com
 * @package simphple
 */

/**
 * This is the cache handler class.
 * @package simphple
 *
 */
class Sphp_Data_Store_File implements Sphp_Data_Store{

	/**
	 * path to the Cache directory.
	 * @var string
	 */
	private $directory_path;

	/**
	 * Flag indicating if reading the cache is enabled.
	 * @var bool
	 */
	private $read_data;
	
	/**
	 * Flag indicating if writing the cache is enabled.
	 * @var bool
	 */
	private $write_data;
	
	/**
	 * Class constructor
	 * @param string $directory_path Path to the cache directory
	 * @param bool $read_data Flag indicating if reading the cache is enabled.
	 * @param bool $write_data Flag indicating if writing the cache is enabled.
	 */
	public function __construct($directory_path, $read_data, $write_data){
		$this->directory_path = $directory_path;
		$this->read_data = $read_data;
		$this->write_data = $write_data;
		
		if(file_exists($this->directory_path)){
			$permisos = fileperms($this->directory_path);
			if(($permisos & 0x0100) && ($permisos &0x0080))
				return;
		}
		
		trigger_error(	"The file '".$this->directory_path .
						"' not exists or is not a directory", 
						E_USER_ERROR
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Sphp_Data_Store::delete_data()
	 */
	public function delete_data($data_name){
		if(!$this->write_data)
			return false;
		
		$file = $this->get_file_path($data_name);
		if(file_exists($file))
			return @unlink($file);
		return false;
	}

	/**
	 * Disable reading the cache.
	 */
	public function disable_read_data(){
		$this->read_data = false;
	}

	/**
	 * Disable writing the cache.
	 */
	public function disable_write_data(){
		$this->write_data = false;
	}

	/**
	 * Enable reading the cache.
	 */
	public function enable_read_data(){
		$this->read_data = true;
	}

	/**
	 * Enable writing the cache.
	 */
	public function enable_write_data(){
		$this->write_data = true;
	}

	/**
	 * (non-PHPdoc)
	 * @see Sphp_Data_Store::get_data()
	 */
	public function get_data($data_name, &$data){
		if(!$this->read_data)
			return false;
		
		$is_valid = false;
		$file = $this->get_file_path($data_name);
		if(file_exists($file)){
			include $file;
			return $is_valid;
		}
		return false;
	}

	/**
	 * Return the path to the cache directory.
	 * @return string
	 */
	public function get_directory_path(){
		return $this->directory_path;
	}
	
	/**
	 * Return the cache filename.
	 * @param string $data_name data name stores in the file.
	 * @return string
	 */
	private function get_file_path($data_name){
		return $this->directory_path.$data_name.'.cache.php';
	}

	/**
	 * (non-PHPdoc)
	 * @see Sphp_Data_Store::set_data()
	 */
	public function set_data($data_name, $data, $data_ttl = false){
		if(!$this->write_data)
			return;

		$data = var_export($data, true);
		$data_file = "";
		if($data_ttl!==false){
			$time = time()+$data_ttl;
			$is_valid = "time()<=$time";
			$expired_msg = "The data will expire on ".gmdate(DATE_RFC822, $time).".";
			$data = "\$is_valid? $data: null";			
		}else{
			$is_valid = "true";
			$expired_msg = "The data not expire.";
		}
		
		
		$data_file = 	"<?php\n".
						"/*\n".
						" * Data name: ".$data_name."\n".
						" * Expire: ".$expired_msg."\n".
						" * Other comments:\n".
						" */\n".
						"\$is_valid = ".$is_valid.";\n".
						"\$data = ".$data.";\n".
						"?>";
		
		$file = $this->get_file_path($data_name);
		$fp = @fopen($file, "w");
		$file_saved = false;
		if($fp){
			@flock($fp, LOCK_EX);
			$file_saved = @fwrite($fp, $data_file);
			@flock($fp, LOCK_UN);
			@fclose($fp);
		}
		
		if(!$file_saved)
			trigger_error("Cannot create the file '$file'.", E_USER_ERROR);
	}
}
?>