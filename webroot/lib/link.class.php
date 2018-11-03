<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2018 Johannes Keßler
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
     * the current loaded link data
     * @var array
     */
    private $_data;

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

        $this->_data = array();

        if(!empty($hash)) {
            $queryStr = "SELECT * FROM `".DB_PREFIX."_link`
                            WHERE `hash` = '".$this->DB->real_escape_string($hash)."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows == 1) {
                $ret = $query->fetch_assoc();

                $this->_data = $ret;

                # add stuff
                $this->_tags();
                $this->_categories();
            }
        }

        return $this->_data;
    }

    public function getData($key=false) {
        $ret = $this->_data;

        if(!empty($key) && isset($this->_data[$key])) {
            $ret = $this->_data[$key];
        }

        return $ret;
    }

    /**
     * reload the current id from DB
     */
    public function reload() {
        $this->load($this->_data['hash']);
    }

    /**
     * create a new link with the given data
     * @param array $data
     */
    public function create($data) {
    }

    /**
     * update the current loaded link with the given data
     * @param array $data
     * @return boolean|int
     */
    public function update($data) {

        $ret = false;

        if(isset($data['title']) && !empty($data['title'])) {

            # categories and tag stuff
            $catArr = Summoner::prepareTagOrCategoryStr($data['category']);
            $tagArr = Summoner::prepareTagOrCategoryStr($data['tag']);

            $search = $data['title'];
            $search .= ' '.$data['description'];
            $search .= ' '.implode(" ",$tagArr);
            $search .= ' '.implode(" ",$catArr);

            $queryStr = "UPDATE `".DB_PREFIX."_link` SET
                            `status` = '".$this->DB->real_escape_string($data['private'])."',
                            `description` = '".$this->DB->real_escape_string($data['description'])."',
                            `title` = '".$this->DB->real_escape_string($data['title'])."',
                            `image` = '".$this->DB->real_escape_string($data['image'])."',
                            `search` = '".$this->DB->real_escape_string($search)."'
                          WHERE `hash` = '".$this->DB->real_escape_string($this->_data['hash'])."'";

            $query = $this->DB->query($queryStr);

            $catObj = new Category($this->DB);
            $tagObj = new Tag($this->DB);
            // clean the relations first
            $this->_removeTagRelation(false);
            $this->_removeCategoryRelation(false);

            if(!empty($catArr)) {
                foreach($catArr as $c) {
                    $catObj->initbystring($c);
                    $catObj->setRelation($this->_data['id']);
                }
            }
            if(!empty($tagArr)) {
                foreach($tagArr as $t) {
                    $tagObj->initbystring($t);
                    $tagObj->setRelation($this->_data['id']);
                }
            }

            $ret = true;
        }

        return $ret;
    }

    /**
     * check if the given URL exists in the DB
     * if so return the hash. If not, return false
     * @param string $link
     * @return string
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

        if(!empty($this->_data['hash'])) {
            $queryStr = "SELECT DISTINCT(tag) FROM `".DB_PREFIX."_combined`
                            WHERE `hash` = '".$this->DB->real_escape_string($this->_data['hash'])."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    if($result['tag'] !== NULL) {
                        $ret[] = $result['tag'];
                    }
                }

            }
        }

        $this->_data['tags'] = $ret;
    }

    /**
     * load all the categories we have to the already loaded link
     * needs $this->load called first
     */
    private function _categories() {
        $ret = array();

        if(!empty($this->_data['hash'])) {
            $queryStr = "SELECT DISTINCT(category) FROM `".DB_PREFIX."_combined`
                            WHERE `hash` = '".$this->DB->real_escape_string($this->_data['hash'])."'";
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
            while($result = $query->fetch_assoc()) {
                    if($result['category'] !== NULL) {
                        $ret[] = $result['category'];
                    }
                }
            }
        }

        $this->_data['categories'] = $ret;
    }

    /**
     * remove all or given tag relation to the current loaded link
     * @param mixed $tagid
     */
    private function _removeTagRelation($tagid) {
        if(!empty($this->_data['id'])) {
            $queryStr = false;
            if($tagid === false) {
                $queryStr = "DELETE FROM `".DB_PREFIX."_tagrelation`
                            WHERE `linkid` = '".$this->DB->real_escape_string($this->_data['id'])."'";
            }
            elseif(is_numeric($tagid)) {
                $queryStr = "DELETE FROM `".DB_PREFIX."_tagrelation`
                            WHERE `linkid` = '".$this->DB->real_escape_string($this->_data['id'])."'
                                AND `tagid` = '".$this->DB->real_escape_string($tagid)."'";
            }
            if(!empty($queryStr)) {
                $this->DB->query($queryStr);
            }
        }
    }

    /**
     * remove all or given category relation to the current loaded link
     * @param mixed $categoryid
     */
    private function _removeCategoryRelation($categoryid) {
        if(!empty($this->_data['id'])) {
            $queryStr = false;
            if($categoryid === false) {
                $queryStr = "DELETE FROM `".DB_PREFIX."_categoryrelation`
                            WHERE `linkid` = '".$this->DB->real_escape_string($this->_data['id'])."'";
            }
            elseif(is_numeric($categoryid)) {
                $queryStr = "DELETE FROM `".DB_PREFIX."_categoryrelation`
                            WHERE `linkid` = '".$this->DB->real_escape_string($this->_data['id'])."'
                                AND `categoryid` = '".$this->DB->real_escape_string($categoryid)."'";
            }
            if(!empty($queryStr)) {
                $this->DB->query($queryStr);
            }
        }
    }
}
 ?>