# E-Mail importer

Insipid has a feature to fetch new links from E-Mails.  Those E-Mails are read from a configured IMAP mailbox.

## Requirements
You need to enable the imap/ssl functions within PHP and  have a IMAP mailbox on a SSL/TLS email server.

## Config
Set the config variables in the config file.  Make sure you an individual marker string!
There is no "security" within this method. Only the special string you can define.
The new links will be hidden at first. You need to verify them before they are  visible in your list.

Here are the important configs:

```
EMAIL_SERVER => Address of your E-Mail server
EMAIL_SERVER_PORT_IMAP => The SSL Imap port. Default: 993
EMAIL_SERVER_MAILBOX => The name of your Inbox. Default: INBOX
EMAIL_ARCHIVE_FOLDER => The name of the archive folder in which the processed emails will be moved

EMAIL_REPORT_BACK => set this to true if you want to report to the sender. Default: false
EMAIL_SERVER_PORT_SMTP => The SSL SMTP port for using the report back function. Default: 465
EMAIL_REPLY_BACK_VALID => Multiple E-Mails addresses which can be reported back to. RFC822-style comma-separated email addresses
EMAIL_REPLY_BACK_ADDRESS => The E-Mail address which sends the report mail. Usually the address from which your read the mails
```

## Moderation

Access the moderation with this link: `index.php?p=stats`. After authentication there will be more info and one called **Moderation**

## Usage

Syntax of the E-Mail body:

```
absolute-link|multiple,category,strings|multiple,tag,strings\n
new-absolute-link|multiple,category,strings|multiple,tag,strings\n
```

Create a cronjob to execute the `email-import.php` file.

## Access and "protection"

If the file needs to be in a web accessible folder you can either use the provided htaccess file
or active the "protection" with a secret given by URL / cli param.
If you activate `EMAIL_JOB_PROTECT` you **NEED** to set an individual string in `EMAIL_JOB_PROTECT_SECRET`
**AND** remove the provided .htaccess file in the job folder.

```
cli: php email-import.php ----hiddenSouce=EMAIL_JOB_PROTECT_SECRET
webaccess: email-import.php?hiddenSouce=EMAIL_JOB_PROTECT_SECRET
```

Use the following settings in the config file:

```
define('EMAIL_JOB_PROTECT', false); => Set to true if you want this kind of "protection"
define('EMAIL_JOB_PROTECT_SECRET', 'YOUR_SOME_SECRET_STRING'); => Change to your liking
```
