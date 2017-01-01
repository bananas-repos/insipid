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

class Link {
    /**
     * the database object
     * @var object
     */
    private $DB;

    /**
     * the current loaded tag by DB id
     * @var int
     */
    private $id;

    public function __construct($databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * load all the info we have about a link by given hash
     * @param string $hash
     * @return mixed
     */
    public function load($hash) {
        $ret = false;

        if(!empty($hash)) {
            $queryStr = "SELECT * FROM `".DB_PREFIX."_link`
                            WHERE `hash` = '".$this->DB->real_escape_string($hash)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows == 1) {
                $ret = $query->fetch_assoc();

                $this->id = $ret['hash'];

                # add stuff
                $ret['tags'] = $this->_tags();
                $ret['categories'] = $this->_categories();
            }
        }

        return $ret;
    }

    public function create($data) {}

    /**
     * check if the given URL exists in the DB
     * if so return the id. If not, return false
     * @param string $link
     * @return boolean|int
     */
    public function exists($link) {
        $ret = false;

        if(!empty($link)) {
            $queryStr = "SELECT * FROM `".DB_PREFIX."_link`
                        WHERE `link` = '".$this->DB->real_escape_string($link)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret = $result['hash'];
            }
        }

        return $ret;
    }

    /**
     * load all the tags we have to the already loaded link
     * needs $this->load called first
     */
    private function _tags() {
        $ret = array();

        if(!empty($this->id)) {
            $queryStr = "SELECT DISTINCT(tag) FROM `".DB_PREFIX."_combined`
                            WHERE `hash` = '".$this->DB->real_escape_string($this->id)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $ret = $query->fetch_all(MYSQLI_ASSOC);
            }
        }

        return $ret;
    }

    /**
     * load all the categories we have to the already loaded link
     * needs $this->load called first
     */
    private function _categories() {
        $ret = array();

        if(!empty($this->id)) {
            $queryStr = "SELECT DISTINCT(category) FROM `".DB_PREFIX."_combined`
                            WHERE `hash` = '".$this->DB->real_escape_string($this->id)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $ret = $query->fetch_all(MYSQLI_ASSOC);
            }
        }

        return $ret;
    }
}
 ?>