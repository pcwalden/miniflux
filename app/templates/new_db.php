<div class="page-header">
    <h2><?= t('New database') ?></h2>
    <nav>
        <ul>
            <li><a href="?action=config"><?= t('general') ?></a></li>
            <li><a href="?action=services"><?= t('external services') ?></a></li>
            <li><a href="?action=api"><?= t('api') ?></a></li>
            <li class="active"><a href="?action=database"><?= t('database') ?></a></li>
            <li><a href="?action=help"><?= t('help') ?></a></li>
            <li><a href="?action=about"><?= t('about') ?></a></li>
        </ul>
    </nav>
</div>

<form method="post" action="?action=new-db" autocomplete="off">

    <?= Miniflux\Helper\form_hidden('csrf', $values) ?>

    <?= Miniflux\Helper\form_label(t('Database name'), 'name') ?>
    <?= Miniflux\Helper\form_text('name', $values, $errors, array('required', 'autofocus')) ?>
    <p class="form-help"><?= t('The name must have only alpha-numeric characters') ?></p>

    <?= Miniflux\Helper\form_label(t('Username'), 'username') ?>
    <?= Miniflux\Helper\form_text('username', $values, $errors, array('required')) ?><br/>

    <?= Miniflux\Helper\form_label(t('Password'), 'password') ?>
    <?= Miniflux\Helper\form_password('password', $values, $errors, array('required')) ?>

    <?= Miniflux\Helper\form_label(t('Confirmation'), 'confirmation') ?>
    <?= Miniflux\Helper\form_password('confirmation', $values, $errors, array('required')) ?><br/>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Create') ?></button>
        <?= t('or') ?> <a href="?action=config"><?= t('cancel') ?></a>
    </div>
</form>
