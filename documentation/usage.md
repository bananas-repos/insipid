# Usage

Use Insipid as a self hosted service for your own bookmarks. Share and collect.

Management needs authentication which is configured with the following options in the options file:

```
FRONTEND_USERNAME  => This is the username
FRONTEND_PASSWORD => This is the password for the username
```

Call the following URL to trigger the authentication: `http(s)://your.domain.tld/path/to/insipid/index.php?m=auth`

If successful you can now manage your items. Edit buttons are visible now.
Moderation and more overview can be access from the stats overview page.

# HowToAdd a new link:

There is no special "add a new link" option. Just paste the link into the search bar.
If the link is already in your database the edit option will be shown.
If not the add field will be shown and the possibility to safe the new link.

Usage of the email-importer can be found in the `email-importer.md` file.

# Search
The search is based on the link, description, tags and categories.
Technology behind is a mysql fulltext search in BOOLEAN MODE: https://dev.mysql.com/doc/refman/8.0/en/fulltext-boolean.html
Which means you can use special operators like +- or *
