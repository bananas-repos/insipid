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
$_displayEditButton = false;
if(Summoner::simpleAuthCheck() === true) {
	$_displayEditButton = true;
	$moderationAmount = $Management->moderationAmount();
}

if(isset($_POST['statsDeleteLocalStorage'])) {

    if($Management->clearLocalStorage() === true) {
        
        $TemplateData['refresh'] = 'index.php?p=stats';
    }
    else {
        $submitFeedback['message'] = 'Something went wrong while storage cleaning';
        $submitFeedback['status'] = 'error';
    }
}

$linkAmount = $Management->linkAmount();
$tagAmount = $Management->tagAmount();
$categoryAmount = $Management->categoryAmount();
$localStorageAmount = $Management->storageAmount();
$localStorageAmount = Summoner::humanFileSize($localStorageAmount);

