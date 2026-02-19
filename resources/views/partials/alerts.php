<?php $successMessage = getFlash('success'); ?>
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle" aria-hidden="true"></i> <?= $successMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= lang('close') ?>"></button>
    </div>
<?php endif; ?>

<?php $errorMessage = getFlash('error'); ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i> <?= $errorMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= lang('close') ?>"></button>
    </div>
<?php endif; ?>

<?php $infoMessage = getFlash('info'); ?>
<?php if ($infoMessage): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle" aria-hidden="true"></i> <?= $infoMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= lang('close') ?>"></button>
    </div>
<?php endif; ?>
