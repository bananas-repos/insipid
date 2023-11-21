<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2023 Johannes Keßler
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
error_reporting(-1); // E_ALL & E_STRICT
require('../config.php');
date_default_timezone_set(TIMEZONE);

## set the error reporting
ini_set('log_errors',true);;
if(DEBUG === true) {
    ini_set('display_errors',true);
}
else {
    ini_set('display_errors',false);
}

// if the file needs to be in a web accessible folder
// you can either use the provided htaccess file
// or active the "protection" with a secret given by URL / cli param
if(defined('EMAIL_JOB_PROTECT') && EMAIL_JOB_PROTECT === true
    && defined('EMAIL_JOB_PROTECT_SECRET')) {

    $_hiddenSouce = false;

    $cliOptions = getopt("",array("hiddenSouce::"));
    if(!empty($cliOptions)) {
        $_hiddenSouce = trim($cliOptions['hiddenSouce']);
    }
    elseif(isset($_GET['hiddenSouce']) && !empty($_GET['hiddenSouce'])) {
        $_hiddenSouce = trim($_GET['hiddenSouce']);
    }

    if($_hiddenSouce !== EMAIL_JOB_PROTECT_SECRET) {
        error_log('ERROR Required param wrong.');
        exit("401\n");
    }
}

require('../lib/summoner.class.php');
require('../lib/tag.class.php');
require('../lib/category.class.php');
require('../lib/link.class.php');

require('../lib/simple-imap.class.php');
require('../lib/email-import-helper.class.php');

# load only if needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
if(EMAIL_REPORT_BACK === true) {
	require('../lib/phpmailer/Exception.php');
	require('../lib/phpmailer/PHPMailer.php');
	require('../lib/phpmailer/SMTP.php');

	$phpmailer = new PHPMailer();
	if(DEBUG === true) $phpmailer->SMTPDebug = SMTP::DEBUG_SERVER;
	$phpmailer->isSMTP();
	$phpmailer->Host = EMAIL_SERVER;
	$phpmailer->SMTPAuth = true;
	$phpmailer->SMTPSecure = $phpmailer::ENCRYPTION_SMTPS;
	$phpmailer->Username = EMAIL_SERVER_USER;
	$phpmailer->Password = EMAIL_SERVER_PASS;
	$phpmailer->Port = EMAIL_SERVER_PORT_SMTP;
	$phpmailer->setFrom(EMAIL_REPLY_BACK_ADDRESS);
	$phpmailer->Subject = EMAIL_REPLY_BACK_SUBJECT;
	$phpmailer->Timeout = 3;

    if(DEBUG === true) $phpmailer->SMTPDebug = SMTP::DEBUG_SERVER;

    $phpmailer->SMTPOptions = array(
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ],
    );
}

## DB connection
$DB = new mysqli(DB_HOST, DB_USERNAME,DB_PASSWORD, DB_NAME);
if ($DB->connect_errno) exit('Can not connect to MySQL Server');
$DB->set_charset("utf8mb4");
$DB->query("SET collation_connection = 'utf8mb4_bin'");
$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;;

# the email reader
$EmailReader = new SimpleImap();
$emails = array();
try {
    $EmailReader->connect();
    if(DEBUG === true) $EmailReader->mailboxStatus();
}
catch (Exception $e) {
    Summoner::sysLog('[ERROR] Email server connection failed: '.$e->getMessage());
    exit();
}

try {
	// emailid => info of the mail as an array
	// this is not the message-id
    $emails = $EmailReader->messageWithValidSubject(EMAIL_MARKER);
}
catch (Exception $e) {
    Summoner::sysLog('[ERROR] Can not process email messages: '.$e->getMessage());
    exit();
}

