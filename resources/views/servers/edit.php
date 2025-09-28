<?php ob_start(); ?>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
		<div class="text-center mb-4">
			<div class="mb-3">
				<i class="bi bi-pencil-square text-primary" style="font-size: 2.5rem;"></i>
			</div>
			<h2 class="h4 fw-bold text-dark mb-1"><?= lang('edit_server') ?></h2>
			<p class="text-muted"><?= lang('submit_subtitle') ?></p>
		</div>
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php
                    $config = [
                        'isAdmin' => false
                    ];
                    ?>
					<?php include __DIR__ . '/../partials/server-edit-form.php'; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <?php include __DIR__ . '/../partials/server-sidebar.php'; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.edit_server') . ': ' . htmlspecialchars($server->name) . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
