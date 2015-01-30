Configuration parameters
========================

How do I override application variables?
----------------------------------------

There are few settings that can't be changed by the user interface.
These parameters are defined with PHP constants.

To override them, rename the file `config.default.php` to `config.php`.

Actually, the following constants can be overrided:

```php
<?php

// HTTP_TIMEOUT => default value is 20 seconds (Maximum time to fetch a feed)
define('HTTP_TIMEOUT', '20');

// DATA_DIRECTORY => default is data (writable directory)
define('DATA_DIRECTORY', __DIR__.'/data');

// DB_FILENAME => default value is db.sqlite (default database filename)
define('DB_FILENAME', 'db.sqlite');

// ENABLE_MULTIPLE_DB => default value is true (multiple users support)
define('ENABLE_MULTIPLE_DB', true);

// DEBUG_FILENAME => default is data/debug.log
define('DEBUG_FILENAME', DATA_DIRECTORY.'/debug.log');

// THEME_DIRECTORY => default is themes
define('THEME_DIRECTORY', 'themes');

// SESSION_SAVE_PATH => default is empty (used to store session files in a custom directory)
define('SESSION_SAVE_PATH', '');

// PROXY_HOSTNAME => default is empty (make HTTP requests through a HTTP proxy if set)
define('PROXY_HOSTNAME', '');

// PROXY_PORT => default is 3128 (default port of Squid)
define('PROXY_PORT', 3128);

// PROXY_USERNAME => default is empty (set the proxy username is needed)
define('PROXY_USERNAME', '');

// PROXY_PASSWORD => default is empty
define('PROXY_PASSWORD', '');

// ENABLE_AUTO_UPDATE => default is true (enable Miniflux update from the user interface)
define('ENABLE_AUTO_UPDATE', true);
```