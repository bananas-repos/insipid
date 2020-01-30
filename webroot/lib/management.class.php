<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2020 Johannes Keßler
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

	const LINK_QUERY_STATUS = 2;

	const COMBINED_SELECT_VALUES = "any_value(`id`) as id,
				any_value(`link`) as link,
				any_value(`created`) as created,
				any_value(`status`) as `status`,
				any_value(`description`) as description,
				any_value(`title`) as title,
				any_value(`image`) as image,
				any_value(`hash`) as hash,
				any_value(`tag`) as tag,
				any_value(`category`) as category,
				any_value(`categoryId`) as categoryId,
				any_value(`tagId`) as tagId";

	/**
	 * the database object
	 * @var object
	 */
	private $DB;

	/**
	 * Type of links based on status to show
	 * @var bool
	 */
	private $_queryStatus = self::LINK_QUERY_STATUS;



	public function __construct($databaseConnectionObject) {
		$this->DB = $databaseConnectionObject;
	}

	/**
	 * Show private links or not
	 * @param $bool
	 */
	public function setShowPrivate($bool) {
		$this->_queryStatus = self::LINK_QUERY_STATUS;
		if($bool === true) {
			$this->_queryStatus = 1;
		}
	}

	/**
	 * Show awaiting moderation links or not
	 * @param $bool
	 */
	public function setShowAwm($bool) {
		$this->_queryStatus = self::LINK_QUERY_STATUS;
		if($bool === true) {
			$this->_queryStatus = 3;
		}
	}

	/**
	 * get all the available categories from the DB.
	 * optional limit
	 * optional stats
	 * @param bool | int $limit
	 * @param bool $stats
	 * @return array
	 */
	public function categories($limit=false, $stats=false) {
		$ret = array();
		$statsInfo = array();

		if($stats === true) {
			$queryStr = "SELECT
				COUNT(*) AS amount,
				any_value(cr.categoryid) AS categoryId
				FROM `".DB_PREFIX."_categoryrelation` AS cr, `".DB_PREFIX."_link` AS t
				WHERE cr.linkid = t.id";
			$queryStr .= " AND ".$this->_decideLinkTypeForQuery();
			$queryStr .= " GROUP BY categoryid";

			$query = $this->DB->query($queryStr);
			if(!empty($query)) {
				while($result = $query->fetch_assoc()) {
					$statsInfo[$result['categoryId']] = $result['amount'];
				}
			}
		}

		$queryStr = "SELECT
			any_value(`id`) as id,
			any_value(`name`) as name
			FROM `".DB_PREFIX."_category`
			ORDER BY `name` ASC";
		if(!empty($limit)) {
			$queryStr .= " LIMIT $limit";
		}
		$query = $this->DB->query($queryStr);
		if(!empty($query)) {
			while($result = $query->fetch_assoc()) {
				if($stats === true && isset($statsInfo[$result['id']])) {
					$ret[$result['id']] = array('name' => $result['name'], 'amount' => $statsInfo[$result['id']]);
				}
				else {
					$ret[$result['id']] = array('name' => $result['name'], 'amount' => 0);
				}
			}
		}

		return $ret;
	}

	/**
	 * get all the available tags from the DB.
	 * optional limit
	 * optional stats
	 * @param bool | int $limit
	 * @param bool $stats
	 * @return array
	 */
	public function tags($limit=false, $stats=false) {
		$ret = array();
		$statsInfo = array();

		if($stats === true) {
			$queryStr = "SELECT
				COUNT(*) AS amount,
				any_value(tr.tagid) AS tagId
				FROM `".DB_PREFIX."_tagrelation` AS tr,  `".DB_PREFIX."_link` AS t
				WHERE tr.linkid = t.id";
			$queryStr .= " AND ".$this->_decideLinkTypeForQuery();
			$queryStr .= " GROUP BY tagId";

			$query = $this->DB->query($queryStr);
			if(!empty($query)) {
				while($result = $query->fetch_assoc()) {
					$statsInfo[$result['tagId']] = $result['amount'];
				}
			}
		}

		$queryStr = "SELECT
			any_value(`id`) as id,
			any_value(`name`) as name
			FROM `".DB_PREFIX."_tag`
			ORDER BY `name` ASC";
		if(!empty($limit)) {
			$queryStr .= " LIMIT $limit";
		}
		$query = $this->DB->query($queryStr);
		if(!empty($query)) {
			while($result = $query->fetch_assoc()) {
				if($stats === true && isset($statsInfo[$result['id']])) {
					$ret[$result['id']] = array('name' => $result['name'], 'amount' => $statsInfo[$result['id']]);
				}
				else {
					$ret[$result['id']] = array('name' => $result['name'], 'amount' => 0);
				}
			}
		}

		return $ret;
	}

	/**
	 * return the latest added links
	 * @param int $limit
	 * @return array
	 */
	public function latestLinks($limit=5) {
		$ret = array();

		$queryStr = "SELECT `title`, `link` FROM `".DB_PREFIX."_link` AS t";
		$queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
		$queryStr .= " ORDER BY `created` DESC";
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
		foreach($categories as $k=>$v) {
			$latestLink = $this->latestLinkForCategory($k);
			if(!empty($latestLink)) {
				array_push($ret, array('created' => $latestLink[0]['created'], 'id' => $k, 'name' => $v['name']));
			}
		}

		$_created  = array_column($ret, 'created');
		array_multisort($_created, SORT_DESC, $ret);

		return $ret;
	}

    /**
     * find all links by given category string or id.
     * Return array sorted by creation date DESC
     * @param int $id Category ID
     * @param string $string Category as string
     * @param array $options Array with limit|offset|sort|sortDirection
     * @return array
     */
	public function linksByCategory($id, $string, $options=array()) {
		$ret = array();

		if(!isset($options['limit'])) $options['limit'] = 5;
        if(!isset($options['offset'])) $options['offset'] = false;
        if(!isset($options['sort'])) $options['sort'] = false;
        if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

		$querySelect = "SELECT ".self::COMBINED_SELECT_VALUES;
		$queryFrom = " FROM `".DB_PREFIX."_combined` AS t";
		$queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
		if(!empty($id) && is_numeric($id)) {
			$queryWhere .= " AND t.categoryId = '" . $this->DB->real_escape_string($id) . "'";
		}
		elseif(!empty($string) && is_string($string)) {
			$queryWhere .= " AND t.category = '" . $this->DB->real_escape_string($string) . "'";
		}
		else {
			return $ret;
		}

        $queryGroup = " GROUP BY t.hash";
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
		$query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryGroup.$queryOrder.$queryLimit);

		if(!empty($query) && $query->num_rows > 0) {
			while($result = $query->fetch_assoc()) {
				$linkObj = new Link($this->DB);
				$ret['results'][] = $linkObj->loadShortInfo($result['hash']);
				unset($linkObj);
			}

			$query = $this->DB->query("SELECT COUNT(DISTINCT(t.hash)) AS amount ".$queryFrom.$queryWhere);
			$result = $query->fetch_assoc();
			$ret['amount'] = $result['amount'];
		}

		return $ret;
	}

    /**
     * find all links by given tag string or id.
     * Return array sorted by creation date DESC
     * @param int $id Tag id
     * @param string $string Tag as string
     * @param array $options Array with limit|offset|sort|sortDirection
     * @return array
     */
	public function linksByTag($id, $string, $options=array()) {
		$ret = array();

        if(!isset($options['limit'])) $options['limit'] = 5;
        if(!isset($options['offset'])) $options['offset'] = false;
        if(!isset($options['sort'])) $options['sort'] = false;
        if(!isset($options['sortDirection'])) $options['sortDirection'] = false;

		$querySelect = "SELECT ".self::COMBINED_SELECT_VALUES;
		$queryFrom = " FROM `".DB_PREFIX."_combined` AS t";
		$queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
		if(!empty($id) && is_numeric($id)) {
			$queryWhere .= " AND t.tagId = '" . $this->DB->real_escape_string($id) . "'";
		}
		elseif(!empty($string) && is_string($string)) {
			$queryWhere .= " AND t.tag = '" . $this->DB->real_escape_string($string) . "'";
		}
		else {
			return $ret;
		}

        $queryGroup = " GROUP BY t.hash";
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
        $query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryGroup.$queryOrder.$queryLimit);

		if(!empty($query) && $query->num_rows > 0) {
			while($result = $query->fetch_assoc()) {
				$linkObj = new Link($this->DB);
				$ret['results'][] = $linkObj->loadShortInfo($result['hash']);
				unset($linkObj);
			}

			$query = $this->DB->query("SELECT COUNT(DISTINCT(t.hash)) AS amount ".$queryFrom.$queryWhere);
			$result = $query->fetch_assoc();
			$ret['amount'] = $result['amount'];
		}

		return $ret;
	}

	/**
	 * return all links and Info we have from the combined view
	 * @param bool | int $limit
	 * @param bool $offset
	 * @return array
	 */
	public function links($limit=10,$offset=false) {
		$ret = array();

		$querySelect = "SELECT `hash`";
		$queryFrom = " FROM `".DB_PREFIX."_link` AS t";
		$queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
		$queryOrder = " ORDER BY `created` DESC";
		$queryLimit = "";
		if(!empty($limit)) {
			$queryLimit = " LIMIT $limit";
			if($offset !== false) {
				$queryLimit .= " OFFSET $offset";
			}
		}
		$query = $this->DB->query($querySelect.$queryFrom.$queryWhere.$queryOrder.$queryLimit);
		if(!empty($query) && $query->num_rows > 0) {
			while($result = $query->fetch_assoc()) {
				$linkObj = new Link($this->DB);
				$ret['results'][] = $linkObj->loadShortInfo($result['hash']);
				unset($linkObj);
			}

			$query = $this->DB->query("SELECT COUNT(t.hash) AS amount ".$queryFrom.$queryWhere);
			$result = $query->fetch_assoc();
			$ret['amount'] = $result['amount'];
		}

		return $ret;
	}

	/**
	 * return the latest added link for given category id
	 * @param int $categoryid
	 * @return array
	 */
	public function latestLinkForCategory($categoryid) {
		$ret = array();

		if(!empty($categoryid) && is_numeric($categoryid)) {
			$queryStr = "SELECT ".self::COMBINED_SELECT_VALUES." 
			FROM `".DB_PREFIX."_combined` AS t";
			$queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
			$queryStr .= " AND t.categoryId = '" . $this->DB->real_escape_string($categoryid) . "'
			ORDER BY t.created DESC
			LIMIT 1";
			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$ret = $query->fetch_all(MYSQLI_ASSOC);
			}
		}
		return $ret;
	}

	/**
	 * Search for the given url in the links table
	 * @param $url
	 * @return mixed
	 */
	public function searchForLinkByURL($url) {
		$ret = false;

		if(!empty($url)) {
			$queryStr = "SELECT * FROM `".DB_PREFIX."_link` AS t";
			$queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();
			$queryStr .= " AND t.link = '".$this->DB->real_escape_string($url)."'";

			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$ret = $query->fetch_all(MYSQLI_ASSOC);
			}
		}

		return $ret;
	}

	/**
	 * search for given searchstring in the search data of the links
	 * @param $searchStr
	 * @return mixed
	 */
	public function searchForLinkBySearchData($searchStr) {
		$ret = false;

		if(!empty($searchStr)) {
			$queryStr = "SELECT *,
				MATCH (`search`) AGAINST ('".$this->DB->real_escape_string($searchStr)."' IN BOOLEAN MODE) AS score
				FROM `".DB_PREFIX."_link` AS t
				WHERE MATCH (`search`) AGAINST ('".$this->DB->real_escape_string($searchStr)."' IN BOOLEAN MODE)";
			$queryStr .= " AND ".$this->_decideLinkTypeForQuery();
			$queryStr .= " ORDER BY score DESC";

			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$ret = $query->fetch_all(MYSQLI_ASSOC);
			}
		}

		return $ret;
	}

	/**
	 * amount of links in the DB. Status 1 and 2 only
	 * @return int
	 */
	public function linkAmount() {
		$ret = 0;

		$queryStr = "SELECT COUNT(*) AS amount 
						FROM `".DB_PREFIX."_link` AS t";
		$queryStr .= " WHERE ".$this->_decideLinkTypeForQuery();

		$query = $this->DB->query($queryStr);
		if(!empty($query) && $query->num_rows > 0) {
			$result = $query->fetch_assoc();
			$ret = $result['amount'];
		}

		return $ret;
	}


	/**
	 * amount of tags
	 * @return int
	 */
	public function tagAmount() {
		$ret = 0;

		$queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_tag`";

		$query = $this->DB->query($queryStr);
		if(!empty($query) && $query->num_rows > 0) {
			$result = $query->fetch_assoc();
			$ret = $result['amount'];
		}

		return $ret;
	}

	/**
	 * amount of categories
	 * @return int
	 */
	public function categoryAmount() {
		$ret = 0;

		$queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_category`";

		$query = $this->DB->query($queryStr);
		if(!empty($query) && $query->num_rows > 0) {
			$result = $query->fetch_assoc();
			$ret = $result['amount'];
		}

		return $ret;
	}

	/**
	 * Amount of links need moderation
	 * @return int
	 */
	public function moderationAmount() {
		$ret = 0;

		$queryStr = "SELECT COUNT(*) AS amount FROM `".DB_PREFIX."_link`";
		$queryStr .= " WHERE `status` = 3";

		$query = $this->DB->query($queryStr);
		if(!empty($query) && $query->num_rows > 0) {
			$result = $query->fetch_assoc();
			$ret = $result['amount'];
		}

		return $ret;
	}

    /**
     * get the used disk space for local image storage
     * @return false|int
     */
	public function storageAmount() {
		$ret = 0;

		$_storageFolder = ABSOLUTE_PATH.'/'.LOCAL_STORAGE;

		if(file_exists($_storageFolder) && is_readable($_storageFolder)) {
			$ret = Summoner::folderSize($_storageFolder);
		}

		return $ret;
	}

    /**
     * empties the local storage directory
     * @return bool
     */
	public function clearLocalStorage() {
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
	 * @param String $hash  Link hash
	 * @param bool $fullInfo Load all the info we have
	 * @param bool $withObject An array with data and the link obj itself
	 * @return array|mixed
	 */
	public function loadLink($hash,$fullInfo=true,$withObject=false) {
		$ret = array();

		if (!empty($hash)) {

			$querySelect = "SELECT `hash`";
			$queryFrom = " FROM `".DB_PREFIX."_link` AS t";
			$queryWhere = " WHERE ".$this->_decideLinkTypeForQuery();
			$queryWhere .= " AND t.hash = '".$this->DB->real_escape_string($hash)."'";

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
		}

		return $ret;
	}

	/**
	 * Delete link by given hash
	 * @param $hash
	 * @return bool
	 */
	public function deleteLink($hash) {
		$ret = false;

		if (!empty($hash)) {
			$linkData = $this->loadLink($hash,false,true);
			if(!empty($linkData)) {
				$linkData['obj']->deleteRelations();

				$queryStr = "DELETE FROM `" . DB_PREFIX . "_link` 
						WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";
				$query = $this->DB->query($queryStr);
				if (!empty($query)) {
					$ret = true;
				}
			}
		}

		return $ret;
	}

	/**
	 * Export given link for download and later import
	 * @param $hash
	 * @param bool $linkObj Use already existing link obj
	 * @return bool
	 */
	public function exportLinkData($hash,$linkObj=false) {
		$ret = false;

		if (!empty($hash)) {
			$linkData = $this->loadLink($hash, true, true);
			if (!empty($linkData)) {
				$data = $linkData;
			}
		}
		elseif(!empty($linkObj) && is_a($linkObj,'Link')) {
			$data = $linkObj->getData();
		}

		if(!empty($data) && isset($data['link'])) {

			require_once 'lib/import-export.class.php';
			$ImEx = new ImportExport();
			$ret = $ImEx->createSingleLinkExportXML($data);
		}

		return $ret;
	}

	/**
	 * for simpler management we have the search data in a separate column
	 * it is not fancy or even technical nice but it damn works
	 */
	public function updateSearchIndex() {
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

				$searchStr = $l['title'];
				$searchStr .= ' '.$l['description'];
				$searchStr .= ' '.implode(' ',$l['tags']);
                $searchStr .= ' '.implode(' ',$l['categories']);
                $searchStr = trim($searchStr);
                $searchStr = strtolower($searchStr);

				# now update the search string
				$queryStr = "UPDATE `".DB_PREFIX."_link`
								SET `search` = '".$this->DB->real_escape_string($searchStr)."'
								WHERE `hash` = '".$this->DB->real_escape_string($link['hash'])."'";

				$this->DB->query($queryStr);

				unset($LinkObj,$l,$searchStr,$t,$c,$queryStr);
			}

			$ret = true;
		}

		return $ret;
	}

	/**
	 * Return the query string for the correct status type
	 * @return string
	 */
	private function _decideLinkTypeForQuery() {
		switch ($this->_queryStatus) {
			case 1:
				$ret = "t.status IN (2,1)";
				break;
			case 3:
				$ret = "t.status = 3";
				break;

			default:
				$ret = "t.status = 2";
		}
		return $ret;
	}
}

