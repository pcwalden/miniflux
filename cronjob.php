<?php

require __DIR__.'/app/common.php';

use Miniflux\Model;

if (php_sapi_name() === 'cli') {
    $options = getopt('', array(
        'limit::',
        'call-interval::',
        'update-interval::',
        'database::',
    ));
}
else {
    $options = $_GET;
}

if (! empty($options['database'])) {
    if (! Model\Database\select($options['database'])) {
        die("Database ".$options['database']." not found\r\n");
    }
}

$limit = ! empty($options['limit']) && ctype_digit($options['limit']) ? (int) $options['limit'] : Model\Feed\LIMIT_ALL;
$update_interval = ! empty($options['update-interval']) && ctype_digit($options['update-interval']) ? (int) $options['update-interval'] : null;
$call_interval = ! empty($options['call-interval']) && ctype_digit($options['call-interval']) ? (int) $options['call-interval'] : null;

if ($update_interval !== null && $call_interval !== null && $limit === Model\Feed\LIMIT_ALL && $update_interval >= $call_interval) {
    $feeds_count = PicoDb\Database::getInstance('db')->table('feeds')->count();
    $limit = ceil($feeds_count / ($update_interval / $call_interval));
}

Model\Feed\refresh_all($limit);
Model\Item\autoflush_read();
Model\Item\autoflush_unread();
Model\Config\write_debug();
