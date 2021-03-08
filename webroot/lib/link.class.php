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

		$this->_data = array();

		if (!empty($hash)) {
			$queryStr = "SELECT
				`id`,
				`link`,
				`created`,
				`updated`,
				`status`,
				`description`,
				`title`,
				`image`,
				`hash`
				FROM `" . DB_PREFIX . "_link`
				WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";
			$query = $this->DB->query($queryStr);
			if (!empty($query) && $query->num_rows == 1) {
				$this->_data = $query->fetch_assoc();

				# add stuff
				$this->_tags();
				$this->_categories();
				$this->_image();
				$this->_private();
				$this->_snapshot();
				$this->_pageScreenshot();
			}
		}

		return $this->_data;
	}

	/**
	 * loads only the info needed to display the link
	 * for edit use $this->load
	 *
	 * @param $hash
	 * @return array
	 */
	public function loadShortInfo($hash) {
		$this->_data = array();

		if (!empty($hash)) {
			$queryStr = "SELECT `id`,`link`,`description`,`title`,`image`,`hash`, `created`
				FROM `" . DB_PREFIX . "_link`
				WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";

			$query = $this->DB->query($queryStr);
			if (!empty($query) && $query->num_rows == 1) {
				$this->_data = $query->fetch_assoc();

				# add stuff
				$this->_image();
			}
		}

		return $this->_data;
	}

	public function loadFromDataShortInfo($data) {
		$this->_data = array();

		if(isset($data['id']) && isset($data['link']) && isset($data['created']) && isset($data['status'])
			&& isset($data['title']) && isset($data['hash']) && isset($data['description']) && isset($data['image'])) {
			$this->_data = $data;
			$this->_image();
		}

		return $this->_data;
	}

	/**
	 * return all or data for given key on the current loaded link
	 *
	 * @param bool $key
	 * @return array|mixed
	 */
	public function getData($key = false) {
		$ret = $this->_data;

		if (!empty($key) && isset($this->_data[$key])) {
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
     * @param bool $returnId
     * @return boolean|int
     */
	public function create($data, $returnId = false) {
		$ret = false;

		if (!isset($data['link']) || empty($data['link'])) return false;
		if (!isset($data['hash']) || empty($data['hash'])) return false;
		if (!isset($data['title']) || empty($data['title'])) return false;

		$queryStr = "INSERT INTO `" . DB_PREFIX . "_link` SET
                        `link` = '" . $this->DB->real_escape_string($data['link']) . "',
                        `created` = NOW(),
                        `status` = '" . $this->DB->real_escape_string($data['status']) . "',
                        `description` = '" . $this->DB->real_escape_string($data['description']) . "',
                        `title` = '" . $this->DB->real_escape_string($data['title']) . "',
                        `image` = '" . $this->DB->real_escape_string($data['image']) . "',
                        `hash` = '" . $this->DB->real_escape_string($data['hash']) . "',
                        `search` = '" . $this->DB->real_escape_string($data['search']) . "'";

		$this->DB->query($queryStr);
		if ($returnId === true) {
			$ret = $this->DB->insert_id;
		}
		else {
			error_log('ERROR Failed to rcreate link: '.var_export($data,true));
		}

		return $ret;
	}

	/**
	 * update the current loaded link with the given data
	 * @param array $data
	 * @return boolean|int
	 */
	public function update($data) {

		$ret = false;

		if (isset($data['title']) && !empty($data['title']) && !empty($this->_data)) {

			# categories and tag stuff
			$catArr = Summoner::prepareTagOrCategoryStr($data['category']);
			$tagArr = Summoner::prepareTagOrCategoryStr($data['tag']);

			$search = $data['title'];
			$search .= ' '.$data['description'];
			$search .= ' '.implode(" ", $tagArr);
			$search .= ' '.implode(" ", $catArr);
            $search = trim($search);
			$search = strtolower($search);

			$this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

			# did the image url change?
			$_imageUrlChanged = false;
			if ($this->_data['image'] != $data['image']) {
				$_imageUrlChanged = true;
			}

			$queryStr = "UPDATE `" . DB_PREFIX . "_link` SET
							`status` = '" . $this->DB->real_escape_string($data['private']) . "',
							`description` = '" . $this->DB->real_escape_string($data['description']) . "',
							`title` = '" . $this->DB->real_escape_string($data['title']) . "',
							`image` = '" . $this->DB->real_escape_string($data['image']) . "',
							`search` = '" . $this->DB->real_escape_string($search) . "'
						  WHERE `hash` = '" . $this->DB->real_escape_string($this->_data['hash']) . "'";
			$query = $this->DB->query($queryStr);

			if ($query !== false) {
				$catObj = new Category($this->DB);
				$tagObj = new Tag($this->DB);
				// clean the relations first
				$this->_removeTagRelation(false);
				$this->_removeCategoryRelation(false);

				if (!empty($catArr)) {
					foreach ($catArr as $c) {
						$catObj->initbystring($c);
						$catObj->setRelation($this->_data['id']);
					}
				}
				if (!empty($tagArr)) {
					foreach ($tagArr as $t) {
						$tagObj->initbystring($t);
						$tagObj->setRelation($this->_data['id']);
					}
				}

				$this->DB->commit();

				# decide to store or remove the image
				if (isset($data['localImage'])) {
					$image = ABSOLUTE_PATH . '/' . LOCAL_STORAGE . '/thumbnail-' . $this->_data['hash'].'.jpg';
					if ($data['localImage'] === true) {
						if (!file_exists($image) || $_imageUrlChanged === true) {
							Summoner::downloadFile($data['image'], $image);
						}
					} elseif ($data['localImage'] === false) {
						if (file_exists($image)) {
							unlink($image);
						}
					}
				}

				# decide if we want to make a local snapshot
				if(isset($data['snapshot'])) {
					$snapshot = ABSOLUTE_PATH . '/' . LOCAL_STORAGE . '/snapshot-' . $this->_data['hash'].'.jpg';
					if ($data['snapshot'] === true) {
						if (!file_exists($snapshot) || $_imageUrlChanged === true) {
							require_once 'lib/snapshot.class.php';
							$snap = new Snapshot();
							$do = $snap->doSnapshot($this->_data['link'], $snapshot);
							if(empty($do)) {
								error_log('ERROR Failed to create snapshot: '.var_export($data,true));
							}
						}
					} elseif ($data['snapshot'] === false) {
						if (file_exists($snapshot)) {
							unlink($snapshot);
						}
					}
				}

				# decide if we want to make a local full page scrrenshot
				if(isset($data['pagescreenshot'])) {
					$pagescreenshot = ABSOLUTE_PATH . '/' . LOCAL_STORAGE . '/pagescreenshot-' . $this->_data['hash'].'.jpg';
					if ($data['pagescreenshot'] === true) {
						if (!file_exists($pagescreenshot) || $_imageUrlChanged === true) {
							require_once 'lib/snapshot.class.php';
							$snap = new Snapshot();
							$do = $snap->wholePageSnpashot($this->_data['link'], $pagescreenshot);
							if(!empty($do)) {
								error_log('ERROR Failed to create snapshot: '.var_export($data,true));
							}
						}
					} elseif ($data['pagescreenshot'] === false) {
						if (file_exists($pagescreenshot)) {
							unlink($pagescreenshot);
						}
					}
				}


				$ret = true;
			} else {
				$this->DB->rollback();
				error_log('ERROR Failed to update link: '.var_export($data,true));
			}

		}

		return $ret;
	}

	/**
	 * call this to delete all the relations to this link.
	 * To completely remove the link use Management->deleteLink()
	 */
	public function deleteRelations() {
		$this->_removeTagRelation(false);
		$this->_removeCategoryRelation(false);
		$this->_deleteImage();
		$this->_deleteSnapshot();
		$this->_deletePageScreenshot();
	}

	/**
	 * load all the tags we have to the already loaded link
	 * needs $this->load called first
	 */
	private function _tags() {
		$ret = array();

		if (!empty($this->_data['hash'])) {
			$queryStr = "SELECT
				DISTINCT tag, tagId
				FROM `" . DB_PREFIX . "_combined`
				WHERE `hash` = '" . $this->DB->real_escape_string($this->_data['hash']) . "'";
			$query = $this->DB->query($queryStr);
			if (!empty($query) && $query->num_rows > 0) {
				while ($result = $query->fetch_assoc()) {
					if ($result['tag'] !== NULL) {
						$ret[$result['tagId']] = $result['tag'];
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

		if (!empty($this->_data['hash'])) {
			$queryStr = "SELECT
				DISTINCT category, categoryId
				FROM `" . DB_PREFIX . "_combined`
				WHERE `hash` = '" . $this->DB->real_escape_string($this->_data['hash']) . "'";
			$query = $this->DB->query($queryStr);
			if (!empty($query) && $query->num_rows > 0) {
				while ($result = $query->fetch_assoc()) {
					if ($result['category'] !== NULL) {
						$ret[$result['categoryId']] = $result['category'];
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
		if (!empty($this->_data['id'])) {
			$queryStr = false;
			if ($tagid === false) {
				$queryStr = "DELETE
					FROM `" . DB_PREFIX . "_tagrelation`
					WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'";
			} elseif (is_numeric($tagid)) {
				$queryStr = "DELETE
					FROM `" . DB_PREFIX . "_tagrelation`
					WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'
					AND `tagid` = '" . $this->DB->real_escape_string($tagid) . "'";
			}
			if (!empty($queryStr)) {
				$this->DB->query($queryStr);
			}
		}
	}

	/**
	 * remove all or given category relation to the current loaded link
	 * @param mixed $categoryid
	 */
	private function _removeCategoryRelation($categoryid) {
		if (!empty($this->_data['id'])) {
			$queryStr = false;
			if ($categoryid === false) {
				$queryStr = "DELETE
					FROM `" . DB_PREFIX . "_categoryrelation`
					WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'";
			} elseif (is_numeric($categoryid)) {
				$queryStr = "DELETE
					FROM `" . DB_PREFIX . "_categoryrelation`
					WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'
					AND `categoryid` = '" . $this->DB->real_escape_string($categoryid) . "'";
			}
			if (!empty($queryStr)) {
				$this->DB->query($queryStr);
			}
		}
	}

	/**
	 * determine of we have a local stored image
	 * if so populate the localImage attribute
	 */
	private function _image() {
		if (!empty($this->_data['hash'])) {
			$this->_data['imageToShow'] = $this->_data['image'];
			$image = ABSOLUTE_PATH.'/'.LOCAL_STORAGE.'/thumbnail-'.$this->_data['hash'].'.jpg';
			if (file_exists($image)) {
				$this->_data['imageToShow'] = LOCAL_STORAGE.'/thumbnail-'.$this->_data['hash'].'.jpg';
				$this->_data['localImage'] = true;
			}
		}
	}

	/**
	 * determine if we have a local stored snapshot
	 * if so populate the snapshotLink attribute
	 */
	private function _snapshot() {
		if (!empty($this->_data['hash'])) {
			$snapshot = ABSOLUTE_PATH.'/'.LOCAL_STORAGE.'/snapshot-'.$this->_data['hash'].'.jpg';
			if (file_exists($snapshot)) {
				$this->_data['snapshotLink'] = LOCAL_STORAGE.'/snapshot-'.$this->_data['hash'].'.jpg';
				$this->_data['snapshot'] = true;
			}
		}
	}

	/**
	 * determine if we have a local full page screenshot
	 * if so populate the pagescreenshotLink attribute
	 */
	private function _pageScreenshot() {
		if (!empty($this->_data['hash'])) {
			$pagescreenshot = ABSOLUTE_PATH.'/'.LOCAL_STORAGE.'/pagescreenshot-'.$this->_data['hash'].'.jpg';
			if (file_exists($pagescreenshot)) {
				$this->_data['pagescreenshotLink'] = LOCAL_STORAGE.'/pagescreenshot-'.$this->_data['hash'].'.jpg';
				$this->_data['pagescreenshot'] = true;
			}
		}
	}

	/**
	 * remove the local stored image
	 */
	private function _deleteImage() {
		if (!empty($this->_data['hash']) && !empty($this->_data['imageToShow'])) {
			$image = ABSOLUTE_PATH.'/'.$this->_data['imageToShow'];
			if (file_exists($image)) {
				unlink($image);
			}
		}
	}

	/**
	 * remove the local stored snapshot
	 */
	private function _deleteSnapshot() {
		if (!empty($this->_data['hash']) && !empty($this->_data['snapshotLink'])) {
			$snapshot = LOCAL_STORAGE.'/snapshot-'.$this->_data['hash'].'.jpg';
			if (file_exists($snapshot)) {
				unlink($snapshot);
			}
		}
	}

	/**
	 * remove the local stored pagescreenshot
	 */
	private function _deletePageScreenshot() {
		if (!empty($this->_data['hash']) && !empty($this->_data['pagescreenshotLink'])) {
			$pagescreenshot = LOCAL_STORAGE.'/pagescreenshot-'.$this->_data['hash'].'.jpg';
			if (file_exists($pagescreenshot)) {
				unlink($pagescreenshot);
			}
		}
	}

	/**
	 * check if the status is private and set the info
	 */
	private function _private() {
		if (!empty($this->_data['status']) && $this->_data['status'] == "1") {
			$this->_data['private'] = "1";
		}
	}
}
