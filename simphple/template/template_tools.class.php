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
 * Make a row for a list.
 * @see Sphp_Template_List
 * @package simphple
 */
class Sphp_Template_Row{
	
	/**
	 * Array with the data of the list.
	 * @var array
	 */
	private $ary_data;
	
	/**
	 * class constructor
	 */
	public function __construct(){
		$this->ary_data = array();
	}
	
	/**
	 * Add a data to the list
	 * @param string $name data name.
	 * @param mixed $value data value.
	 * @return Sphp_Template_Row The object that you use.
	 */
	public function add_data($name, $value){
		$this->ary_data[$name] = $value;
		return $this;
	}
	
	/**
	 * Add a sub-list to the list
	 * @param Sphp_Template_List New list.
	 * @return Sphp_Template_Row The object that you use.
	 */
	public function add_list(Sphp_Template_List $list){
		$this->ary_data[$list->get_name()] = $list->get_array_data();
		return $this;
	}
	
	/**
	 * Delete all data of the list.
	 */
	public function empty_row(){
		$this->ary_data = array();
	}
	
	/**
	 * Get an array with all data of the list.
	 * @return array
	 */
	public function get_array_data(){
		return $this->ary_data;
	}
}

/**
 * 
 * Make a new list for Simphple framework.
 * @see Sphp_Template_Row
 * @see Sphp_Template::add_list
 * @author David
 *
 */
class Sphp_Template_List{
	
	/**
	 * List name. This name is used as the name of the array in the foreach structure.
	 * @var string
	 */
	private $name;
	
	/**
	 * Array with all rows to the list.
	 * @var array
	 */
	private $ary_rows;
	
	/**
	 * Number of rows in the list.
	 * @var int
	 */
	private $ary_rows_length;

	/**
	 * class constructor
	 * @param string $name list name.
	 */
	public function __construct($name){
		$this->name = $name;
		$this->ary_rows_length = 0;
		$this->ary_rows = array();	
	}
	
	/**
	 * Add a new row at the end of the list.
	 * @param Sphp_Template_Row $row
	 * @return Sphp_Template_List The object that you use.
	 */
	public function add_row(Sphp_Template_Row $row){
		$this->ary_rows[] = $row;
		$this->ary_rows_length++;
		return $this;
	}
	
	/**
	 * Make a new row at the end of the list.
	 * @return Sphp_Template_Row
	 */
	public function create_row(){
		$r = new Sphp_Template_Row();
		$this->add_row($r);
		return $r;
	}
	
	/**
	 * Delete the last row of the list.
	 */
	public function delete_row(){
		if($this->ary_rows_length>0){
			$this->ary_rows_length--;
			unset($this->ary_rows[$this->ary_rows_length]);
		}
	}
	
	/**
	 * Delete all rows of the list.
	 */
	public function empty_list(){
		$this->ary_rows = array();
		$this->ary_rows_length = 0;
	}
	
	/**
	 * Return an array with all data of the list.
	 * @return array
	 */
	public function get_array_data(){
		$data = array();
		foreach($this->ary_rows as $row){
			$data[] = $row->get_array_data();
		}
		return $data;
	}
	
	/**
	 * Return the list name.
	 * @return string
	 */
	public function get_name(){      
		return $this->name;
	}
	
	/**
	 * Return the last row of the list.
	 * @return Sphp_Template_Row Return an object with the last row. If the list is empty return null.
	 */
	public function get_row(){
		return $this->ary_rows_length>0? $this->ary_rows[$this->ary_rows_length-1]: null;
	}	
	
	/**
	 * Return the rows number.
	 * @return number
	 */
	public function get_number_rows(){
		return $this->ary_rows_length;
	}
}