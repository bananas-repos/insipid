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

$submitFeedback = false;
$formData = false;

# very simple security check.
# can/should be extended in the future.
Summoner::simpleAuth();

$_id = false;
if(isset($_GET['id']) && !empty($_GET['id'])) {
	$_id = trim($_GET['id']);
	$_id = Summoner::validate($_id,'nospace') ? $_id : false;
}

$_isAwm = false;
if(isset($_GET['awm']) && !empty($_GET['awm'])) {
	$_isAwm = trim($_GET['awm']);
	$_isAwm = Summoner::validate($_isAwm,'digit') ? true : false;
	$Management->setShowAwm($_isAwm);
}

$linkData = $Management->loadLink($_id);
if(empty($linkData)) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$linkObj = new Link($DB);
$linkObj->load($_id);

if($_isAwm === true) {
	$submitFeedback['message'] = 'To accept this link (link has moderation status), just save it. Otherwise just delete.';
	$submitFeedback['status'] = 'success';
}

if(isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['editlink'])) {
	$fData = $_POST['data'];

	$formData['private'] = 2;
	if(isset($fData['private'])) {
		$formData['private'] = 1;
	}

	$formData['localImage'] = false;
	if(isset($fData['localImage'])) {
		$formData['localImage'] = true;
	}

	$formData['description'] = trim($fData['description']);
	$formData['title'] = trim($fData['title']);
	$formData['image'] = trim($fData['image']);
	$formData['category'] = trim($fData['category']);
	$formData['tag'] = trim($fData['tag']);

	if(!empty($formData['title'])) {
		$update = $linkObj->update($formData);

		if($update === true) {
			$submitFeedback['message'] = 'Link updated successfully.';
			$submitFeedback['status'] = 'success';
			// update link info
			$linkObj->reload();
			$linkData = $linkObj->getData();
		}
		else {
			$submitFeedback['message'] = 'Something went wrong...';
			$submitFeedback['status'] = 'error';
		}
	}
	else {
		$submitFeedback['message'] = 'Please provide a title.';
		$submitFeedback['status'] = 'error';
	}
}
elseif(isset($_POST['refreshlink'])) {
	$linkInfo = Summoner::gatherInfoFromURL($linkData['link']);
	if(!empty($linkInfo)) {
		if(isset($linkInfo['description'])) {
			$linkData['description'] = $linkInfo['description'];
		}
		if(isset($linkInfo['title'])) {
			$linkData['title'] = $linkInfo['title'];
		}
		if(isset($linkInfo['image'])) {
			$linkData['image'] = $linkInfo['image'];
		}
	}
}

$formData = $linkData;
# prepare the tag string
$formData['tag'] = '';
if(!empty($linkData['tags'])) {
	foreach($linkData['tags'] as $k=>$v) {
		$formData['tag'] .= $v.',';
	}
	$formData['tag'] = trim($formData['tag']," ,");
}

# prepare the category string
$formData['category'] = '';
if(!empty($linkData['categories'])) {
	foreach($linkData['categories'] as $k=>$v) {
		$formData['category'] .= $v.',';
	}
	$formData['category'] = trim($formData['category']," ,");
}

$existingCategories = $Management->categories();
$existingTags = $Management->tags();
