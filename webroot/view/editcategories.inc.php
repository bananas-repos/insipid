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


if(isset($_POST['category']) && !empty($_POST['category']) && isset($_POST['updateCategories'])) {
	$categoryData = $_POST['category'];

	$deleteCategoryData = array();
	if(isset($_POST['deleteCategory'])) {
		$deleteCategoryData = $_POST['deleteCategory'];
	}

	$newCategory = $_POST['newCategory'];

	# first deletion, then update and then add
	# adding a new one which matches an existing one will update it.

	if(!empty($deleteCategoryData)) {
		foreach($deleteCategoryData as $k=>$v) {
			if($v == "delete") {
				$catObj = new Category($DB);
				$load = $catObj->initbyid($k);
				if($load === true) {
					$catObj->delete();
				}
			}
		}

		$submitFeedback['message'] = 'Link updated successfully.';
		$submitFeedback['status'] = 'success';
	}

	$submitFeedback['message'] = 'Something went wrong...';
			$submitFeedback['status'] = 'error';
}

# show all the categories we have
$categoryCollection = $Management->categories(false, true);
$subHeadline = 'All the categories <i class="ion-md-filing"></i>';
