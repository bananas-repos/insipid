<?php

/**
 * simple IMAP SSL/TLS email connection based on the imap PHP functions
 * the code supports SSL/TLS and IMAP only
 *
 * @author banana
 *
 */
class SimpleImap {

	private $_connection;

	private $_inbox;

	private $_server = EMAIL_SERVER;
	private $_user = EMAIL_SERVER_USER;
	private $_pass = EMAIL_SERVER_PASS;
	private $_port = EMAIL_SERVER_PORT;
	private $_mailbox = EMAIL_SERVER_MAILBOX;

	private $_connectionstring = '';

	function __construct() {
	    # create the mailboxstring
	    $this->_connectionstring = '{'.$this->_server.':'.$this->_port.'/imap/ssl}';
	}

	function __destruct() {
	    imap_close($this->_connection);
	}



	/**
	 * connect to the e-mail server
	 * with this code SSL/TLS only
	 *
	 * @see http://ca.php.net/manual/en/function.imap-open.php
	 */
	public function connect() {

	    if(empty($this->_server)) {
	        throw new Exception('Missing EMAIL_SERVER');
	    }
	    if(empty($this->_port)) {
	        throw new Exception('Missing EMAIL_SERVER_PORT');
	    }
	    if(empty($this->_user)) {
	        throw new Exception('Missing EMAIL_SERVER_USER');
	    }

        # create the connection
	    $this->_connection = imap_open($this->_connectionstring.$this->_mailbox, $this->_user, $this->_pass);

		if(!$this->_connection) {
		    throw new Exception('Failed IMAP connection: '.var_export(imap_last_error(),true));
		}
	}

	/**
	 * process the given mailbox and check for the special messages
	 */
	function process() {
	    $messagecount = imap_num_msg($this->_connection);

	    if($messagecount === false) {
	        throw new Exception('Can not read the messages in given mailbox');
	    }

	    $messages = array();

	    for($i = 1; $i <= $messagecount; $i++) {
	        $subject = '';

	        # first we check the header.

	        # extract the subject....
	        $headerinfo = imap_rfc822_parse_headers(imap_fetchheader($this->_connection, $i));
	        $subjectArr = imap_mime_header_decode($headerinfo->subject);
	        foreach ($subjectArr as $el) {
                $subject .= $el->text;
	        }

	        if(!empty($subject)) {
	            # check the special stuff
	            $markerextract = substr($subject, 0, strlen(EMAIL_MARKER));
	            if($markerextract == EMAIL_MARKER) {
	                # valid message
	                var_dump($subject);
	            }
	        }

	        /*
	        $messages[] = array(
	            'index'     => $i,
	            'header'    => $headerinfo,
	            'body'      => imap_qprint(imap_body($this->_connection, $i)),
	            'structure' => imap_fetchstructure($this->_connection, $i)
	        );
	        */
	    }

	    # log messages processed to all messages

	    #var_dump($messages);
	}

	/**
	 * the the current stats about the mail connection and INBOX
	 * kinda debug only
	 *
	 * @see http://ca.php.net/manual/en/function.imap-status.php
	 */
	public function mailboxStatus() {
	    if($this->_connection !== false) {
	        $status = imap_status($this->_connection, $this->_connectionstring.$this->_mailbox, SA_ALL);
	        var_dump("messages ".$status->messages);
	        var_dump("recent ".$status->recent);
	        var_dump("unseen ".$status->unseen);
	        var_dump("uidnext ".$status->uidnext);
	        var_dump("uidvalidity ".$status->uidvalidity);

	        $list = imap_getmailboxes($this->_connection, $this->_connectionstring, "*");
	        if (is_array($list)) {
	            foreach ($list as $key => $val) {
	                echo "($key) ";
	                echo imap_utf7_decode($val->name) . ",";
	                echo "'" . $val->delimiter . "',";
	                echo $val->attributes . "<br />\n";
	            }
	        } else {
	            error_log("imap_getmailboxes failed: ".var_export(imap_last_error()));
	        }
	    }
	}




	// move the message to a new folder
	function move($msg_index, $folder='INBOX.Processed') {
		// move on server
		imap_mail_move($this->_connection, $msg_index, $folder);
		imap_expunge($this->_connection);

		// re-read the inbox
		$this->inbox();
	}

	// get a specific message (1 = first email, 2 = second email, etc.)
	function get($msg_index=NULL) {
		if (count($this->_inbox) <= 0) {
			return array();
		}
		elseif ( ! is_null($msg_index) && isset($this->_inbox[$msg_index])) {
			return $this->_inbox[$msg_index];
		}

		return $this->_inbox[0];
	}



	/**
	 * close the imap connection
	 */
	function close() {
	    imap_close($this->_connection);
	}
}

?>