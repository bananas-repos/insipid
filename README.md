# About

Insipid is a self hosted web-based bookmark manager similar to the Delicious service.

Project page: https://www.bananas-playground.net/projekt/insipid/

# Documentation

Documentation can be found in the documentation folder of each release.

# Usage

You install the latest release on a webserver of your own. Add your links, either public or private.

# Contribute

Want to contribute or found a problem?

See Contributing document: [CONTRIBUTING.md](https://github.com/bananas-repos/insipid/blob/master/CONTRIBUTING.md)

# Requirements

+ Apache (2.4 and up) with PHP extension enabled
+ PHP (8 and up)
  - mysql & mysqli
  - curl
  - pdo
  - imap +ssl if you us the email importer
  - xmlread
  - xmlwriter
+ MySQL server or access to a database 5.6.x and up
  - DB user rights has to include create, alter a view
  - NOT MariaDB. It is missing some functions MySQL has.

Latest browser for accessing the client. IE (not Edge) is not supported anymore.

# Uses

+ https://bulma.io/
+ https://github.com/PHPMailer/PHPMailer
+ https://ionicons.com/
+ https://github.com/druidfi/mysqldump-php
+ https://github.com/mikehaertl/php-shellcommand
