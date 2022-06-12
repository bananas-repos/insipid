<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2021 Johannes Keßler
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

/**
 * a static helper class for email importer
 */
class EmailImportHelper {
	/**
	 * extract from given string (eg. email body) any links we want to add
	 * should be in the right format. See documentation
	 * return an array with links and the infos about them
	 *
	 * @param string $string
	 * @return array $ret
	 */
	static function extractEmailLinks(string $string): array {
		$ret = array();

		#this matches a valid URL. An URL with | is still valid...
		$urlpattern  = '#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#';

		preg_match_all($urlpattern, $string, $matches);
		if(isset($matches[0]) && !empty($matches[0])) {
			foreach($matches[0] as $match) {
				$ret[md5($match)] = $match;
			}
		}

		return $ret;
	}

	/**
	 * Check if given from is in the valid EMAIL_REPLY_BACK_VALID
	 *
	 * @param string $replyTo
	 * @return bool
	 */
	static function canSendReplyTo(string $replyTo): bool {
		if(defined("EMAIL_REPORT_BACK") && EMAIL_REPORT_BACK === true
			&& defined("EMAIL_REPLY_BACK_VALID") && !empty(EMAIL_REPLY_BACK_VALID)) {
			if(strstr($replyTo,EMAIL_REPLY_BACK_VALID)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * check if given email header identify the email as a autoreply
	 * if so, you should do nothing with this email
	 *
	 * based an some code from:
	 * https://github.com/r-a-y/bp-reply-by-email
	 * https://arp242.net/autoreply.html
	 * https://github.com/jpmckinney/multi_mail/wiki/Detecting-autoresponders
	 * https://github.com/Exim/exim/wiki/EximAutoReply#Router-1
	 * https://stackoverflow.com/questions/6317714/apache-camel-mail-to-identify-auto-generated-messages/6383675#6383675
	 *
	 * this does not really quality check the values. It just checks if some headers are set
	 *
	 * @param array $headers complete email headers as an array
	 * @return bool
	 */
	static function isAutoReplyMessage(array $headers): bool {
		if(empty($headers)) {
			return true;
		}

		foreach($headers as $k=>$v) {
			$headers[strtolower($k)] = $v;
		}

		if(isset($headers['auto-submitted'])
			|| isset($headers['x-autoreply'])
			|| isset($headers['x-autorespond'])
			|| isset($headers['x-auto-response-suppress'])
			|| isset($headers['list-id'])
			|| isset($headers['list-subscribe'])
			|| isset($headers['list-archive'])
			|| isset($headers['list-help'])
			|| isset($headers['list-post'])
			|| isset($headers['mailing-list'])
			|| isset($headers['x-mailing-list'])
			|| isset($headers['list-unsubscribe'])
			|| isset($headers['list-owner'])
			|| isset($headers['auto-submitted'])
			|| isset($headers['x-autoreply-from'])
			|| isset($headers['x-mail-autoreply'])
			|| isset($headers['x-mc-system'])
			|| isset($headers['x-fc-machinegenerated'])
			|| isset($headers['x-facebook-notify'])
			|| isset($headers['x-autogenerated'])
			|| isset($headers['feedback-id'])
			|| isset($headers['x-msfbl'])
			|| isset($headers['x-loop'])
			|| isset($headers['x-cron-env'])
			|| isset($headers['x-ebay-mailtracker'])
			|| (isset($headers['precedence']) && $headers['precedence'] == "list")
			|| (isset($headers['x-precedence']) && $headers['x-precedence'] == "list")
			|| (isset($headers['precedence']) && $headers['precedence'] == "auto_reply")
			|| (isset($headers['x-precedence']) && $headers['x-precedence'] == "auto_reply")
			|| (isset($headers['x-post-messageclass']) && $headers['x-post-messageclass'] == "9; Autoresponder")
			|| (isset($headers['delivered-to']) && $headers['delivered-to'] == "Autoresponder")
			|| (isset($headers['x-spam-flag']) && $headers['x-spam-flag'] == "yes")
			|| (isset($headers['x-spam-status']) && $headers['x-spam-status'] == "yes")
		) {
			return true;
		}

		$_autoreplymail = array(
			"reply", "mailer-daemon", "delivery", "owner-", "request-", "bounce", "-confirm", "-errors", "donotreply",
			"postmaster", "daemon", "listserv", "majordom", "mailman", "mdaemon", "root"
		);
		foreach ($_autoreplymail as $entry) {
			if(
				(isset($headers['from']) && stripos($headers['from'],$entry))
				|| (isset($headers['reply-to']) && stripos($headers['reply-to'],$entry))
				|| (isset($headers['return-path']) &&stripos($headers['return-path'],$entry))
			) {
				return true;
			}
		}

		return false;
	}
}
