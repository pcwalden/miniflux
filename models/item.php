<?php

namespace Model\Item;

use Model\Config;
use PicoDb\Database;
use PicoFeed\Logging;
use PicoFeed\Grabber;
use PicoFeed\Client;
use PicoFeed\Filter;

// Get all items without filtering
function get_everything()
{
    return Database::get('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->orderBy('updated', 'desc')
        ->findAll();
}

// Get everthing since date (timestamp)
function get_everything_since($timestamp)
{
    return Database::get('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->gte('updated', $timestamp)
        ->orderBy('updated', 'desc')
        ->findAll();
}

// Get a list of [item_id => status,...]
function get_all_status()
{
    return Database::get('db')
        ->table('items')
        ->in('status', array('read', 'unread'))
        ->orderBy('updated', 'desc')
        ->listing('id', 'status');
}

// Get all items by status
function get_all($status, $offset = null, $limit = null, $order_column = 'updated', $order_direction = 'desc')
{
    return Database::get('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->eq('status', $status)
        ->orderBy($order_column, $order_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get the number of items per status
function count_by_status($status)
{
    return Database::get('db')
        ->table('items')
        ->eq('status', $status)
        ->count();
}

// Get the number of bookmarks
function count_bookmarks()
{
    return Database::get('db')
        ->table('items')
        ->eq('bookmark', 1)
        ->in('status', array('read', 'unread'))
        ->count();
}

// Get all bookmarks
function get_bookmarks($offset = null, $limit = null)
{
    return Database::get('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.status',
            'items.content',
            'items.feed_id',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->eq('bookmark', 1)
        ->orderBy('updated', Config\get('items_sorting_direction'))
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get the number of items per feed
function count_by_feed($feed_id)
{
    return Database::get('db')
        ->table('items')
        ->eq('feed_id', $feed_id)
        ->in('status', array('unread', 'read'))
        ->count();
}

// Get all items per feed
function get_all_by_feed($feed_id, $offset = null, $limit = null, $order_column = 'updated', $order_direction = 'desc')
{
    return Database::get('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.bookmark',
            'items.language',
            'feeds.site_url',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('unread', 'read'))
        ->eq('feed_id', $feed_id)
        ->orderBy($order_column, $order_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get one item by id
function get($id)
{
    return Database::get('db')
        ->table('items')
        ->eq('id', $id)
        ->findOne();
}

// Get item naviguation (next/prev items)
function get_nav($item, $status = array('unread'), $bookmark = array(1, 0), $feed_id = null)
{
    $query = Database::get('db')
        ->table('items')
        ->columns('id', 'status', 'title', 'bookmark')
        ->neq('status', 'removed')
        ->orderBy('updated', Config\get('items_sorting_direction'));

    if ($feed_id) $query->eq('feed_id', $feed_id);

    $items = $query->findAll();

    $next_item = null;
    $previous_item = null;

    for ($i = 0, $ilen = count($items); $i < $ilen; $i++) {

        if ($items[$i]['id'] == $item['id']) {

            if ($i > 0) {

                $j = $i - 1;

                while ($j >= 0) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $previous_item = $items[$j];
                        break;
                    }

                    $j--;
                }
            }

            if ($i < ($ilen - 1)) {

                $j = $i + 1;

                while ($j < $ilen) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $next_item = $items[$j];
                        break;
                    }

                    $j++;
                }
            }

            break;
        }
    }

    return array(
        'next' => $next_item,
        'previous' => $previous_item
    );
}

// Change item status to removed and clear content
function set_removed($id)
{
    return Database::get('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'removed', 'content' => ''));
}

// Change item status to read
function set_read($id)
{
    return Database::get('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'read'));
}

// Change item status to unread
function set_unread($id)
{
    return Database::get('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'unread'));
}

// Change item status to "read", "unread" or "removed"
function set_status($status, array $items)
{
    if (! in_array($status, array('read', 'unread', 'removed'))) return false;

    return Database::get('db')
        ->table('items')
        ->in('id', $items)
        ->save(array('status' => $status));
}

// Enable/disable bookmark flag
function set_bookmark_value($id, $value)
{
    return Database::get('db')
        ->table('items')
        ->eq('id', $id)
        ->in('status', array('read', 'unread'))
        ->save(array('bookmark' => $value));
}

// Swap item status read <-> unread
function switch_status($id)
{
    $item = Database::get('db')
        ->table('items')
        ->columns('status')
        ->eq('id', $id)
        ->findOne();

    if ($item['status'] == 'unread') {

        Database::get('db')
            ->table('items')
            ->eq('id', $id)
            ->save(array('status' => 'read'));

        return 'read';
    }
    else {

        Database::get('db')
            ->table('items')
            ->eq('id', $id)
            ->save(array('status' => 'unread'));

        return 'unread';
    }

    return '';
}

// Mark all unread items as read
function mark_all_as_read()
{
    return Database::get('db')
        ->table('items')
        ->eq('status', 'unread')
        ->save(array('status' => 'read'));
}

// Mark all read items to removed
function mark_all_as_removed()
{
    return Database::get('db')
        ->table('items')
        ->eq('status', 'read')
        ->eq('bookmark', 0)
        ->save(array('status' => 'removed', 'content' => ''));
}

// Mark only specified items as read
function mark_items_as_read(array $items_id)
{
    Database::get('db')->startTransaction();

    foreach ($items_id as $id) {
        set_read($id);
    }

    Database::get('db')->closeTransaction();
}

// Mark all items of a feed as read
function mark_feed_as_read($feed_id)
{
    return Database::get('db')
        ->table('items')
        ->eq('status', 'unread')
        ->eq('feed_id', $feed_id)
        ->update(array('status' => 'read'));
}

// Mark all read items to removed after X days
function autoflush_read()
{
    $autoflush = (int) Config\get('autoflush');

    if ($autoflush > 0) {

        // Mark read items removed after X days
        Database::get('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'read')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed', 'content' => ''));
    }
    else if ($autoflush === -1) {

        // Mark read items removed immediately
        Database::get('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'read')
            ->save(array('status' => 'removed', 'content' => ''));
    }
}

// Mark all unread items to removed after X days
function autoflush_unread()
{
    $autoflush = (int) Config\get('autoflush_unread');

    if ($autoflush > 0) {

        // Mark read items removed after X days
        Database::get('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'unread')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed', 'content' => ''));
    }
}

// Update all items
function update_all($feed_id, array $items, $enable_grabber = false)
{
    $nocontent = (bool) Config\get('nocontent');

    $items_in_feed = array();

    $db = Database::get('db');
    $db->startTransaction();

    foreach ($items as $item) {

        Logging::setMessage('Item => '.$item->getId().' '.$item->getUrl());

        // Item parsed correctly?
        if ($item->getId() && $item->getUrl()) {

            Logging::setMessage('Item parsed correctly');

            // Get item record in database, if any
            $itemrec = $db
                ->table('items')
                ->columns('enclosure')
                ->eq('id', $item->getId())
                ->findOne();

            // Insert a new item
            if ($itemrec === null) {

                Logging::setMessage('Item added to the database');

                if ($enable_grabber && ! $nocontent && ! $item->getContent()) {
                    $item->content = download_content_url($item->getUrl());
                }

                $db->table('items')->save(array(
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'url' => $item->getUrl(),
                    'updated' => $item->getDate(),
                    'author' => $item->getAuthor(),
                    'content' => $nocontent ? '' : $item->getContent(),
                    'status' => 'unread',
                    'feed_id' => $feed_id,
                    'enclosure' => $item->getEnclosureUrl(),
                    'enclosure_type' => $item->getEnclosureType(),
                    'language' => $item->getLanguage(),
                ));
            }
            else if (! $itemrec['enclosure'] && $item->getEnclosureUrl()) {

                Logging::setMessage('Update item enclosure');

                $db->table('items')->eq('id', $item->getId())->save(array(
                    'status' => 'unread',
                    'enclosure' => $item->getEnclosureUrl(),
                    'enclosure_type' => $item->getEnclosureType(),
                ));
            }
            // update item enclosure if enclosure has now been set.
            // Some sites add enclosures later without changinging the item id. e.g. NPR, ScientificAmerican
            elseif (isset($item->enclosure) && $item->enclosure && !$itemrec['enclosure']) {

                \PicoFeed\Logging::log('Updated Item media entry the database');
                // \PicoFeed\Logging::log('$item->enclosure: '.$item->enclosure?'true':'false');
                // \PicoFeed\Logging::log('!$itemrec[enclosure]: '.(!$itemrec['enclosure'])?'true':'false');

                $db->table('items')->eq('id', $item->id)->save(array(
                    'status' => 'unread',
                    'enclosure' => $item->enclosure,
                    'enclosure_type' => isset($item->enclosure_type) ? $item->enclosure_type : null,
                    ));
            }
            else {
                Logging::setMessage('Item already in the database');
            }

            // Items inside this feed
            $items_in_feed[] = $item->id;
        }
    }

    // Cleanup old items
    cleanup($feed_id, $items_in_feed);

    $db->closeTransaction();
}

// Remove from the database items marked as "removed"
// and not present inside the feed
function cleanup($feed_id, array $items_in_feed)
{
    if (! empty($items_in_feed)) {

        $db = Database::get('db');

        $removed_items = $db
            ->table('items')
            ->columns('id')
            ->notin('id', $items_in_feed)
            ->eq('status', 'removed')
            ->eq('feed_id', $feed_id)
            ->desc('updated')
            ->findAllByColumn('id');

        // Keep a buffer of 2 items
        // It's workaround for buggy feeds (cache issue with some Wordpress plugins)
        if (is_array($removed_items)) {

            $items_to_remove = array_slice($removed_items, 2);

            if (! empty($items_to_remove)) {

                $nb_items = count($items_to_remove);
                Logging::setMessage('There is '.$nb_items.' items to remove');

                // Handle the case when there is a huge number of items to remove
                // Sqlite have a limit of 1000 sql variables by default
                // Avoid the error message "too many SQL variables"
                // We remove old items by batch of 500 items
                $chunks = array_chunk($items_to_remove, 500);

                foreach ($chunks as $chunk) {

                    $db->table('items')
                        ->in('id', $chunk)
                        ->eq('status', 'removed')
                        ->eq('feed_id', $feed_id)
                        ->remove();
                }
            }
        }
    }
}

// Download content from an URL
function download_content_url($url)
{
    $content = '';

    $grabber = new Grabber($url);
    $grabber->setConfig(Config\get_reader_config());
    $grabber->download();

    if ($grabber->parse()) {
        $content = $grabber->getcontent();
    }

    if (! empty($content)) {
        $filter = Filter::html($content, $url);
        $filter->setConfig(Config\get_reader_config());
        $content = $filter->execute();
    }

    return $content;
}

// Download content from item ID
function download_content_id($item_id)
{
    $item = get($item_id);
    $content = download_content_url($item['url']);

    if (! empty($content)) {

        if (! Config\get('nocontent')) {

            // Save content
            Database::get('db')
                ->table('items')
                ->eq('id', $item['id'])
                ->save(array('content' => $content));
        }

        Config\write_debug();

        return array(
            'result' => true,
            'content' => $content
        );
    }

    Config\write_debug();

    return array(
        'result' => false,
        'content' => ''
    );
}
