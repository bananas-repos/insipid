<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2017 Johannes Keßler
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

$linkObj = new Link($DB);
$link = $linkObj->load($_id);
if(empty($link)) {
    header("HTTP/1.0 404 Not Found");
}

$formData = $link;
# prepate the tag edit string
$formData['tag'] = '';
if(!empty($link['tags'])) {
    foreach($link['tags'] as $entry) {
        $formData['tag'] .= $entry['tag'].',';
    }
    $formData['tag'] = trim($formData['tag']," ,");
}

# prepate the category string
$formData['category'] = '';
if(!empty($link['categories'])) {
    foreach($link['categories'] as $entry) {
        $formData['category'] .= $entry['category'].',';
    }
    $formData['category'] = trim($formData['category']," ,");
}

$existingCategories = $Management->categories();
$existingTags = $Management->tags();

