<?php
/**
 * Insipid
 * Personal web-bookmark-system
 *
 * Copyright 2016-2020 Johannes Keßler
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

#
## database config
define('DB_HOST','127.0.0.1'); # Address of the database server
define('DB_USERNAME','user'); # Username to access the database server and database itself
define('DB_PASSWORD','test'); # Password for username
define('DB_NAME','insipid'); # Database name on your database server
define('DB_PREFIX','insipid'); # a _ is added automatically as separation

# user config
define('FRONTEND_USERNAME','luke');
define('FRONTEND_PASSWORD','father');

# absolute path of webroot
define('ABSOLUTE_PATH', '/path/to/insipid/webroot');
# relative to absolute path the name of the storage folder
define('LOCAL_STORAGE', 'localdata');

# complete restricted access not only the private links or the edit functions
# username and password see above
define("USE_PAGE_AUTH",false);

# results per page
define("RESULTS_PER_PAGE",12);

# if the location of email-import.php needs to be in a web accessible folder
# you can protect it by setting EMAIL_JOB_PROTECT to true
# and EMAIL_JOB_PROTECT_SECRET to a special secret string
# AND remove the default provided .htaccess file in the job folder
define('EMAIL_JOB_PROTECT', false); # Default false
define('EMAIL_JOB_PROTECT_SECRET', 'YOUR_SOME_SECRET_STRING'); # Your own secret string
# settings for importing from e-mail
# SSL/TLS only
# IMAP (reading), SMTP (sending)
define('EMAIL_SERVER','');
define('EMAIL_SERVER_USER','');
define('EMAIL_SERVER_PASS','');
define('EMAIL_SERVER_PORT_IMAP',993);
define('EMAIL_SERVER_PORT_SMTP',465);
define('EMAIL_SERVER_MAILBOX','INBOX'); # default INBOX
define('EMAIL_MARKER','to-insipid- ');
define('EMAIL_ARCHIVE_FOLDER','archive');
define('EMAIL_REPORT_BACK',false);
define('EMAIL_REPLY_BACK_VALID','');
define('EMAIL_REPLY_BACK_ADDRESS','');
define('EMAIL_REPLY_BACK_SUBJECT','Insipid email import response');
