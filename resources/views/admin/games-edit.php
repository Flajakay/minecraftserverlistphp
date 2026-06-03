<?php ob_start(); ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit Game</h2>
                <a href="/admin/games" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Games
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editing: <?= sanitize($game->name) ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= sanitize($game->name) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="steam_app_id" class="form-label">Steam App ID</label>
                            <input type="number" class="form-control" id="steam_app_id" name="steam_app_id"
                                   value="<?= $game->steam_app_id ?>" placeholder="730">
                        </div>
                        <div class="mb-3">
                            <label for="protocol" class="form-label">Protocol</label>
                                <select class="form-select" id="protocol" name="protocol">
                                    <option value="steam_a2s" <?= $game->protocol === 'steam_a2s' ? 'selected' : '' ?>>Steam</option>
                                </select>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enabled" name="enabled"
                                    <?= $game->enabled ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="enabled">Enabled</label>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Submit
                            </button>
                            <a href="/admin/games" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = 'Edit Game'; ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
