<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2023 Johannes Keßler
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
     * @var mysqli
     */
    private mysqli $DB;

    /**
     * the current loaded category by DB id
     * @var string
     */
    private string $_id;

    /**
     * current loaded tag data
     * @var array
     */
    private array $_data;

    /**
     * @param mysqli $databaseConnectionObject
     */
    public function __construct(mysqli $databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * by given string load the info from the DB and even create if not existing
     *
     * @param string $string
     * @param bool $doNotCreate
     * @return int 0=fail, 1=existing, 2=new, 3=newNotCreated
     */
    public function initbystring(string $string, bool $doNotCreate=false): int {
        $ret = 0;
        $this->_id = false;
        if(!empty($string)) {
            $queryStr = "SELECT `id`,`name` FROM `".DB_PREFIX."_category`
                            WHERE `name` = '".$this->DB->real_escape_string($string)."'";

            if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $result = $query->fetch_assoc();
                    $this->_id = $result['id'];
                    $this->_data = $result;
                    $ret = 1;
                }
                else {
                    if(!$doNotCreate) {
                        $queryStr = "INSERT INTO `" . DB_PREFIX . "_category`
                                        SET `name` = '" . $this->DB->real_escape_string($string) . "'";

                        if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

                        $this->DB->query($queryStr);
                        if (!empty($this->DB->insert_id)) {
                            $this->_id = $this->DB->insert_id;
                            $this->_data['id'] = $this->_id;
                            $this->_data['name'] = $string;
                            $ret = 2;
                        }
                    }
                    else {
                        $ret = 3;
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("ERROR ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }
        return $ret;
    }

    /**
     * by given DB table id load all the info we need
     *
     * @param string $id
     * @return string
     */
    public function initbyid(string $id): string {
        $this->_id = 0;

        if(!empty($id)) {
            $queryStr = "SELECT id,name
                FROM `".DB_PREFIX."_category`
                WHERE `id` = '".$this->DB->real_escape_string($id)."'";

            if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $result = $query->fetch_assoc();
                    $this->_id = $id;
                    $this->_data = $result;
                }
            } catch (Exception $e) {
                Summoner::sysLog("ERROR ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $this->_id;
    }

    /**
     * return all or data for given key on the current loaded category
     *
     * @param string $key
     * @return string|array
     */
    public function getData(string $key=''): string|array {
        $ret = $this->_data;

        if(!empty($key) && isset($this->_data[$key])) {
            $ret = $this->_data[$key];
        }

        return $ret;
    }

    /**
     * set the relation to the given link to the loaded category
     *
     * @param string $linkid
     * @return void
     */
    public function setRelation(string $linkid): void {
        if(!empty($linkid) && !empty($this->_id)) {
            $queryStr = "INSERT IGNORE INTO `".DB_PREFIX."_categoryrelation`
                            SET `linkid` = '".$this->DB->real_escape_string($linkid)."',
                                `categoryid` = '".$this->DB->real_escape_string($this->_id)."'";

            if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $this->DB->query($queryStr);
            } catch (Exception $e) {
                Summoner::sysLog("ERROR ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }
    }

    /**
     * Return an array of any linkid related to the current loaded category
     *
     * @return array
     */
    public function getRelations(): array {
        $ret = array();

        $queryStr = "SELECT linkid 
                    FROM `".DB_PREFIX."_categoryrelation` 
                    WHERE `categoryid` = '".$this->DB->real_escape_string($this->_id)."'";

        if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    $ret[] = $result['linkid'];
                }
            }
        } catch (Exception $e) {
            Summoner::sysLog("ERROR ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * deletes the current loaded category from db
     *
     * @return boolean
     */
    public function delete(): bool {
        $ret = false;

        if(!empty($this->_id)) {
            $this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

            $queryStr = "DELETE
                    FROM `".DB_PREFIX."_categoryrelation`
                    WHERE `categoryid` = '".$this->DB->real_escape_string($this->_id)."'";
            $this->DB->query($queryStr);

            $queryStr = "DELETE
                    FROM `".DB_PREFIX."_category`
                    WHERE `id` = '".$this->DB->real_escape_string($this->_id)."'";

            if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $this->DB->query($queryStr);
                $this->DB->commit();
                $ret = true;
            } catch (Exception $e) {
                Summoner::sysLog('ERROR Failed to remove category: '.$e->getMessage());
                $this->DB->rollback();
            }
        }

        return $ret;
    }

    /**
     * Rename current loaded cat name
     *
     * @param string $newValue
     * @return void
     */
    public function rename(string $newValue): void {
        if(!empty($newValue)) {
            $queryStr = "UPDATE `".DB_PREFIX."_category`
                        SET `name` = '".$this->DB->real_escape_string($newValue)."'
                        WHERE `id` = '".$this->DB->real_escape_string($this->_id)."'";

            if(QUERY_DEBUG) Summoner::sysLog("QUERY ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $this->DB->query($queryStr);
            } catch (Exception $e) {
                Summoner::sysLog("ERROR ".__METHOD__." mysql catch: ".$e->getMessage());
            }

            $this->_data['name'] = $newValue;
        }
    }
}
