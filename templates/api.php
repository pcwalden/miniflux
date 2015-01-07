<div class="page-header">
    <h2><?= t('API') ?></h2>
    <ul>
        <li><a href="?action=config"><?= t('settings') ?></a></li>
        <li><a href="?action=services"><?= t('external services') ?></a></li>
        <li><a href="?action=about"><?= t('about') ?></a></li>
        <li><a href="?action=help"><?= t('help') ?></a></li>
        <li><a href="?action=database"><?= t('database') ?></a></li>
    </ul>
</div>
<section>
    <div class="alert alert-normal">
        <h3 id="fever"><?= t('Fever API') ?></h3>
        <ul>
            <li><?= t('API endpoint:') ?> <strong><?= Helper\get_current_base_url().'fever/' ?></strong></li>
            <li><?= t('API username:') ?> <strong><?= Helper\escape($config['username']) ?></strong></li>
            <li><?= t('API token:') ?> <strong><?= Helper\escape($config['fever_token']) ?></strong></li>
        </ul>
    </div>
    <div class="alert alert-normal">
        <h3 id="api"><?= t('Miniflux API') ?></h3>
        <ul>
            <li><?= t('API endpoint:') ?> <strong><?= Helper\get_current_base_url().'jsonrpc.php' ?></strong></li>
            <li><?= t('API username:') ?> <strong><?= Helper\escape($config['username']) ?></strong></li>
            <li><?= t('API token:') ?> <strong><?= Helper\escape($config['api_token']) ?></strong></li>
        </ul>
    </div>
</section>
