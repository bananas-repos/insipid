# Backup

In the `index.php?p=stats` view, after authentication, you can create a basic complete mysql DB dump
of your current database.

The downloaded file can then be used to restore your data.

Currently it dumps the whole database which is configured as the `DB_NAME` in config file.
If you share this database with other applications, they will be dumped as well.
Improvements will be done in future versions.
