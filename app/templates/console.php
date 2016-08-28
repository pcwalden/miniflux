<div class="page-header">
    <h2><?= t('Console') ?></h2>
    <ul>
        <li><a href="?action=console"><?= t('refresh') ?></a></li>
        <li><a href="?action=flush-console"><?= t('flush messages') ?></a></li>
    </ul>
</div>

<?php if (empty($content)): ?>
    <p class="alert alert-info"><?= t('Nothing to show. Enable the debug mode to see log messages.') ?></p>
<?php else: ?>
    <pre id="console"><code><?= Miniflux\Helper\escape($content) ?></code></pre>
<?php endif ?>
