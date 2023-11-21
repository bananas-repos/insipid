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

/**
 * Class Management
 */
class Management {

    /**
     * Default value
     */
    const LINK_QUERY_STATUS = 2;

    /**
     * the database object
     *
     * @var mysqli
     */
    private mysqli $DB;

    /**
     * Type of links based on status to show
     *
     * @var int
     */
    private int $_queryStatus = self::LINK_QUERY_STATUS;

    /**
     * @var array Store already loaded categories to avoid unneeded queries
     */
    private array $_categories;

    /**
     * Management constructor.
     *
     * @param mysqli $databaseConnectionObject
     * @return void
     */
    public function __construct(mysqli $databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * Show private links or not
     *
     * @param boolean $bool
     * @return void
     */
    public function setShowPrivate(bool $bool): void {
        $this->_queryStatus = self::LINK_QUERY_STATUS;
        if($bool === true) {
            $this->_queryStatus = 1;
        }
    }

    /**
     * Show awaiting moderation links or not
     *
     * @param boolean $bool
     * @return void
     */
    public function setShowAwm(bool $bool): void {
        $this->_queryStatus = self::LINK_QUERY_STATUS;
        if($bool === true) {
            $this->_queryStatus = 3;
        }
    }

    /**
     * get all the available categories from the DB.
     * optional limit
     * optional stats
     *
     * @param string $limit
     * @param bool $stats
     * @return array
     */
    public function categories(string $limit="0", bool $stats=false): array {
        $ret = array();
        $statsInfo = array();

        if(!empty($this->_categories)) return $this->_categories;

        if($stats === true) {
            $queryStr = "SELECT
                COUNT(*) AS amount,
                cr.categoryid AS categoryId
                FROM `".DB_PREFIX."_categoryrelation` AS cr, 
                    `".DB_PREFIX."_link` AS t
                WHERE cr.linkid = t.id";
            $queryStr .= " AND ".$this->_decideLinkTypeForQuery();
            $queryStr .= " GROUP BY categoryid";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    while($result = $query->fetch_assoc()) {
                        $statsInfo[$result['categoryId']] = $result['amount'];
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        $queryStr = "SELECT `id`, `name`
            FROM `".DB_PREFIX."_category`
            ORDER BY `name` ASC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    if($stats === true && isset($statsInfo[$result['id']])) {
                        $ret[$result['id']] = array('name' => $result['name'], 'amount' => $statsInfo[$result['id']]);
                    }
                    else {
                        $ret[$result['id']] = array('name' => $result['name'], 'amount' => 0);
                    }
                }
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        $this->_categories = $ret;
        return $ret;
    }

    /**
     * get all the available tags from the DB.
     * optional limit
     * optional stats
     *
     * @param string $limit
     * @param bool $stats
     * @return array
     */
    public function tags(string $limit="0", bool $stats=false): array {
        $ret = array();
        $statsInfo = array();

        if($stats === true) {
            $queryStr = "SELECT COUNT(*) AS amount,
                tr.tagid AS tagId
                FROM `".DB_PREFIX."_tagrelation` AS tr,  `".DB_PREFIX."_link` AS t
                WHERE tr.linkid = t.id";
            $queryStr .= " AND ".$this->_decideLinkTypeForQuery();
            $queryStr .= " GROUP BY tagId";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    while($result = $query->fetch_assoc()) {
                        $statsInfo[$result['tagId']] = $result['amount'];
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        $queryStr = "SELECT `id`, `name`
            FROM `".DB_PREFIX."_tag`
            ORDER BY `name` ASC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    if($stats === true && isset($statsInfo[$result['id']])) {
                        $ret[$result['id']] = array('name' => $result['name'], 'amount' => $statsInfo[$result['id']]);
                    }
                    else {
                        $ret[$result['id']] = array('name' => $result['name'], 'amount' => 0);
                    }
                }
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * return the latest added links
     *
     * @param string $limit
     * @return array
     */
    public function latestLinks(string $limit="5"): array {
        $ret = array();

        $queryStr = "SELECT `hash` FROM `".DB_PREFIX."_link` AS t";
        $queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
        $queryStr .= " ORDER BY `created` DESC";
        if(!empty($limit)) {
            $queryStr .= " LIMIT $limit";
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    $linkObj = new Link($this->DB);
                    $ret[] = $linkObj->loadShortInfo($result['hash']);

                }
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * Return a random entry from link table.
     * Slow but does the trick for now. If there is way more entries
     * re-think this solution
     *
     * @param String $limit
     * @return array
     */
    public function randomLink(string $limit="1"): array {
        $ret = array();

        $amount = $this->linkAmount();
        $offset = rand(0, $amount-1);

        $queryStr = "SELECT `title`, `link`, `hash` FROM `".DB_PREFIX."_link` AS t";
        $queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
        $queryStr .= " LIMIT $offset, $limit";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $ret = $query->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * Get a random category
     *
     * @param string $limit
     * @return array
     */
    public function randomCategory(string $limit="1"): array {
        $ret = array();

        $amount = $this->categoryAmount();
        $offset = rand(0, $amount-1);

        $queryStr = "SELECT `id`, `name` FROM `".DB_PREFIX."_category`";
        $queryStr .= " LIMIT $offset, $limit";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $ret = $query->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * Get a random tag
     *
     * @param string $limit
     * @return array
     */
    public function randomTag(string $limit="1"): array {
        $ret = array();

        $amount = $this->tagAmount();
        $offset = rand(0, $amount-1);

        $queryStr = "SELECT `id`, `name` FROM `".DB_PREFIX."_tag`";
        $queryStr .= " LIMIT $offset, $limit";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $ret = $query->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * get all the categories ordered by link added date
     *
     * @return array
     */
    public function categoriesByDateAdded(): array {
        $ret = array();

        $categories = $this->categories();
        foreach($categories as $k=>$v) {
            $latestLink = $this->latestLinkForCategory($k);
            if(!empty($latestLink)) {
                $ret[] = array('created' => $latestLink[0]['created'], 'id' => $k, 'name' => $v['name']);
            }
        }

        $_created  = array_column($ret, 'created');
        array_multisort($_created, SORT_DESC, $ret);

        return $ret;
    }

    /**
     * find all links by given category string or id.
     * Return array sorted by creation date DESC
     *
     * @param string $id Category ID
     * @param array $options Array with limit|offset|sort|sortDirection
     * @return array
     */
    public function linksByCategory(string $id, array $options=array()): array {
        $ret = array();

        if(!isset($options['limit'])) $options['limit'] = 5;
        if(!isset($options['offset'])) $options['offset'] = false;
        if(!isset($options['sort'])) $options['sort'] = false;
        if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

        $querySelect = "SELECT `id`, `link`, `created`, `status`, `title`, `hash`, `description`, `image`";
        $queryFrom = " FROM `".DB_PREFIX."_link` AS t
                        LEFT JOIN insipid_categoryrelation AS cr ON cr.linkid = t.id";
        $queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
        if(!empty($id) && is_numeric($id)) {
            $queryWhere .= " AND cr.categoryId = '" . $this->DB->real_escape_string($id) . "'";
        }
        else {
            return $ret;
        }

        $queryOrder = " ORDER BY";
        if(!empty($options['sort'])) {
            $queryOrder .= ' t.'.$options['sort'];
        }
        else {
            $queryOrder .= " t.created";
        }
        if(!empty($options['sortDirection'])) {
            $queryOrder .= ' '.$options['sortDirection'];
        }
        else {
            $queryOrder .= " DESC";
        }

        $queryLimit = '';
        # this allows the set the limit to false
        if(!empty($options['limit'])) {
            $queryLimit .= " LIMIT ".$options['limit'];
            # offset can be 0
            if($options['offset'] !== false) {
                $queryLimit .= " OFFSET ".$options['offset'];
            }
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit));

        try {
            $query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    $linkObj = new Link($this->DB);
                    $ret['results'][] = $linkObj->loadFromDataShortInfo($result);
                    unset($linkObj);
                }

                $query = $this->DB->query("SELECT COUNT(DISTINCT(t.hash)) AS amount ".$queryFrom.$queryWhere);
                $result = $query->fetch_assoc();
                $ret['amount'] = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * find all links by given tag string or id.
     * Return array sorted by creation date DESC
     *
     * @param string $id Tag id
     * @param array $options Array with limit|offset|sort|sortDirection
     * @return array
     */
    public function linksByTag(string $id, array $options=array()): array {
        $ret = array();

        if(!isset($options['limit'])) $options['limit'] = 5;
        if(!isset($options['offset'])) $options['offset'] = false;
        if(!isset($options['sort'])) $options['sort'] = false;
        if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

        $querySelect = "SELECT `id`, `link`, `created`, `status`, `title`, `hash`, `description`, `image`";
        $queryFrom = " FROM `".DB_PREFIX."_link` AS t
                        LEFT JOIN insipid_tagrelation AS tr ON tr.linkid = t.id";
        $queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
        if(!empty($id) && is_numeric($id)) {
            $queryWhere .= " AND tr.tagId = '".$this->DB->real_escape_string($id)."'";
        }
        else {
            return $ret;
        }

        $queryOrder = " ORDER BY";
        if(!empty($options['sort'])) {
            $queryOrder .= ' t.'.$options['sort'];
        }
        else {
            $queryOrder .= " t.created";
        }
        if(!empty($options['sortDirection'])) {
            $queryOrder .= ' '.$options['sortDirection'];
        }
        else {
            $queryOrder .= " DESC";
        }

        $queryLimit = '';
        # this allows the set the limit to false
        if(!empty($options['limit'])) {
            $queryLimit .= " LIMIT ".$options['limit'];
            # offset can be 0
            if($options['offset'] !== false) {
                $queryLimit .= " OFFSET ".$options['offset'];
            }
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit));

        try {
            $query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    $linkObj = new Link($this->DB);
                    $ret['results'][] = $linkObj->loadFromDataShortInfo($result);
                    unset($linkObj);
                }

                if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryFrom.$queryWhere));

                $query = $this->DB->query("SELECT COUNT(DISTINCT(t.hash)) AS amount ".$queryFrom.$queryWhere);
                $result = $query->fetch_assoc();
                $ret['amount'] = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * return all links and Info we have from the combined view
     *
     * @param array $options
     * @return array
     */
    public function links(array $options=array()): array {
        $ret = array();

        if(!isset($options['limit'])) $options['limit'] = 5;
        if(!isset($options['offset'])) $options['offset'] = false;
        if(!isset($options['sort'])) $options['sort'] = false;
        if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

        $querySelect = "SELECT `hash`";
        $queryFrom = " FROM `".DB_PREFIX."_link` AS t";
        $queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();

        $queryOrder = " ORDER BY";
        if(!empty($options['sort'])) {
            $queryOrder .= ' t.'.$options['sort'];
        }
        else {
            $queryOrder .= " t.created";
        }
        if(!empty($options['sortDirection'])) {
            $queryOrder .= ' '.$options['sortDirection'];
        }
        else {
            $queryOrder .= " DESC";
        }

        $queryLimit = '';
        # this allows the set the limit to false
        if(!empty($options['limit'])) {
            $queryLimit .= " LIMIT ".$options['limit'];
            # offset can be 0
            if($options['offset'] !== false) {
                $queryLimit .= " OFFSET ".$options['offset'];
            }
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit));

        try {
            $query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    $linkObj = new Link($this->DB);
                    $ret['results'][] = $linkObj->loadShortInfo($result['hash']);
                    unset($linkObj);
                }

                if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryFrom.$queryWhere));

                $query = $this->DB->query("SELECT COUNT(t.hash) AS amount ".$queryFrom.$queryWhere);
                $result = $query->fetch_assoc();
                $ret['amount'] = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * return the latest added link for given category id
     *
     * @param string $categoryid
     * @return array
     */
    public function latestLinkForCategory(string $categoryid): array {
        $ret = array();

        if(!empty($categoryid) && is_numeric($categoryid)) {
            $queryStr = "SELECT `id`, `link`, `created`, `status`, `description`, `title`, `image`, `hash`,
                        `tag`, `category`, `categoryId`, `tagId`
                        FROM `".DB_PREFIX."_combined` AS t";
            $queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
            $queryStr .= " AND t.categoryId = '" . $this->DB->real_escape_string($categoryid) . "'
                            ORDER BY t.created DESC
                            LIMIT 1";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $ret = $query->fetch_all(MYSQLI_ASSOC);
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }
        return $ret;
    }

    /**
     * Search for the given url in the links table
     *
     * @param string $url
     * @return array
     */
    public function searchForLinkByURL(string $url): array {
        $ret = array();

        if(!empty($url)) {
            $queryStr = "SELECT `id`, `link`, `title`, `hash` 
                            FROM `".DB_PREFIX."_link` AS t";
            $queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
            $queryStr .= " AND t.link = '".$this->DB->real_escape_string($url)."'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $ret = $query->fetch_all(MYSQLI_ASSOC);
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * search for given searchstring in the search data of the links
     *
     * @param string $searchStr
     * @return array
     */
    public function searchForLinkBySearchData(string $searchStr): array {
        $ret = array();

        if(!empty($searchStr)) {
            $queryStr = "SELECT `id`, `link`, `title`, `hash`,
                MATCH (`search`) AGAINST ('".$this->DB->real_escape_string($searchStr)."' IN BOOLEAN MODE) AS score
                FROM `".DB_PREFIX."_link` AS t
                WHERE MATCH (`search`) AGAINST ('".$this->DB->real_escape_string($searchStr)."' IN BOOLEAN MODE)";
            $queryStr .= " AND ".$this->_decideLinkTypeForQuery();
            $queryStr .= " ORDER BY score DESC";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $ret = $query->fetch_all(MYSQLI_ASSOC);
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * amount of links in the DB. Status 1 and 2 only
     *
     * @return string
     */
    public function linkAmount(): string {
        $ret = 0;

        $queryStr = "SELECT COUNT(*) AS amount 
                        FROM `".DB_PREFIX."_link` AS t";
        $queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * amount of tags
     *
     * @return string
     */
    public function tagAmount(): string {
        $ret = 0;

        $queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_tag`";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * amount of categories
     *
     * @return string
     */
    public function categoryAmount(): string {
        $ret = 0;

        $queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_category`";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * Amount of links need moderation
     *
     * @return string
     */
    public function moderationAmount(): string {
        $ret = 0;

        $queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_link`";
        $queryStr .= " WHERE `status` = 3";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                $result = $query->fetch_assoc();
                $ret = $result['amount'];
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * get the used disk space for local image storage
     *
     * @return int
     */
    public function storageAmount(): int {
        $ret = 0;

        $_storageFolder = ABSOLUTE_PATH.'/'.LOCAL_STORAGE;

        if(file_exists($_storageFolder) && is_readable($_storageFolder)) {
            $ret = Summoner::folderSize($_storageFolder);
        }

        return $ret;
    }

    /**
     * empties the local storage directory
     *
     * @return bool
     */
    public function clearLocalStorage(): bool {
        $ret = false;

        $_storageFolder = ABSOLUTE_PATH.'/'.LOCAL_STORAGE;
        if(file_exists($_storageFolder) && is_writable($_storageFolder)) {
            $ret = Summoner::recursive_remove_directory($_storageFolder,true);
        }

        return $ret;
    }


    /**
     * Load link by given hash. Do not use Link class directly.
     * Otherwise the authentication will be ignored.
     *
     * @param String $hash Link hash
     * @param bool $fullInfo Load all the info we have
     * @param bool $withObject An array with data and the link obj itself
     * @return array
     */
    public function loadLink(string $hash, bool $fullInfo=true, bool $withObject=false): array {
        $ret = array();

        if (!empty($hash)) {

            $querySelect = "SELECT `hash`";
            $queryFrom = " FROM `".DB_PREFIX."_link` AS t";
            $queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
            $queryWhere .= " AND t.hash = '".$this->DB->real_escape_string($hash)."'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($querySelect.$queryFrom.$queryWhere));

            try {
                $query = $this->DB->query($querySelect.$queryFrom.$queryWhere);
                if (!empty($query) && $query->num_rows == 1) {
                    $linkObj = new Link($this->DB);
                    if($fullInfo === true) {
                        $ret = $linkObj->load($hash);
                    }
                    else {
                        $ret = $linkObj->loadShortInfo($hash);
                    }

                    if($withObject === true) {
                        $ret = array(
                            'data' => $ret,
                            'obj' => $linkObj
                        );
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * Delete link by given hash
     *
     * @param string $hash
     * @return bool
     */
    public function deleteLink(string $hash): bool {
        $ret = false;

        if (!empty($hash)) {
            $linkData = $this->loadLink($hash,false,true);
            if(!empty($linkData)) {
                $linkData['obj']->deleteRelations();

                $queryStr = "DELETE FROM `" . DB_PREFIX . "_link` 
                        WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";

                if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

                try {
                    $query = $this->DB->query($queryStr);
                    if (!empty($query)) {
                        $ret = true;
                    }
                } catch (Exception $e) {
                    Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
                }
            }
        }

        return $ret;
    }

    /**
     * Export given link for download as a xml file
     *
     * @param string $hash
     * @param Link|null $linkObj Use already existing link obj
     * @return string
     */
    public function exportLinkData(string $hash, Link $linkObj=null): string {
        $ret = '';

        if(DEBUG) {
            Summoner::sysLog("DEBUG Start to export link with hash $hash");
        }

        if (!empty($hash)) {
            $linkData = $this->loadLink($hash, true, true);
            if (!empty($linkData)) {
                $data = $linkData;
            } elseif(DEBUG) {
                Summoner::sysLog("ERROR Could not load link with $hash");
            }
        }
        elseif(!empty($linkObj) && is_a($linkObj,'Link')) {
            $data = $linkObj->getData();
        }

        if(!empty($data) && isset($data['link'])) {
            if(DEBUG) {
                Summoner::sysLog("DEBUG Using data: ".Summoner::cleanForLog($data));
            }
            require_once 'lib/import-export.class.php';
            $ImEx = new ImportExport();
            $ret = $ImEx->createSingleLinkExportXML($data);
        } elseif(DEBUG) {
            Summoner::sysLog("ERROR Missing link data for hash $hash");
        }

        return $ret;
    }

    /**
     * for simpler management we have the search data in a separate column
     * it is not fancy or even technical nice but it damn works
     *
     * @return boolean
     */
    public function updateSearchIndex(): bool {
        $ret = false;

        $allLinks = array();
        $queryStr = "SELECT hash FROM `".DB_PREFIX."_link`";
        $query = $this->DB->query($queryStr);
        if(!empty($query) && $query->num_rows > 0) {
            $allLinks = $query->fetch_all(MYSQLI_ASSOC);
        }

        if(!empty($allLinks)) {
            foreach($allLinks as $link) {
                $LinkObj = new Link($this->DB);
                $l = $LinkObj->load($link['hash']);

                $_t = parse_url($l['link']);
                $searchStr = $l['title'];
                $searchStr .= ' '.$l['description'];
                $searchStr .= ' '.implode(' ',$l['tags']);
                $searchStr .= ' '.implode(' ',$l['categories']);
                $searchStr .= ' '.$_t['host'];
                if(isset($_t['path'])) {
                    $searchStr .= ' '.implode(' ',explode('/',$_t['path']));
                }
                $searchStr = trim($searchStr);
                $searchStr = strtolower($searchStr);

                # now update the search string
                $queryStr = "UPDATE `".DB_PREFIX."_link`
                                SET `search` = '".$this->DB->real_escape_string($searchStr)."'
                                WHERE `hash` = '".$this->DB->real_escape_string($link['hash'])."'";

                if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

                try {
                    $this->DB->query($queryStr);
                } catch (Exception $e) {
                    Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
                }

                unset($LinkObj,$l,$searchStr,$_t,$queryStr);
            }

            $ret = true;
        }

        return $ret;
    }

    /**
     * process the given xml file. Based on the export file
     * options are overwrite => true|false
     *
     * @param array $file
     * @param array $options
     * @return array
     */
    public function processImportFile(array $file, array $options): array {
        $ret = array(
            'status' => 'error',
            'message' => 'Processing error'
        );

        $links = array();
        require_once 'lib/import-export.class.php';
        $ImEx = new ImportExport();
        try {
            $ImEx->loadImportFile($file);
            $links = $ImEx->parseImportFile();
        }
        catch (Exception $e) {
            $ret['message'] = $e->getMessage();
        }

        $_existing = 0;
        $_new = 0;
        if(!empty($links)) {
            $_amount = count($links);
            foreach($links as $linkToImport) {
                $do = false;

                if($this->_linkExistsById($linkToImport['id'])) {
                    if(isset($options['overwrite']) && $options['overwrite'] === true) {
                        $linkObj = new Link($this->DB);
                        $linkObj->load($linkToImport['hash']);
                        $do = $linkObj->update($linkToImport);
                    }
                    $_existing++;
                }
                else {
                    $linkObj = new Link($this->DB);

                    $this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
                    try {
                        $do = $linkObj->create(array(
                            'hash' => $linkToImport['hash'],
                            'link' => $linkToImport['link'],
                            'status' => $linkToImport['private'],
                            'description' => $linkToImport['description'],
                            'title' => $linkToImport['title'],
                            'image' => $linkToImport['image']
                        ), true);
                    } catch (Exception $e) {
                        continue;
                    }

                    if(!empty($do)) {

                        $linkToImport['catArr'] = Summoner::prepareTagOrCategoryStr($linkToImport['category']);
                        $linkToImport['tagArr'] = Summoner::prepareTagOrCategoryStr($linkToImport['tag']);

                        if(!empty($linkToImport['catArr'])) {
                            foreach($linkToImport['catArr'] as $c) {
                                $catObj = new Category($this->DB);
                                $catObj->initbystring($c);
                                $catObj->setRelation($do);

                                unset($catObj);
                            }
                        }
                        if(!empty($linkToImport['tagArr'])) {
                            foreach($linkToImport['tagArr'] as $t) {
                                $tagObj = new Tag($this->DB);
                                $tagObj->initbystring($t);
                                $tagObj->setRelation($do);

                                unset($tagObj);
                            }
                        }

                        $this->DB->commit();

                        $this->updateSearchIndex();
                    }
                    else {
                        $this->DB->rollback();
                    }
                    $_new++;
                }
            }
            if(isset($options['overwrite']) && $options['overwrite'] === true) {
                $_msg = "Found $_amount link(s) to import. Overwritten $_existing existing and imported $_new new one(s).";
            }
            else {
                $_msg = "Found $_amount link(s) to import. Skipped $_existing existing and imported $_new new one(s).";
            }
            $ret = array(
                'status' => 'success',
                'message' => $_msg
            );
        }

        return $ret;
    }

    /**
     * Top 5 combinations of either tag or category
     *
     * array(
     *    array(
     *        amount => number
     *        rel => array(rel, name)
     *    )
     * )
     *
     * @param string $type
     * @return array
     */
    public function linkRelationStats(string $type='tag'): array {
        $ret = array();

        // build the digit string which describes the tag/cat combination
        $_relCombination = array();
        if($type == 'category') {
            $queryStr = "SELECT `linkid`, `categoryid` AS rel
                        FROM `".DB_PREFIX."_categoryrelation`
                        ORDER BY `linkid`, rel ASC";
        } else {
            $queryStr = "SELECT `linkid`, `tagid` AS rel
                        FROM `".DB_PREFIX."_tagrelation`
                        ORDER BY `linkid`, rel ASC";
        }

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $query = $this->DB->query($queryStr);
            if(!empty($query) && $query->num_rows > 0) {
                while($result = $query->fetch_assoc()) {
                    if(isset($_relCombination[$result['linkid']])) {
                        $_relCombination[$result['linkid']] .= ",".$result['rel'];
                    } else {
                        // https://www.php.net/manual/en/language.types.array.php "Strings containing valid decimal ints ... will be cast to the int type
                        // if not not done the arsort results are messed up
                        $_relCombination[$result['linkid']] = "0".$result['rel'];
                    }
                }
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        // now count the unique digit strings
        $_relCombination_amount = array();
        if(!empty($_relCombination)) {
            foreach($_relCombination as $k=>$v) {
                if(isset($_relCombination_amount[$v])) {
                    $_relCombination_amount[$v]++;
                } else {
                    $_relCombination_amount[$v] = 1;
                }
            }
        }

        // now sort and return top 5 combinations
        // also resolve tag/cat name
        if(!empty($_relCombination_amount)) {
            arsort($_relCombination_amount);
            $_top5 = array_splice($_relCombination_amount,0,5);

            foreach($_top5 as $k=>$v) {
                $_t = array();
                if($k[0] === "0") {
                    $k = substr($k,1);
                }

                $_t['amount'] = $v;
                $_rel = explode(",",$k);

                $_existingRelInfo = array(); // avoid duplicate queries
                foreach($_rel as $t) {
                    if(!isset($_existingRelInfo[$t])) {
                        if($type == 'category') {
                            $queryStr = "SELECT `name` FROM `".DB_PREFIX."_category`
                                    WHERE `id` = '".$this->DB->real_escape_string($t)."'";
                        } else {
                            $queryStr = "SELECT `name` FROM `".DB_PREFIX."_tag`
                                    WHERE `id` = '".$this->DB->real_escape_string($t)."'";
                        }

                        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

                        try {
                            $query = $this->DB->query($queryStr);
                            if(!empty($query) && $query->num_rows > 0) {
                                $relinfo = $query->fetch_assoc();
                                $_existingRelInfo[$t] = $relinfo['name'];
                                $_t['rel'][$t] = $relinfo['name'];
                            }
                        } catch (Exception $e) {
                            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
                        }
                    } else {
                        $_t['rel'][$t] = $_existingRelInfo[$t];
                    }
                }
                $ret[] = $_t;
            }

        }

        return $ret;
    }

    /**
     * Return the query string for the correct status type
     *
     * @return string
     */
    private function _decideLinkTypeForQuery(): string {
        return match ($this->_queryStatus) {
            1 => "t.status IN (2,1)",
            3 => "t.status = 3",
            default => "t.status = 2",
        };
    }

    /**
     * Check if given id (not hash) exists in link database
     *
     * @param string $id
     * @return bool
     */
    private function _linkExistsById(string $id): bool {
        $ret = false;

        if(!empty($id)) {
            $queryStr = "SELECT `id`
                            FROM `" . DB_PREFIX . "_link`
                            WHERE `id` = '" . $this->DB->real_escape_string($id) . "'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if(!empty($query) && $query->num_rows > 0) {
                    $ret = true;
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $ret;
    }
}