# process the emails and then move the emails
$invalidProcessedEmails = array();
$validProcessedEmails = array();
if(!empty($emails)) {
    foreach($emails as $emailId=>$emailData) {
        $links = EmailImportHelper::extractEmailLinks($emailData['body']);
		if(!empty($links)) {
			if(DEBUG === true) Summoner::sysLog("Links in email: ".Summoner::cleanForLog($links));

			foreach($links as $linkstring) {
				# defaults
				$newdata['link'] = $linkstring;
				$newdata['description'] = '';
				$newdata['title'] = '';
				$newdata['image'] = '';
				$newdata['status'] = '3'; # moderation required
				$newdata['tagArr'] = array();
				$newdata['catArr'] = array();
				$newdata['hash'] = '';

				if(strstr($linkstring, "|")) {
					$_t = explode("|", $linkstring);
					$newdata['link'] = $_t[0];

					$newdata['catArr'] = Summoner::prepareTagOrCategoryStr($_t[1]);
					if(isset($_t[2])) {
						$newdata['tagArr'] = Summoner::prepareTagOrCategoryStr($_t[2]);
					}
				}

				$newdata['link'] = filter_var($newdata['link'], FILTER_SANITIZE_URL);
				$newdata['link'] = Summoner::addSchemeToURL($newdata['link']);

				if (!filter_var($newdata['link'], FILTER_VALIDATE_URL)) {
					error_log("ERROR Invalid URL: ".Summoner::cleanForLog($newdata['link']));
					if(DEBUG === true) Summoner::sysLog("Invalid URL: ".Summoner::cleanForLog($newdata['link']));
					continue;
				}

				$newdata['hash'] = md5($newdata['link']);

				$linkInfo = Summoner::gatherInfoFromURL($newdata['link']);
				if(!empty($linkInfo) && !empty($linkInfo['title'])) {
					$newdata['title'] = $linkInfo['title'];

					if(isset($linkInfo['description'])) {
						$newdata['description'] = $linkInfo['description'];
					}
					if(isset($linkInfo['image'])) {
						$newdata['image'] = $linkInfo['image'];
					}
				}
				else {
					error_log("WARN No valid title for link found: ".$newdata['link']);
					if(DEBUG === true) Summoner::sysLog("[WARN] No valid title for link found: ".Summoner::cleanForLog($newdata));
					array_push($invalidProcessedEmails, $emailData);
					continue;
				}

				if(DEBUG === true) Summoner::sysLog("New data ".Summoner::cleanForLog($newdata));

                $linkObj = new Link($DB);
                $linkID = false;

				# check for duplicate
                $existing = $linkObj->load($newdata['hash']);

				$DB->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

                if(!empty($existing) && isset($existing['id'])) {
                    $linkID = $existing['id'];
                    Summoner::sysLog('[INFO] Updating existing link with tag or category '.Summoner::cleanForLog($newdata['link']));
                }
                else {
                    $linkObj = new Link($DB);
                    try {
                        $linkID = $linkObj->create(array(
                            'hash' => $newdata['hash'],
                            'link' => $newdata['link'],
                            'status' => $newdata['status'],
                            'description' => $newdata['description'],
                            'title' => $newdata['title'],
                            'image' => $newdata['image'],
							'tagArr' => $newdata['tagArr'],
							'catArr' => $newdata['catArr']
                        ), true);
                    } catch (Exception $e) {
                        $_m = "[WARN] Can not create new link into DB." . $e->getMessage();
                        Summoner::sysLog($_m);
                        $emailData['importmessage'] = $_m;
                        array_push($invalidProcessedEmails, $emailData);
                        if (DEBUG === true) Summoner::sysLog($_m);
                        if (DEBUG === true) Summoner::sysLog(Summoner::cleanForLog($newdata));
                        continue;
                    }
                }

				if(!empty($linkID)) {

					if(!empty($newdata['catArr'])) {
						foreach($newdata['catArr'] as $c) {
							$catObj = new Category($DB);
							$catObj->initbystring($c);
							$catObj->setRelation($linkID);

							unset($catObj);
						}
					}
					if(!empty($newdata['tagArr'])) {
						foreach($newdata['tagArr'] as $t) {
							$tagObj = new Tag($DB);
							$tagObj->initbystring($t);
							$tagObj->setRelation($linkID);

							unset($tagObj);
						}
					}

					$DB->commit();

                    Summoner::sysLog("[INFO] Link successfully added/updated: ".$newdata['link']);
					array_push($validProcessedEmails,$emailData);
				}
				else {
					$DB->rollback();
                    Summoner::sysLog("[ERROR] Link could not be added. SQL problem? ".$newdata['link']);
					$emailData['importmessage'] = "Link could not be added";
					array_push($invalidProcessedEmails,$emailData);
				}
			}
		}
    }
}

# if we have invalid import mails, ignore them, just log em
# if EMAIL_REPORT_BACK is true then report back with errors if EMAIL_REPLY_BACK_VALID
if(!empty($invalidProcessedEmails)) {
    Summoner::sysLog("[INFO] We have invalid import messages.");
	foreach ($invalidProcessedEmails as $invalidMail) {
		if(EmailImportHelper::canSendReplyTo($invalidMail['header_rfc822']->reply_toaddress)
			&& !EmailImportHelper::isAutoReplyMessage($invalidMail['header_array'])) {
			$_address = PHPMailer::parseAddresses($invalidMail['header_rfc822']->reply_toaddress);
			$phpmailer->Body = $invalidMail['importmessage']."\n\n";
			$phpmailer->Body .= $invalidMail['body'];
			$phpmailer->addAddress($_address[0]['address']);
			$phpmailer->send();
            Summoner::sysLog("[INFO] Report back email to: ".$_address[0]['address']);
		}
		else {
            Summoner::sysLog("[WARN] Invalid message: ".$invalidMail['header_rfc822']->subject);
		}
	}
}

# move them to the processed / archive folder
if(!empty($validProcessedEmails)) {
    Summoner::sysLog("[INFO] We have valid import messages.");
	foreach ($validProcessedEmails as $validMail) {
	    $EmailReader->moveMessage($validMail['uid']);
        Summoner::sysLog("[INFO] Mail moved to archive ".$validMail['header_rfc822']->subject);
	}
}

$DB->close();
$EmailReader->close();
