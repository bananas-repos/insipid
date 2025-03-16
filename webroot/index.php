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
 */

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
error_reporting(-1); // E_ALL & E_STRICT
require('config.php');
date_default_timezone_set(TIMEZONE);

## set the error reporting
ini_set('log_errors',true);
if(DEBUG === true) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

require('lib/summoner.class.php');
require('lib/management.class.php');
require('lib/tag.class.php');
require('lib/category.class.php');
require('lib/link.class.php');
require('lib/translation.class.php');

## main vars
$Summoner = new Summoner();
# the template data as an array
$TemplateData = array();
# translation
$T = new Translation();
# the default view
$View = 'home.php';
# the default script
$ViewScript = 'home.inc.php';

# if the USE_PAGE_AUTH option is set
if(defined("USE_PAGE_AUTH") && USE_PAGE_AUTH === true) {
    # very simple security check.
    # can/should be extended in the future.
    Summoner::simpleAuth();
}

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_unicode_520_ci'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;;

# management needs the DB object
$Management = new Management($DB);
if($Summoner::simpleAuthCheck() === true) {
	$Management->setShowPrivate(true);
}

if(isset($_GET['p']) && !empty($_GET['p'])) {
    $_requestPage = trim($_GET['p']);
    $_requestPage = Summoner::validate($_requestPage,'nospace') ? $_requestPage : "home";

    $ViewScript = $_requestPage.'.inc.php';
    $View = $_requestPage.'.php';
}

# now include the script
# this sets information into $Data and can overwrite $View
if(file_exists('view/'.$ViewScript)) {
    require 'view/'.$ViewScript;
}

if(!empty($TemplateData['refresh'])) {
    header("Location: ".$TemplateData['refresh']);
	exit();
}

# header information
header('Content-type: text/html; charset=UTF-8');
if($Summoner::simpleAuthCheck() === true || !empty($TemplateData['nocacheHeader'])) {
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}

require 'view/_head.php';
require 'view/'.$View;
require 'view/_foot.php';

$DB->close();
# END
