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
$_requestMode = false;
if(isset($_GET['m']) && !empty($_GET['m'])) {
    $_requestMode = trim($_GET['m']);
    $_requestMode = Summoner::validate($_requestMode,'nospace') ? $_requestMode : "all";
}

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