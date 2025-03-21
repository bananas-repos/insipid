<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2025 Johannes Keßler
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

$submitFeedback = array();
$formData = array();

# very simple security check.
# can/should be extended in the future.
Summoner::simpleAuth();

$_id = '';
if(isset($_GET['id']) && !empty($_GET['id'])) {
	$_id = trim($_GET['id']);
	$_id = Summoner::validate($_id,'nospace') ? $_id : '';
}

$_isAwm = false;
if(isset($_GET['awm']) && !empty($_GET['awm'])) {
	$_isAwm = trim($_GET['awm']);
	$_isAwm = Summoner::validate($_isAwm,'digit') ? true : false;
	$Management->setShowAwm($_isAwm);
}

$_requestMode = '';
if(isset($_GET['m']) && !empty($_GET['m'])) {
	$_requestMode = trim($_GET['m']);
	$_requestMode = Summoner::validate($_requestMode,'nospace') ? $_requestMode : '';
}

$linkData = $Management->loadLink($_id);
if(empty($linkData)) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$linkObj = new Link($DB);

if($_isAwm === true) {
	$submitFeedback['message'] = $T->t('edit.link.accept.new.link');
	$submitFeedback['status'] = 'success';
}

if($_requestMode == "export") {
	$linkObj->load($_id);

	$_i = $linkObj->getData('id');
	if(!empty($_i)) {

		$exportFilename = 'inspid-single-export-'.$_i.'.xml';

		$exportData = $Management->exportLinkData(false, $linkObj);
		if (!empty($exportData)) {
			header('Content-Type: text/xml');
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=$exportFilename");
			echo($exportData);
			exit();
		}
		else {
			$submitFeedback['message'] = $T->t('edit.link.export.fail');
			$submitFeedback['status'] = 'error';
		}
	}
	else {
		$submitFeedback['message'] = $T->t('edit.link.export.data.fail');
		$submitFeedback['status'] = 'error';
	}
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

	$formData['pagescreenshot'] = false;
	if(isset($fData['pagescreenshot'])) {
		$formData['pagescreenshot'] = true;
	}

	$formData['description'] = trim($fData['description']);
	$formData['title'] = trim($fData['title']);
	$formData['image'] = trim($fData['image']);
	$formData['category'] = trim($fData['category']);
	$formData['tag'] = trim($fData['tag']);

	if(!empty($formData['title'])) {
		$linkObj->load($_id);

		$update = $linkObj->update($formData);

		if($update === true) {
			$submitFeedback['message'] = $T->t('edit.link.updated');
			$submitFeedback['status'] = 'success';
			// update link info
			$linkObj->reload();
			$linkData = $linkObj->getData();
		}
		else {
			$submitFeedback['message'] = $T->t('edit.link.update.failed');
			$submitFeedback['status'] = 'error';
		}
	}
	else {
		$submitFeedback['message'] = $T->t('edit.link.update.title.missing');
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
elseif(isset($_POST['deleteLink'])) {
	$fData = $_POST['data'];
	if(isset($fData['delete'])) {
		$do = $Management->deleteLink($_id);
		if($do === true) {
			if($_isAwm === true) {
				header('Location: index.php?p=overview&m=awm');
			}
			else {
				header('Location: index.php');
			}
			exit();
		}
	}
}

$formData = $linkData;

$existingCategories = $Management->categories();
$existingTags = $Management->tags();
