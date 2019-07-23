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

$searchValue = false;
$isUrl = false;
$submitFeedback = false;
$queryStr = false;
$searchResult = false;
$showAddForm = false;
$formData = false;
$honeypotCheck = false;

if((isset($_POST['password']) && !empty($_POST['password'])) || (isset($_POST['username']) && !empty($_POST['username']))) {
	# those are hidden fields. A robot may input these. A valid user does not.
	$honeypotCheck = true;
}

# search or new one.
if(isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['submitsearch']) && $honeypotCheck === false) {
	$searchValue = trim($_POST['data']['searchfield']);
	$isUrl = Summoner::validate($searchValue,'url');
	if($isUrl === true) {
		# search for URL
		$queryStr = "SELECT * FROM `".DB_PREFIX."_link`
						WHERE `link` = '".$DB->real_escape_string($searchValue)."'";
	}
	elseif(Summoner::validate($searchValue,'text')) {
		$queryStr = "SELECT *,
			MATCH (`search`) AGAINST ('".$DB->real_escape_string($searchValue)."' IN BOOLEAN MODE) AS score
			FROM `".DB_PREFIX."_link`
			WHERE MATCH (`search`) AGAINST ('".$DB->real_escape_string($searchValue)."' IN BOOLEAN MODE)
			ORDER BY score DESC";
	}
	else {
		$submitFeedback['message'] = 'Invalid input';
		$submitFeedback['status'] = 'error';
	}

	if(!empty($queryStr)) {
		$query = $DB->query($queryStr);
		if(!empty($query) && $query->num_rows > 0) {
			$searchResult = $query->fetch_all(MYSQLI_ASSOC);
		}
	}

	# new one?
	if(empty($searchResult) && $isUrl === true) {
		# try to gather some information automatically
		$linkInfo = Summoner::gatherInfoFromURL($searchValue);
		if(!empty($linkInfo)) {
			if(isset($linkInfo['description'])) {
				$formData['description'] = $linkInfo['description'];
			}
			if(isset($linkInfo['title'])) {
				$formData['title'] = $linkInfo['title'];
			}
			if(isset($linkInfo['image'])) {
				$formData['image'] = $linkInfo['image'];
			}
		}
		# show the add form
		$showAddForm = true;
		$formData['url'] = $searchValue;
	}
	elseif(!empty($searchResult)) {
		# something has been found
	}
	else {
		# nothing found
		$submitFeedback['message'] = 'Nothing found...';
		$submitFeedback['status'] = 'error';
	}
}

# add a new one
if(isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['addnewone']) && $honeypotCheck === false) {
	$fData = $_POST['data'];

	# very simple security check.
	# can/should be extended in the future.
	Summoner::simpleAuth();

	$formData['private'] = 2;
	if(isset($fData['private'])) {
		$formData['private'] = 1;
	}

	$formData['url'] = trim($fData['url']);
	$formData['description'] = trim($fData['description']);
	$formData['title'] = trim($fData['title']);
	$formData['image'] = trim($fData['image']);
	$formData['category'] = trim($fData['category']);
	$formData['tag'] = trim($fData['tag']);

	$isUrl = Summoner::validate($formData['url'],'url');

	if($isUrl === true && !empty($formData['title'])) {
		$hash = md5($formData['url']);

		# categories and tag stuff
		$catArr = Summoner::prepareTagOrCategoryStr($formData['category']);
		$tagArr = Summoner::prepareTagOrCategoryStr($formData['tag']);

		$search = $formData['title'];
		$search .= ' '.$formData['description'];
		$search .= ' '.implode(" ",$tagArr);
		$search .= ' '.implode(" ",$catArr);

		$DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

		$linkObj = new Link($DB);
		$linkID = $linkObj->create(array(
			'hash' => $hash,
			'search' => $search,
			'link' => $formData['url'],
			'status' => $formData['private'],
			'description' => $formData['description'],
			'title' => $formData['title'],
			'image' => $formData['image']
		),true);

		if(!empty($linkID)) {

			if(!empty($catArr)) {
				foreach($catArr as $c) {
					$catObj = new Category($DB);
					$catObj->initbystring($c);
					$catObj->setRelation($linkID);

					unset($catObj);
				}
			}
			if(!empty($tagArr)) {
				foreach($tagArr as $t) {
					$tagObj = new Tag($DB);
					$tagObj->initbystring($t);
					$tagObj->setRelation($linkID);

					unset($tagObj);
				}
			}

			$DB->commit();

			$submitFeedback['message'] = 'Link added successfully.';
			$submitFeedback['status'] = 'success';
			$TemplateData['refresh'] = 'index.php?p=linkinfo&id='.$hash;
		}
		else {
			$DB->rollback();
			$submitFeedback['message'] = 'Something went wrong...';
			$submitFeedback['status'] = 'error';
			$showAddForm = true;
		}
	}
	else {
		$submitFeedback['message'] = 'Please provide a valid URL and title.';
		$submitFeedback['status'] = 'error';
		$showAddForm = true;
	}
}

$existingCategories = $Management->categories();
$existingTags = $Management->tags();
$latestLinks = $Management->latestLinks();
$orderedCategories = $Management->categoriesByDateAdded();
