<?php if (empty($items)): ?>
    <p class="alert alert-info"><?= t('No history') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?= t('History') ?><span id="page-counter"><?= isset($nb_items) ? $nb_items : '' ?></span></h2>
        <ul>
            <li><a href="?action=confirm-flush-history"><?= t('flush all items') ?></a></li>
        </ul>
    </div>

    <?php if ($nothing_to_read): ?>
        <p class="alert alert-info"><?= t('There is nothing new to read, enjoy your previous readings!') ?></p>
    <?php endif ?>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?= \PicoFarad\Template\load('item', array(
                'item' => $item,
                'menu' => $menu,
                'offset' => $offset,
                'hide' => true,
                'display_mode' => $display_mode,
                'favicons' => $favicons,
                'original_marks_read' => $original_marks_read,
            )) ?>
        <?php endforeach ?>

        <?= \PicoFarad\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>
