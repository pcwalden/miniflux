<div class="page-header">
    <h2><?= t('Confirmation') ?></h2>
</div>

<p class="alert alert-info"><?= t('This action will update Miniflux with the last development version, are you sure?') ?></p>

<div class="form-actions">
    <a href="?action=auto-update" class="btn btn-red"><?= t('Update Miniflux') ?></a>
    <?= t('or') ?> <a href="?action=config"><?= t('cancel') ?></a>
</div>