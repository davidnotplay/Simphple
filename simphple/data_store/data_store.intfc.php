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
 *
 * Interface for save a data in a cache.
 * @package simphple
 *
 */
interface Sphp_Data_Store{
	/**
	 * Delete a data of the cache
	 * @param string $data_name The name of the data that you want to delete.
	 * @return bool True if the data is deleted. False if the data not exists, or there is a error.
	 */
	public function delete_data($data_name);
	
	/**
	 * Get a data from the cache.
	 * @param string $data_name The name of the data that you want to get.
	 * @param mixed $data Output parameter with the data that you want.
	 * @return bool True if the data is extracted. False if the data not exists or there is a error.
	 * 
	 */
	public function get_data($data_name, &$data);
	
	/**
	 * Store a data in the cache.
	 * @param string $data_name Name of the new data. This name use after for get and delete the data.
	 * @param mixed $data Info that you store in the cache.
	 * @param bool|int $data_ttl Time to live in seconds that data store in the cache. If is false the data not expire.
	 */
	public function set_data($data_name, $data, $data_ttl = false);
}

?>
