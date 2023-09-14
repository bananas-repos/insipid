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
 * Class Link
 */
class Link {

    /**
     * the database object
     *
     * @var mysqli
     */
    private mysqli $DB;

    /**
     * the current loaded link data
     *
     * @var array
     */
    private array $_data;

    /**
     * Link constructor.
     *
     * @param mysqli $databaseConnectionObject
     */
    public function __construct(mysqli $databaseConnectionObject) {
        $this->DB = $databaseConnectionObject;
    }

    /**
     * load all the info we have about a link by given hash
     *
     * @param string $hash
     * @return array
     */
    public function load(string $hash): array {

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
                FROM `".DB_PREFIX."_link`
                WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
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
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $this->_data;
    }

    /**
     * loads only the info needed to display the link
     * for edit use $this->load
     *
     * @param string $hash
     * @return array
     */
    public function loadShortInfo(string $hash): array {
        $this->_data = array();

        if (!empty($hash)) {
            $queryStr = "SELECT `id`,`link`,`description`,`title`,`image`,`hash`, `created`
                FROM `".DB_PREFIX."_link`
                WHERE `hash` = '" . $this->DB->real_escape_string($hash) . "'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if (!empty($query) && $query->num_rows == 1) {
                    $this->_data = $query->fetch_assoc();

                    # add stuff
                    $this->_image();
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        return $this->_data;
    }

    /**
     * Get shortinfo from given data array
     *
     * @param array $data
     * @return array
     */
    public function loadFromDataShortInfo(array $data): array {
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
     * @param string $key
     * @return string|array
     */
    public function getData(string $key = ''): string|array {
        $ret = $this->_data;

        if (!empty($key) && isset($this->_data[$key])) {
            $ret = $this->_data[$key];
        }

        return $ret;
    }

    /**
     * reload the current id from DB
     *
     * @return void
     */
    public function reload(): void {
        $this->load($this->_data['hash']);
    }

    /**
     * create a new link with the given data
     *
     * @param array $data
     * @param bool $returnId
     * @return string
     */
    public function create(array $data, bool $returnId = false): string {
        $ret = '';

        if (!isset($data['link']) || empty($data['link'])) return $ret;
        if (!isset($data['hash']) || empty($data['hash'])) return $ret;
        if (!isset($data['title']) || empty($data['title'])) return $ret;

        $_t = parse_url($data['link']);
        $data['search'] = $data['title'];
        $data['search'] .= ' '.$data['description'];
        $data['search'] .= ' '.implode(" ",$data['tagArr']);
        $data['search'] .= ' '.implode(" ",$data['catArr']);
        $data['search'] .= ' '.$_t['host'];
        $data['search'] .= ' '.implode(' ',explode('/',$_t['path']));
        $data['search'] = trim($data['search']);
        $data['search'] = strtolower($data['search']);

        $queryStr = "INSERT INTO `" . DB_PREFIX . "_link` SET
                        `link` = '" . $this->DB->real_escape_string($data['link']) . "',
                        `created` = NOW(),
                        `status` = '" . $this->DB->real_escape_string($data['status']) . "',
                        `description` = '" . $this->DB->real_escape_string($data['description']) . "',
                        `title` = '" . $this->DB->real_escape_string($data['title']) . "',
                        `image` = '" . $this->DB->real_escape_string($data['image']) . "',
                        `hash` = '" . $this->DB->real_escape_string($data['hash']) . "',
                        `search` = '" . $this->DB->real_escape_string($data['search']) . "'";

        if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

        try {
            $this->DB->query($queryStr);
            if ($returnId === true) {
                $ret = $this->DB->insert_id;
            }
            else {
                Summoner::sysLog('ERROR Failed to create link: '.var_export($data,true));
            }
        } catch (Exception $e) {
            Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
        }

        return $ret;
    }

    /**
     * update the current loaded link with the given data
     *
     * @param array $data
     * @return boolean
     */
    public function update(array $data): bool {
        $ret = false;

        if (isset($data['title']) && !empty($data['title']) && !empty($this->_data)) {

            # categories and tag stuff
            $catArr = Summoner::prepareTagOrCategoryStr($data['category']);
            $tagArr = Summoner::prepareTagOrCategoryStr($data['tag']);

            $_t = parse_url($this->_data['link']);
            $search = $data['title'];
            $search .= ' '.$data['description'];
            $search .= ' '.implode(" ", $tagArr);
            $search .= ' '.implode(" ", $catArr);
            $search .= ' '.$_t['host'];
            if(isset($_t['path'])) {
                $search .= ' '.implode(' ',explode('/',$_t['path']));
            }
            $search = trim($search);
            $search = strtolower($search);

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

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            $this->DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
            try {
                $query = $this->DB->query($queryStr);
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }


            if ($query !== false) {
                $catObj = new Category($this->DB);
                $tagObj = new Tag($this->DB);
                // clean the relations first
                $this->_removeTagRelation();
                $this->_removeCategoryRelation();

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

                    if(DEBUG) Summoner::sysLog("DEBUG Try to save local image to: $image");

                    if ($data['localImage'] === true) {
                        if(DEBUG) Summoner::sysLog("DEBUG want to save local image to: $image");

                        if (!file_exists($image) || $_imageUrlChanged === true) {
                            if(DEBUG) Summoner::sysLog("DEBUG Image new or not there yet: $image");

                            Summoner::downloadFile($data['image'], $image);
                        }
                    } elseif ($data['localImage'] === false) {
                        if(DEBUG) Summoner::sysLog("DEBUG Image to be removed: $image");

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
                                Summoner::sysLog('ERROR Failed to create snapshot: '.var_export($data,true));
                            }
                        }
                    } elseif ($data['snapshot'] === false) {
                        if (file_exists($snapshot)) {
                            unlink($snapshot);
                        }
                    }
                }

                # decide if we want to make a local full page screenshot
                if(isset($data['pagescreenshot'])) {
                    $pagescreenshot = ABSOLUTE_PATH . '/' . LOCAL_STORAGE . '/pagescreenshot-' . $this->_data['hash'].'.jpg';
                    if ($data['pagescreenshot'] === true) {
                        if (!file_exists($pagescreenshot) || $_imageUrlChanged === true) {
                            require_once 'lib/snapshot.class.php';
                            $snap = new Snapshot();
                            $do = $snap->wholePageSnapshot($this->_data['link'], $pagescreenshot);
                            if(!empty($do)) {
                                Summoner::sysLog('ERROR Failed to create snapshot: '.var_export($data,true));
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
                Summoner::sysLog('ERROR Failed to update link: '.var_export($data,true));
            }

        }

        return $ret;
    }

    /**
     * call this to delete all the relations to this link.
     * To completely remove the link use Management->deleteLink()
     *
     * @return void
     */
    public function deleteRelations(): void {
        $this->_removeTagRelation();
        $this->_removeCategoryRelation();
        $this->_deleteImage();
        $this->_deleteSnapshot();
        $this->_deletePageScreenshot();
    }

    /**
     * load all the tags we have to the already loaded link
     * needs $this->load called first
     *
     * @return void
     */
    private function _tags(): void {
        $ret = array();

        if (!empty($this->_data['hash'])) {
            $queryStr = "SELECT
                DISTINCT tag, tagId
                FROM `" . DB_PREFIX . "_combined`
                WHERE `hash` = '" . $this->DB->real_escape_string($this->_data['hash']) . "'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if (!empty($query) && $query->num_rows > 0) {
                    while ($result = $query->fetch_assoc()) {
                        if ($result['tag'] !== NULL) {
                            $ret[$result['tagId']] = $result['tag'];
                        }
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        $this->_data['tags'] = $ret;
    }

    /**
     * load all the categories we have to the already loaded link
     * needs $this->load called first
     *
     * @return void
     */
    private function _categories(): void {
        $ret = array();

        if (!empty($this->_data['hash'])) {
            $queryStr = "SELECT
                DISTINCT category, categoryId
                FROM `" . DB_PREFIX . "_combined`
                WHERE `hash` = '" . $this->DB->real_escape_string($this->_data['hash']) . "'";

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            try {
                $query = $this->DB->query($queryStr);
                if (!empty($query) && $query->num_rows > 0) {
                    while ($result = $query->fetch_assoc()) {
                        if ($result['category'] !== NULL) {
                            $ret[$result['categoryId']] = $result['category'];
                        }
                    }
                }
            } catch (Exception $e) {
                Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
            }
        }

        $this->_data['categories'] = $ret;
    }

    /**
     * remove all or given tag relation to the current loaded link
     *
     * @param string $tagid
     * @return void
     */
    private function _removeTagRelation(string $tagid = ''): void {
        if (!empty($this->_data['id'])) {
            $queryStr = '';
            if (is_numeric($tagid)) {
                $queryStr = "DELETE
                    FROM `" . DB_PREFIX . "_tagrelation`
                    WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'
                    AND `tagid` = '" . $this->DB->real_escape_string($tagid) . "'";

            } else {
                $queryStr = "DELETE
                    FROM `" . DB_PREFIX . "_tagrelation`
                    WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'";
            }

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            if (!empty($queryStr)) {
                try {
                    $this->DB->query($queryStr);
                } catch (Exception $e) {
                    Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
                }
            }
        }
    }

    /**
     * remove all or given category relation to the current loaded link
     *
     * @param string $categoryid
     * @return void
     */
    private function _removeCategoryRelation(string $categoryid=''): void {
        if (!empty($this->_data['id'])) {
            $queryStr = '';
            if (is_numeric($categoryid)) {
                $queryStr = "DELETE
                    FROM `" . DB_PREFIX . "_categoryrelation`
                    WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'
                    AND `categoryid` = '" . $this->DB->real_escape_string($categoryid) . "'";
            } else {
                $queryStr = "DELETE
                    FROM `" . DB_PREFIX . "_categoryrelation`
                    WHERE `linkid` = '" . $this->DB->real_escape_string($this->_data['id']) . "'";
            }

            if(QUERY_DEBUG) Summoner::sysLog("[QUERY] ".__METHOD__." query: ".Summoner::cleanForLog($queryStr));

            if (!empty($queryStr)) {
                try {
                    $this->DB->query($queryStr);
                } catch (Exception $e) {
                    Summoner::sysLog("[ERROR] ".__METHOD__." mysql catch: ".$e->getMessage());
                }
            }
        }
    }

    /**
     * determine of we have a local stored image
     * if so populate the localImage attribute
     *
     * @return void
     */
    private function _image(): void {
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
     *
     * @return void
     */
    private function _snapshot(): void {
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
     *
     * @return void
     */
    private function _pageScreenshot(): void {
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
     *
     * @return void
     */
    private function _deleteImage(): void {
        if (!empty($this->_data['hash']) && !empty($this->_data['imageToShow'])) {
            $image = ABSOLUTE_PATH.'/'.$this->_data['imageToShow'];
            if (file_exists($image)) {
                unlink($image);
            }
        }
    }

    /**
     * remove the local stored snapshot
     *
     * @return void
     */
    private function _deleteSnapshot(): void {
        if (!empty($this->_data['hash']) && !empty($this->_data['snapshotLink'])) {
            $snapshot = LOCAL_STORAGE.'/snapshot-'.$this->_data['hash'].'.jpg';
            if (file_exists($snapshot)) {
                unlink($snapshot);
            }
        }
    }

    /**
     * remove the local stored pagescreenshot
     *
     * @return void
     */
    private function _deletePageScreenshot(): void {
        if (!empty($this->_data['hash']) && !empty($this->_data['pagescreenshotLink'])) {
            $pagescreenshot = LOCAL_STORAGE.'/pagescreenshot-'.$this->_data['hash'].'.jpg';
            if (file_exists($pagescreenshot)) {
                unlink($pagescreenshot);
            }
        }
    }

    /**
     * check if the status is private and set the info
     *
     * @return void
     */
    private function _private(): void {
        if (!empty($this->_data['status']) && $this->_data['status'] == "1") {
            $this->_data['private'] = "1";
        }
    }
}
