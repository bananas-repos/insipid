<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2021 Johannes Keßler
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

/**
 * Class Tag
 */
class Tag {
	/**
	 * the database object
	 *
	 * @var object
	 */
	private $DB;

	/**
	 * the current loaded tag by DB id
	 *
	 * @var int
	 */
	private $_id;

	/**
	 * current loaded tag data
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * Tag constructor.
	 *
	 * @param Obnject $databaseConnectionObject
	 */
	public function __construct($databaseConnectionObject) {
		$this->DB = $databaseConnectionObject;
	}

	/**
	 * by given string load the info from the DB and even create if not existing
	 *
	 * @param string $string
	 * @param bool $doNotCreate
	 * @return int 0=fail, 1=existing, 2=new, 3=newNotCreated
	 */
	public function initbystring(string $string, $doNotCreate=false): int {
	    $ret = 0;
		$this->_id = false;
		if(!empty($string)) {
			$queryStr = "SELECT `id`,`name` FROM `".DB_PREFIX."_tag`
							WHERE `name` = '".$this->DB->real_escape_string($string)."'";
			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$result = $query->fetch_assoc();
				$this->_id = $result['id'];
				$this->_data = $result;
				$ret = 1;
			}
			else {
			    if(!$doNotCreate) {
                    $queryStr = "INSERT INTO `" . DB_PREFIX . "_tag`
                                    SET `name` = '" . $this->DB->real_escape_string($string) . "'";
                    $this->DB->query($queryStr);
                    if (!empty($this->DB->insert_id)) {
                        $this->_id = $this->DB->insert_id;
                        $this->_data['id'] = $this->_id;
                        $this->_data['name'] = $string;
                        $ret = 2;
                    }
                }
			    else {
			        $ret=3;
                }
			}
		}
		return $ret;
	}

	/**
	 * by given DB table id load all the info we need
	 *
	 * @param int $id
	 * @return int
	 */
	public function initbyid(int $id): int {
		$this->_id = 0;

		if(!empty($id)) {
			$queryStr = "SELECT `id`,`name` FROM `".DB_PREFIX."_tag`
							WHERE `id` = '".$this->DB->real_escape_string($id)."'";
			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$result = $query->fetch_assoc();
				$this->_id = $result['id'];
				$this->_data = $result;
			}
		}

		return $this->_id;
	}

	/**
	 * return all or data fpr given key on the current loaded tag
	 *
	 * @param bool $key
	 * @return array|string
	 */
	public function getData($key=false) {
		$ret = $this->_data;

		if(!empty($key) && isset($this->_data[$key])) {
			$ret = $this->_data[$key];
		}

		return $ret;
	}

	/**
	 * set the relation to the given link to the loaded tag
	 *
	 * @param int $linkid
	 * @return void
	 */
	public function setRelation(int $linkid) {
		if(!empty($linkid) && !empty($this->_id)) {
			$queryStr = "INSERT IGNORE INTO `".DB_PREFIX."_tagrelation`
							SET `linkid` = '".$this->DB->real_escape_string($linkid)."',
								`tagid` = '".$this->DB->real_escape_string($this->_id)."'";
			$this->DB->query($queryStr);
		}
	}

    /**
     * Return an array of any linkid related to the current loaded tag
	 *
     * @return array
     */
	public function getReleations(): array {
	    $ret = array();

	    $queryStr = "SELECT linkid 
	                FROM `".DB_PREFIX."_tagrelation` 
	                WHERE tagid = '".$this->DB->real_escape_string($this->_id)."'";
	    $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            while($result = $query->fetch_assoc()) {
                $ret[] = $result['linkid'];
            }
        }

        return $ret;
    }

	/**
	 * deletes the current loaded tag from db
	 *
	 * @return boolean
	 */
	public function delete(): bool {
		$ret = false;

		if(!empty($this->_id)) {
			$this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

			try {
				$queryStr = "DELETE
					FROM `".DB_PREFIX."_tagrelation`
					WHERE `tagid` = '".$this->DB->real_escape_string($this->_id)."'";
				$this->DB->query($queryStr);

				$queryStr = "DELETE
					FROM `".DB_PREFIX."_tag`
					WHERE `id` = '".$this->DB->real_escape_string($this->_id)."'";
				$this->DB->query($queryStr);

				$this->DB->commit();
			} catch (Exception $e) {
				if(DEBUG) {
					var_dump($e->getMessage());
				}
				error_log('ERROR Failed to remove tag: '.var_export($e->getMessage(),true));

				$this->DB->rollback();
			}
		}

		return $ret;
	}

	/**
	 * Rename current loaded tag name
	 *
	 * @param string $newValue
	 * @return void
	 */
	public function rename(string $newValue) {
	    if(!empty($newValue)) {
	        $queryStr = "UPDATE `".DB_PREFIX."_tag`
	                    SET `name` = '".$this->DB->real_escape_string($newValue)."'
	                    WHERE `id` = '".$this->DB->real_escape_string($this->_id)."'";
	        $this->DB->query($queryStr);
            $this->_data['name'] = $newValue;
        }
    }
}
