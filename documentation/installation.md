# Installation

## 1. Requirements

Check the `requirements.md` first.
An access and database on a mysql database server. Write them down.
If you share a database, either use the default table prefix (inisipid) or use your own.
Absolute path on your webserver web-accessible folder where insipid will be installed to.

## 2. Read

Read this document before changing anything!

## 3. Unpack

Unpack the archive to a temporary directory of your choice.
Final files and folder will be moved to the webroot of your webserver later on.
You can also verify the package with the available md5/sha sum information.

## 4. Config

Create your config file.

Copy `webroot/config.default.php` to `webroot/config.php`

Modify at least the following settings in this file:
```
const DB_HOST = '127.0.0.1; <= The database server hostname you are using
const DB_USERNAME = 'user' <= The database username you are using
const DB_PASSWORD = 'test; <= The database password you are using
const DB_NAME = 'insipid' <= Database name on your database server
const FRONTEND_USERNAME = 'han'; <= The frontend username. Please change the default
const FRONTEND_PASSWORD = 'solo'; <= The frontend password. Please change the default
const ABSOLUTE_PATH = '/path/to/insipid/on/your/webserver'; <= Absolute path where insipid is located in your webserver
```

## 5. Prepare SQL file

Copy and modify `documentation/insipid-edit-me.sql` and replace the placeholder `#REPLACE_ME#`.
Open the file and make a search and replace:

```
search for: #REPLACE_ME#
replace with: YOUR_TABLE_PREFIX
```

The value `YOUR_TABLE_PREFIX` has to match with the value for `DB_PREFIX` in your `config.php` file.
Default is insipid. Alter the value here and in the config file if you choose a different one.

## 6. Database

Create a database if needed. Should match `DB_NAME` in your `config.php`. Remember step 1.
Import the edited from step 5 sql file into your MySQL database.

## 7. Robots.txt

Edit the `robots.txt` file to your needs. Usually not needed.

## 8. Move files

Copy the content of the unpacked webroot folder onto your webserver folder.
Make the `localdata` folder writable to the web process to store images from link parsing.
`LOCAL_STORAGE` is the option in the `config.php` file.
Make sure `ABSOLUTE_PATH` matches the location of insipid in your webserver!

## 9. Access

If you want to fully restrict your contents activate the user/password restriction in the `config.php` file.

## 10. E-Mail importer

If you want to use the e-mail importer read the `email-importer.md` file.
