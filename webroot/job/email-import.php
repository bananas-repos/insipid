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

mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
ini_set('error_reporting',-1); // E_ALL & E_STRICT
# time settings
date_default_timezone_set('Europe/Berlin');

define('DEBUG',true);

## check request
$_urlToParse = filter_var($_SERVER['QUERY_STRING'],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
if(!empty($_urlToParse)) {
    # see http://de2.php.net/manual/en/regexp.reference.unicode.php
    if(preg_match('/[\p{C}\p{M}\p{Sc}\p{Sk}\p{So}\p{Zl}\p{Zp}]/u',$_urlToParse) === 1) {
        die('Malformed request. Make sure you know what you are doing.');
    }
}

## set the error reporting
ini_set('log_errors',true);
ini_set('error_log','error.log');
if(DEBUG === true) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

require('../config.php');
require('../lib/simple-imap.class.php');

## DB connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); # throw exeptions
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_bin'");

# the email reader
$EmailReader = new SimpleImap();
$emaildata = array();
try {
    $EmailReader->connect();
    #if(DEBUG === true) {$EmailReader->mailboxStatus();}
}
catch (Exception $e) {
    error_log('Email server connection failed: '.var_export($e->getMessage(),true));
    exit();
}

try {
    $emaildata = $EmailReader->bodyFromMessagesWithSubject(EMAIL_MARKER);
}
catch (Exception $e) {
    error_log('Can not process email messages: '.var_export($e->getMessage(),true));
    exit();
}

# process the emaildata and then move the emails
var_dump($emaildata);

# move them to the processed / archive folder
#$EmailReader->move()

$DB->close();
# END