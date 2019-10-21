<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2019 Johannes Keßler
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

	private $_showPrivate = false;

	protected $COMBINED_SELECT_VALUES = "any_value(`id`) as id,
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

	public function __construct($databaseConnectionObject) {
		$this->DB = $databaseConnectionObject;
	}

	/**
	 * Show private links or not
	 * @param $bool
	 */
	public function setShowPrivate($bool) {
		if(is_bool($bool)) {
			$this->_showPrivate = $bool;
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
				COUNT(*) as amount,
				any_value(cr.categoryid) as categoryId
				FROM `".DB_PREFIX."_categoryrelation` AS cr, `".DB_PREFIX."_link` AS l
				WHERE cr.linkid = l.id";
			if($this->_showPrivate === true) {
				$queryStr .= " AND l.status IN (2,1)";
			}
			else {
				$queryStr .= " AND l.status = 2";
			}
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
				COUNT(*) as amount,
				any_value(tr.tagid) as tagId
				FROM `".DB_PREFIX."_tagrelation` AS tr,  `".DB_PREFIX."_link` AS l
				WHERE tr.linkid = l.id";
			if($this->_showPrivate === true) {
				$queryStr .= " AND l.status IN (2,1)";
			}
			else {
				$queryStr .= " AND l.status = 2";
			}
			$queryStr .= "GROUP BY tagId";

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

		$queryStr = "SELECT `title`, `link` FROM `".DB_PREFIX."_link`";
		if($this->_showPrivate === true) {
			$queryStr .= " WHERE `status` IN (2,1)";
		}
		else {
			$queryStr .= " WHERE `status` = 2";
		}
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
	 * @param int $id
	 * @param string $string
	 * @param int $limit
	 * @param bool $offset
	 * @return array
	 */
	public function linksByCategory($id, $string, $limit=5, $offset=false) {
		$ret = array();

		$querySelect = "SELECT ".$this->COMBINED_SELECT_VALUES;
		$queryFrom = " FROM `".DB_PREFIX."_combined`";
		$queryWhere = "	WHERE `status` = 2";
		if($this->_showPrivate === true) {
			$queryWhere = "	WHERE `status` IN (2,1)";
		}
		if(!empty($id) && is_numeric($id)) {
			$queryWhere .= " AND `categoryId` = '" . $this->DB->real_escape_string($id) . "'";
		}
		elseif(!empty($string) && is_string($string)) {
			$queryWhere .= " AND `category` = '" . $this->DB->real_escape_string($string) . "'";
		}
		else {
			return $ret;
		}

		$queryOrder = "GROUP BY `hash`
			ORDER BY `created` DESC";
		$queryLimit = '';
		if(!empty($limit)) {
			$queryLimit .= " LIMIT $limit";
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

			$query = $this->DB->query("SELECT COUNT(DISTINCT(hash)) AS amount ".$queryFrom.$queryWhere);
			$result = $query->fetch_assoc();
			$ret['amount'] = $result['amount'];
		}

		return $ret;
	}

	/**
	 * find all links by given tag string or id.
	 * Return array sorted by creation date DESC
	 * @param int $id
	 * @param string $string
	 * @param int $limit
	 * @param bool $offset
	 * @return array
	 */
	public function linksByTag($id, $string, $limit=5, $offset=false) {
		$ret = array();

		$querySelect = "SELECT ".$this->COMBINED_SELECT_VALUES;
		$queryFrom = " FROM `".DB_PREFIX."_combined`";
		$queryWhere = " WHERE `status` = 2";
		if($this->_showPrivate === true) {
			$queryWhere = " WHERE `status` IN (2,1)";
		}
		if(!empty($id) && is_numeric($id)) {
			$queryWhere .= " AND `tagId` = '" . $this->DB->real_escape_string($id) . "'";
		}
		elseif(!empty($string) && is_string($string)) {
			$queryWhere .= " AND `tag` = '" . $this->DB->real_escape_string($string) . "'";
		}
		else {
			return $ret;
		}

		$queryOrder = "GROUP BY `hash`
			ORDER BY `created` DESC";
		$queryLimit = '';
		if(!empty($limit)) {
			$queryLimit .= " LIMIT $limit";
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

			$query = $this->DB->query("SELECT COUNT(DISTINCT(hash)) AS amount ".$queryFrom.$queryWhere);
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
		$queryFrom = " FROM `".DB_PREFIX."_link`";
		$queryWhere = " WHERE `status` = 2";
		if($this->_showPrivate === true) {
			$queryWhere = " WHERE `status` IN (2,1)";
		}
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

			$query = $this->DB->query("SELECT COUNT(hash) AS amount ".$queryFrom.$queryWhere);
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
			$queryStr = "SELECT ".$this->COMBINED_SELECT_VALUES." 
			FROM `".DB_PREFIX."_combined`";
			if($this->_showPrivate === true) {
				$queryStr .= " WHERE `status` IN (2,1)";
			}
			else {
				$queryStr .= " WHERE `status` = 2";
			}
			$queryStr .= " AND `categoryId` = '" . $this->DB->real_escape_string($categoryid) . "'
			ORDER BY `created` DESC
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
			$queryStr = "SELECT * FROM `".DB_PREFIX."_link`";
			if($this->_showPrivate === true) {
				$queryStr .= " WHERE `status` IN (2,1)";
			}
			else {
				$queryStr .= " WHERE `status` = 2";
			}
			$queryStr .= " AND `link` = '".$this->DB->real_escape_string($url)."'";

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
				FROM `".DB_PREFIX."_link`
				WHERE MATCH (`search`) AGAINST ('".$this->DB->real_escape_string($searchStr)."' IN BOOLEAN MODE)";
			if($this->_showPrivate === true) {
				$queryStr .= " WHERE `status` IN (2,1)";
			}
			else {
				$queryStr .= " WHERE `status` = 2";
			}
			$queryStr .= " ORDER BY score DESC";

			$query = $this->DB->query($queryStr);
			if(!empty($query) && $query->num_rows > 0) {
				$ret = $query->fetch_all(MYSQLI_ASSOC);
			}
		}

		return $ret;
	}

	/**
	 * for simpler management we have the search data in a separate column
	 * it is not fancy or even technical nice but it damn works
	 */
	private function _updateSearchIndex() {
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
				foreach($l['tags'] as $t) {
					$searchStr .= ' '.$t['tag'];
				}
				foreach($l['categories'] as $c) {
					$searchStr .= ' '.$c['category'];
				}

				# now update the search string
				$queryStr = "UPDATE `".DB_PREFIX."_link`
								SET `search` = '".$this->DB->real_escape_string($searchStr)."'
								WHERE `hash` = '".$this->DB->real_escape_string($link['hash'])."'";

				$this->DB->query($queryStr);

				unset($LinkObj,$l,$searchStr,$t,$c,$queryStr);
			}
		}
	}
}

