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
    private $id;

    public function __construct($databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * by given string load the info from the DB and even create if not existing
     * @param string $string
     */
    public function initbystring($string) {
        if(!empty($string)) {
            $queryStr = "SELECT id FROM `".DB_PREFIX."_category`
                            WHERE `name` = '".$this->DB->real_escape_string($string)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $this->id = $result['id'];
            }
            else {
                $queryStr = "INSERT INTO `".DB_PREFIX."_category`
                                SET `name` = '".$this->DB->real_escape_string($string)."'";
                $this->DB->query($queryStr);
                if(!empty($this->DB->insert_id)) {
                    $this->id = $this->DB->insert_id;
                }
            }
        }
    }

    /**
     * by given DB table id load all the info we need
     * @param int $id
     */
    public function initbyid($id) {
        if(!empty($id)) {
            $this->id = $id;
        }
    }

    /**
     * set the relation to the given link to the loaded category
     * @param int $linkid
     * @return boolean
     */
    public function setRelation($linkid) {
        if(!empty($linkid) && !empty($this->id)) {
            $queryStr = "INSERT IGNORE INTO `".DB_PREFIX."_categoryrelation`
                            SET `linkid` = '".$this->DB->real_escape_string($linkid)."',
                                `categoryid` = '".$this->DB->real_escape_string($this->id)."'";
            $this->DB->query($queryStr);
        }
    }
}
 ?>