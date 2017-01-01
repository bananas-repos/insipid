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

class Management {
    /**
     * the database object
     * @var object
     */
    private $DB;

    public function __construct($databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * get all the available categories from the DB.
     * optinal limit
     * @param int $limit
     */
    public function categories($limit=false) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_category` ORDER BY `name`";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }
        $query = $this->DB->query($queryStr);
        if(!empty($query)) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }

    /**
     * get all the available tags from the DB.
     * optional limit
     * @param int $limit
     */
    public function tags($limit=false) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_tag` ORDER BY `name`";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }
        $query = $this->DB->query($queryStr);
        if(!empty($query)) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }

    /**
     * return the latest addded links
     * @param number $limit
     */
    public function latest($limit=5) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_link` WHERE `status` = 2 ORDER BY `created` DESC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }
        $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }

    /**
     * get all the categories ordered by link added date
     */
    public function categoriesByDateAdded() {
        $ret = array();

        $categories = $this->categories();
        foreach($categories as $cat) {
            $queryStr = "SELECT insipid_category.name, insipid_link.created
                            FROM `insipid_category`
                            LEFT JOIN insipid_categoryrelation ON insipid_categoryrelation.categoryid = insipid_category.id
                            LEFT JOIN insipid_link ON insipid_link.id = insipid_categoryrelation.linkid
                            WHERE insipid_category.id = '".$this->DB->real_escape_string($cat['id'])."'
                            ORDER BY insipid_link.created DESC
                            LIMIT 1";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret[$result['name']] = $result['created'];
            }
        }

        arsort($ret);

        return $ret;
    }

    /**
     * find all links by given category string.
     * Return array sorted by creation date DESC
     * @param string $string
     * @param number $limit
     */
    public function linksByCategoryString($string,$limit=5) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_combined`
            WHERE `status` = 2
                AND `category` = '".$this->DB->real_escape_string($string)."'
            GROUP BY `hash`
            ORDER BY `created` DESC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }
        $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }

    /**
     * find all links by given tag string.
     * Return array sorted by creation date DESC
     * @param string $string
     * @param number $limit
     */
    public function linksByTagString($string,$limit=5) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_combined`
            WHERE `status` = 2
                AND `tag` = '".$this->DB->real_escape_string($string)."'
            GROUP BY `hash`
            ORDER BY `created` DESC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }
        $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }

    public function all($limit=false) {
        $ret = array();

        $queryStr = "SELECT * FROM `".DB_PREFIX."_combined`
                        WHERE `status` = 2
                        GROUP BY `hash`
                        ORDER BY `created` DESC";
        $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            $ret = $query->fetch_all(MYSQLI_ASSOC);
        }

        return $ret;
    }
}

?>