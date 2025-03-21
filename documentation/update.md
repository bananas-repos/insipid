# Update

If you are updating from a previous version make sure every update info from  the version your are updating from is done.

## version 2.9.0 - Griffin Chapel

Add new config setting `TIMEZONE` to your config. See `config.default.php` for more details.

Add new config settings for using page screenshot with browserless.io. See `config.default.php` for more details.

```
const COMPLETE_PAGE_SCREENSHOT_BROWSERLESS_API = "https://chrome.browserless.io/screenshot?token=";
const COMPLETE_PAGE_SCREENSHOT_BROWSERLESS_API_KEY = 'YOUR-API-KEY';
const COMPLETE_PAGE_SCREEENSHOT_BROWSERLESS_TIMEOUT = 10000; # milliseconds
const COMPLETE_PAGE_SCREEENSHOT_BROWSERLESS_IMAGE_QUALITY = 75; # quality for jpeg
```

## version 2.8.2 - Dragon Chapel

Add query debug setting to config file. See `config.default.php` for `QUERY_DEBUG` constant. Add it to your local `config.php`

Add log file path constant to config file.  See `config.default.php` for `LOGFILE` constant. Add it to your local `config.php`

Update your tables with the following SQL statements. Replace `#REPLACE_ME#` with your current table prefix.

```
ALTER TABLE `#REPLACE_ME#_link` ADD INDEX (`created`);
ALTER TABLE `#REPLACE_ME#_link` ADD INDEX (`status`);
ALTER TABLE `#REPLACE_ME#_categoryrelation` ADD INDEX (`categoryid`);
ALTER TABLE `#REPLACE_ME#_tagrelation` ADD UNIQUE `tagid` (`linkid`, `tagid`);
ALTER TABLE `#REPLACE_ME#_tagrelation` ADD INDEX (`linkid`);
ALTER TABLE `#REPLACE_ME#_category` ADD INDEX (`name`);

ALTER TABLE `#REPLACE_ME#_category` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `#REPLACE_ME#_categoryrelation` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `#REPLACE_ME#_link` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `#REPLACE_ME#_tag` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `#REPLACE_ME#_tagrelation` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `#REPLACE_ME#_tag` CHANGE `name` `name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `link` `link` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `image` `image` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `hash` `hash` CHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_link` CHANGE `search` `search` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
ALTER TABLE `#REPLACE_ME#_category` CHANGE `name` `name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;
```

Folder `lib/MysqlDump` and its contents can be removed.

## version 2.8.1 - Deathwind

Added debug setting into `config.php`. See `config.default.php` for `DEBUG` constant. Add it to your local `config.php`.

Removed `WKHTMLTOPDF_USE` and `WKHTMLTOPDF_COMMAND` from `config.php` file  and replaced with `COMPLETE_PAGE_SCREENSHOT` 
and `COMPLETE_PAGE_SCREENSHOT_COMMAND`.

## version 2.8 - Wastelands

Nothing.

## version 2.7 - Sacred Grove

New syntax in `config.php` file. Switched from `define()` to `const` syntax. Use `config.default.php` as a template to update your config.

## version 2.6 - Hypostyle

Update config file with the new language setting. See `config.default.php` and `translation.md` for more information 

Run update search index to use the URL within the search index for your existing data.

## version 2.5 - Winnowing Hall

Update `config.php` file with the two new constants. See `config.default.php` and `snapshots-of-linked-webpage.txt` for more information.

```
  define('WKHTMLTOPDF_USE',false);
  define('WKHTMLTOPDF_COMMAND','/absolute/path/to/wkhtmltoimage');
```

## version 2.4 - Seven Portals

Run (after authentication) at `/index.php?p=stats` the "Search index update" to make the search work again correctly. 
At success, there is no confirmation.  To validate you can now search for single words case-insensitive.

Update `config.php` file with two new settings. See `config.default.php` for more info.

```
define('EMAIL_JOB_PROTECT', false);
define('EMAIL_JOB_PROTECT_SECRET', 'SOME_SECRET_STRING');
```

All files in `webroot/asset/js/` **EXCEPT** the new `editlink.js` can be removed.
