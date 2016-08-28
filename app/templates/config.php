<div class="page-header">
    <h2><?= $title ?></h2>
    <nav>
        <ul>
            <li class="active"><a href="?action=config"><?= t('general') ?></a></li>
            <li><a href="?action=services"><?= t('external services') ?></a></li>
            <li><a href="?action=api"><?= t('api') ?></a></li>
            <li><a href="?action=database"><?= t('database') ?></a></li>
            <li><a href="?action=help"><?= t('help') ?></a></li>
            <li><a href="?action=about"><?= t('about') ?></a></li>
        </ul>
    </nav>
</div>
<section>
<form method="post" action="?action=config" autocomplete="off" id="config-form">

    <h3><?= t('Authentication') ?></h3>
    <div class="options">
        <?= Miniflux\Helper\form_hidden('csrf', $values) ?>
        <?= Miniflux\Helper\form_label(t('Username'), 'username') ?>
        <?= Miniflux\Helper\form_text('username', $values, $errors, array('required')) ?><br/>

        <?= Miniflux\Helper\form_label(t('Password'), 'password') ?>
        <?= Miniflux\Helper\form_password('password', $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Confirmation'), 'confirmation') ?>
        <?= Miniflux\Helper\form_password('confirmation', $values, $errors) ?><br/>
    </div>

    <h3><?= t('Application') ?></h3>
    <div class="options">
        <?= Miniflux\Helper\form_label(t('Timezone'), 'timezone') ?>
        <?= Miniflux\Helper\form_select('timezone', $timezones, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Language'), 'language') ?>
        <?= Miniflux\Helper\form_select('language', $languages, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Theme'), 'theme') ?>
        <?= Miniflux\Helper\form_select('theme', $theme_options, $values, $errors) ?><br/>

        <?php if (ENABLE_AUTO_UPDATE): ?>
            <?= Miniflux\Helper\form_label(t('Auto-Update URL'), 'auto_update_url') ?>
            <?= Miniflux\Helper\form_text('auto_update_url', $values, $errors, array('required')) ?><br/>
        <?php endif ?>

        <?= Miniflux\Helper\form_checkbox('debug_mode', t('Enable debug mode'), 1, isset($values['debug_mode']) && $values['debug_mode'] == 1) ?><br/>

        <?= Miniflux\Helper\form_checkbox('image_proxy', t('Enable image proxy'), 1, isset($values['image_proxy']) && $values['image_proxy'] == 1) ?>
        <div class="form-help"><?= t('Avoid mixed content warnings with HTTPS') ?></div>
    </div>

    <h3><?= t('Reading') ?></h3>
    <div class="options">
        <?= Miniflux\Helper\form_label(t('Remove automatically read items'), 'autoflush') ?>
        <?= Miniflux\Helper\form_select('autoflush', $autoflush_read_options, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Remove automatically unread items'), 'autoflush_unread') ?>
        <?= Miniflux\Helper\form_select('autoflush_unread', $autoflush_unread_options, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Items per page'), 'items_per_page') ?>
        <?= Miniflux\Helper\form_select('items_per_page', $paging_options, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Default sorting order for items'), 'items_sorting_direction') ?>
        <?= Miniflux\Helper\form_select('items_sorting_direction', $sorting_options, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Display items on lists'), 'items_display_mode') ?>
        <?= Miniflux\Helper\form_select('items_display_mode', $display_mode, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Item title links to'), 'item_title_link') ?>
        <?= Miniflux\Helper\form_select('item_title_link', $item_title_link, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('When there is nothing to read, redirect me to this page'), 'redirect_nothing_to_read') ?>
        <?= Miniflux\Helper\form_select('redirect_nothing_to_read', $redirect_nothing_to_read_options, $values, $errors) ?><br/>

        <?= Miniflux\Helper\form_label(t('Refresh interval in minutes for unread counter'), 'frontend_updatecheck_interval') ?>
        <?= Miniflux\Helper\form_number('frontend_updatecheck_interval', $values, $errors, array('min="0"')) ?><br/>

        <?= Miniflux\Helper\form_checkbox('original_marks_read', t('Original link marks article as read'), 1, isset($values['original_marks_read']) && $values['original_marks_read'] == 1) ?><br/>
        <?= Miniflux\Helper\form_checkbox('nocontent', t('Do not fetch the content of articles'), 1, isset($values['nocontent']) && $values['nocontent'] == 1) ?><br/>
        <?= Miniflux\Helper\form_checkbox('favicons', t('Download favicons'), 1, isset($values['favicons']) && $values['favicons'] == 1) ?><br/>
    </div>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>
</section>

<div class="page-section">
    <h2><?= t('Advanced') ?></h2>
</div>
<section class="panel panel-danger">
<ul>
    <li><a href="?action=generate-tokens&amp;csrf=<?= $values['csrf'] ?>"><?= t('Generate new tokens') ?></a> (<?= t('Miniflux API') ?>, <?= t('Fever API') ?>, <?= t('Bookmarklet') ?>, <?= t('Bookmark RSS Feed') ?>)</li>
<?php if (ENABLE_AUTO_UPDATE): ?>
    <li><a href="?action=confirm-auto-update"><?= t('Update Miniflux') ?></a> (<?= t('Don\'t forget to backup your database') ?>)</li>
<?php endif ?>
</ul>
</section>
