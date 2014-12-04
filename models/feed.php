<?php

namespace Model\Feed;

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoDb\Database;
use PicoFeed\Export;
use PicoFeed\Import;
use PicoFeed\Reader;
use PicoFeed\Logging;
use Model\Config;
use Model\Item;

const LIMIT_ALL = -1;

// Update feed information
function update(array $values)
{
    return Database::get('db')
            ->table('feeds')
            ->eq('id', $values['id'])
            ->save(array(
                'title' => $values['title'],
                'site_url' => $values['site_url'],
                'feed_url' => $values['feed_url'],
                'enabled' => empty($values['enabled']) ? 0 : $values['enabled'],
                'rtl' => empty($values['rtl']) ? 0 : $values['rtl'],
                'download_content' => empty($values['download_content']) ? 0 : $values['download_content'],
            ));
}

// Export all feeds
function export_opml()
{
    $opml = new Export(get_all());
    return $opml->execute();
}

// Import OPML file
function import_opml($content)
{
    Logging::setTimezone(Config\get('timezone'));
    $import = new Import($content);
    $feeds = $import->execute();

    if ($feeds) {

        $db = Database::get('db');
        $db->startTransaction();

        foreach ($feeds as $feed) {

            if (! $db->table('feeds')->eq('feed_url', $feed->feed_url)->count()) {

                $db->table('feeds')->save(array(
                    'title' => $feed->title,
                    'site_url' => $feed->site_url,
                    'feed_url' => $feed->feed_url
                ));
            }
        }

        $db->closeTransaction();

        Config\write_debug();

        return true;
    }

    Config\write_debug();

    return false;
}

// Add a new feed from an URL
function create($url, $enable_grabber = false, $force_rtl = false)
{
    $reader = new Reader(Config\get_reader_config());
    $resource = $reader->download($url);

    $parser = $reader->getParser();

    if ($parser !== false) {

        if ($enable_grabber) {
            $parser->enableContentGrabber();
        }

        $feed = $parser->execute();

        if ($feed === false) {
            Config\write_debug();
            return false;
        }

        if (! $feed->getUrl()) {
            $feed->url = $reader->getUrl();
        }

        if (! $feed->getTitle()) {
            Config\write_debug();
            return false;
        }

        $db = Database::get('db');

        // Check if the feed is already there
        if (! $db->table('feeds')->eq('feed_url', $reader->getUrl())->count()) {

            // Etag and LastModified are added the next update
            $rs = $db->table('feeds')->save(array(
                'title' => $feed->getTitle(),
                'site_url' => $feed->getUrl(),
                'feed_url' => $reader->getUrl(),
                'download_content' => $enable_grabber ? 1 : 0,
                'rtl' => $force_rtl ? 1 : 0,
            ));

            if ($rs) {

                $feed_id = $db->getConnection()->getLastId();
                Item\update_all($feed_id, $feed->getItems(), $enable_grabber);
                Config\write_debug();

                return (int) $feed_id;
            }
        }
    }

    Config\write_debug();

    return false;
}

// Refresh all feeds
function refresh_all($limit = LIMIT_ALL)
{
    $feeds_id = get_ids($limit);

    foreach ($feeds_id as $feed_id) {
        refresh($feed_id);
    }

    // Auto-vacuum for people using the cronjob
    Database::get('db')->getConnection()->exec('VACUUM');

    return true;
}

// Refresh one feed
function refresh($feed_id)
{
    $feed = get($feed_id);

    if (empty($feed)) {
        return false;
    }

    $reader = new Reader(Config\get_reader_config());

    $resource = $reader->download(
        $feed['feed_url'],
        $feed['last_modified'],
        $feed['etag']
    );

    // Update the `last_checked` column each time, HTTP cache or not
    update_last_checked($feed_id);

    if (! $resource->isModified()) {
        update_parsing_error($feed_id, 0);
        Config\write_debug();
        return true;
    }

    $parser = $reader->getParser();

    if ($parser !== false) {

        if ($feed['download_content']) {

            // Don't fetch previous items, only new one
            $parser->enableContentGrabber();
            $parser->setGrabberIgnoreUrls(Database::get('db')->table('items')->eq('feed_id', $feed_id)->findAllByColumn('url'));
        }

        $result = $parser->execute();

        if ($result !== false) {

            update_parsing_error($feed_id, 0);
            update_cache($feed_id, $resource->getLastModified(), $resource->getEtag());

            Item\update_all($feed_id, $result->getItems(), $feed['download_content']);
            Config\write_debug();

            return true;
        }
    }

    update_parsing_error($feed_id, 1);
    Config\write_debug();

    return false;
}

