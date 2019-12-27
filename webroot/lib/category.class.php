<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2017 Johannes Keßler
 *
 * Development starting from 2011: Johannes Keßler
 * https://www.bananas-playground.net/projekt/insipid/
 *
 * creator:
 * Luke Reeves <luke@neuro-tech.net>
 *
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
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.
 *
 */

class Category {
	/**
	 * the database object
	 * @var object
	 */
	private $DB;

	/**
	 * the current loaded category by DB id
	 * @var int
	 */
	private $_id;

	/**
	 * current loaded tag data
	 * @var array
	 */
	private $_data;

	public function __construct($databaseConnectionObject) {
		$this->DB = $databaseConnectionObject;
	}

	/**
	 * by given string load the info from the DB and even create if not existing
	 * @param string $string
	 * @return bool|int
	 */
	public function initbystring($string) {
		$this->_id = false;
		if(!empty($string)) {
			$queryStr = "SELECT `id`,`name` FROM `".DB_PREFIX."_category`
							WHERE `name` = '".$this->DB->real_escape_string($string)."'";
			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$result = $query->fetch_assoc();
				$this->_id = $result['id'];
				$this->_data = $result;
			}
			else {
				$queryStr = "INSERT INTO `".DB_PREFIX."_category`
								SET `name` = '".$this->DB->real_escape_string($string)."'";
				$this->DB->query($queryStr);
				if(!empty($this->DB->insert_id)) {
					$this->_id = $this->DB->insert_id;
					$this->_data['id'] = $this->_id;
					$this->_data['name'] = $string;
				}
			}
		}
		return $this->_id;
	}

	/**
	 * by given DB table id load all the info we need
	 * @param int $id
	 * @return mixed
	 */
	public function initbyid($id) {
		$this->_id = false;

		if(!empty($id)) {
			$queryStr = "SELECT id,name
				FROM `".DB_PREFIX."_category`
				WHERE `id` = '".$this->DB->real_escape_string($id)."'";
			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$result = $query->fetch_assoc();
				$this->_id = $id;
				$this->_data = $result;
			}
		}

		return $this->_id;
	}

	/**
	 * return all or data fpr given key on the current loaded tag
	 * @param bool $key
	 * @return array|mixed
	 */
	public function getData($key=false) {
		$ret = $this->_data;

		if(!empty($key) && isset($this->_data[$key])) {
			$ret = $this->_data[$key];
		}

		return $ret;
	}

	/**
	 * set the relation to the given link to the loaded category
	 * @param int $linkid
	 * @return void
	 */
	public function setRelation($linkid) {
		if(!empty($linkid) && !empty($this->_id)) {
			$queryStr = "INSERT IGNORE INTO `".DB_PREFIX."_categoryrelation`
							SET `linkid` = '".$this->DB->real_escape_string($linkid)."',
								`categoryid` = '".$this->DB->real_escape_string($this->_id)."'";
			$this->DB->query($queryStr);
		}
	}

	/**
	 * deletes the current loaded category from db
	 * @return boolean
	 */
	public function delete() {
		$ret = false;

		if(!empty($this->_id)) {
			$this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

			try {
				$queryStr = "DELETE
					FROM `".DB_PREFIX."_categoryrelation`
					WHERE `categoryid` = '".$this->DB->real_escape_string($this->_id)."'";
				$this->DB->query($queryStr);

				$queryStr = "DELETE
					FROM `".DB_PREFIX."_category`
					WHERE `id` = '".$this->DB->real_escape_string($this->_id)."'";
				$this->DB->query($queryStr);

				$this->DB->commit();
			} catch (Exception $e) {
				if(DEBUG) {
					var_dump($e->getMessage());
				}
				error_log('ERROR Failed to remove category: '.var_export($e->getMessage(),true));

				$this->DB->rollback();
			}
		}

		return $ret;
	}
}
