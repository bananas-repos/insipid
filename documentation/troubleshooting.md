# Troubleshooting

To get more information if your are stuck:

Edit `config.php` and change the following setting:

```
FROM: const DEBUG = false;
TO: const DEBUG = true;
```

Alter it to false after your are done. Do not use this permanently since it displays unwanted and even
sensitive information to everyone.

If you want to know the db queries:

```
FROM: const QUERY_DEBUG = false;
TO: const QUERY_DEBUG = true;
```

This writes the queries into the `LOGFILE`