// Get the list of feeds ID to refresh
function get_ids($limit = LIMIT_ALL)
{
    $table_feeds = Database::get('db')->table('feeds')
                                             ->eq('enabled', 1)
                                             ->asc('last_checked');

    if ($limit !== LIMIT_ALL) {
        $table_feeds->limit((int) $limit);
    }

    return $table_feeds->listing('id', 'id');
}

// Get feeds with no item
function get_all_empty()
{
    $feeds = Database::get('db')
        ->table('feeds')
        ->columns('feeds.id', 'feeds.title', 'COUNT(items.id) AS nb_items')
        ->join('items', 'feed_id', 'id')
        ->isNull('feeds.last_checked')
        ->groupBy('feeds.id')
        ->findAll();

    foreach ($feeds as $key => &$feed) {

        if ($feed['nb_items'] > 0) {
            unset($feeds[$key]);
        }
    }

    return $feeds;
}

// Get all feeds
function get_all()
{
    return Database::get('db')
        ->table('feeds')
        ->asc('title')
        ->findAll();
}

// Get all feeds with the number unread/total items
function get_all_item_counts()
{
    $counts = Database::get('db')
        ->table('items')
        ->columns('feed_id', 'status', 'count(*) as item_count')
        ->in('status', array('read', 'unread'))
        ->groupBy('feed_id', 'status')
        ->findAll();

    $feeds = Database::get('db')
        ->table('feeds')
        ->asc('title')
        ->findAll();

    $item_counts = array();

    foreach ($counts as &$count) {

        if (! isset($item_counts[$count['feed_id']])) {
            $item_counts[$count['feed_id']] = array(
                'items_unread' => 0,
                'items_total' => 0,
            );
        }

        $item_counts[$count['feed_id']]['items_total'] += $count['item_count'];

        if ($count['status'] === 'unread') {
            $item_counts[$count['feed_id']]['items_unread'] = $count['item_count'];
        }
    }

    foreach ($feeds as &$feed) {

        if (isset($item_counts[$feed['id']])) {
            $feed += $item_counts[$feed['id']];
        }
        else {
            $feed += array(
                'items_unread' => 0,
                'items_total' => 0,
            );
        }
    }

    return $feeds;
}

// Get unread/total count for one feed
function count_items($feed_id)
{
    $counts = Database::get('db')
        ->table('items')
        ->columns('status', 'count(*) as item_count')
        ->in('status', array('read', 'unread'))
        ->eq('feed_id', $feed_id)
        ->groupBy('status')
        ->findAll();

    $result = array(
        'items_unread' => 0,
        'items_total' => 0,
    );

    foreach ($counts as &$count) {

        if ($count['status'] === 'unread') {
            $result['items_unread'] = (int) $count['item_count'];
        }

        $result['items_total'] += $count['item_count'];
    }

    return $result;
}

// Get one feed
function get($feed_id)
{
    return Database::get('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->findOne();
}

// Update parsing error column
function update_parsing_error($feed_id, $value)
{
    Database::get('db')->table('feeds')->eq('id', $feed_id)->save(array('parsing_error' => $value));
}

// Update last check date
function update_last_checked($feed_id)
{
    Database::get('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->save(array(
            'last_checked' => time()
        ));
}

// Update Etag and last Modified columns
function update_cache($feed_id, $last_modified, $etag)
{
    Database::get('db')
        ->table('feeds')
        ->eq('id', $feed_id)
        ->save(array(
            'last_modified' => $last_modified,
            'etag'          => $etag
        ));
}

// Remove one feed
function remove($feed_id)
{
    // Items are removed by a sql constraint
    return Database::get('db')->table('feeds')->eq('id', $feed_id)->remove();
}

// Remove all feeds
function remove_all()
{
    return Database::get('db')->table('feeds')->remove();
}

// Enable a feed (activate refresh)
function enable($feed_id)
{
    return Database::get('db')->table('feeds')->eq('id', $feed_id)->save((array('enabled' => 1)));
}

// Disable feed
function disable($feed_id)
{
    return Database::get('db')->table('feeds')->eq('id', $feed_id)->save((array('enabled' => 0)));
}

// Validation for edit
function validate_modification(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('id', t('The feed id is required')),
        new Validators\Required('title', t('The title is required')),
        new Validators\Required('site_url', t('The site url is required')),
        new Validators\Required('feed_url', t('The feed url is required')),
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    return array(
        $result,
        $errors
    );
}
