<?php ob_start(); ?>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
		<div class="text-center">
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

<!-- Load Jodit Helper -->
<?php joditAssets(); ?>

<?php echo joditScript([
    [
        'selector' => '#description',
        'type' => 'page',
        'placeholder' => lang('description_placeholder')
    ]
]); ?>

<?php $content = ob_get_clean(); ?>
<?php /** @noinspection PhpUndefinedVariableInspection */
$title = lang('titles.edit_server') . ': ' . sanitize($server->name) . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
