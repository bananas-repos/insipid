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

$searchValue = false;
$isUrl = false;
$submitFeedback = false;
$queryStr = false;
$searchResult = false;
$showAddForm = false;
$formData = false;

if(isset($_POST['data']) && !empty($_POST['data']) && isset($_POST['submitsearch'])) {
    $searchValue = trim($_POST['data']['searchfield']);
    $isUrl = Summoner::validate($searchValue,'url');
    if($isUrl === true) {
        # search for URL
        $queryStr = "SELECT * FROM";
    }
    elseif(Summoner::validate($searchValue,'text')) {
        # search for this in more then one field

    }
    else {
        $submitFeedback['message'] = 'Invalid input';
        $submitFeedback['status'] = 'error';
    }

    if(!empty($queryStr)) {
    }

    # new one?
    if(empty($searchResult) && $isUrl === true) {
        # show the add form
        $showAddForm = true;
        $formData['url'] = $searchValue;
    }
}