<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-activity text-primary me-2"></i><?= lang('server_status') ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> me-2">
                <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                <?= $server->status ? lang('active') : lang('inactive') ?>
            </span>
            <?php if ($server->status): ?>
                <span class="text-muted"><?= $server->players ?>/<?= $server->max_players ?> <?= lang('players') ?></span>
            <?php endif; ?>
        </div>
        
        <div class="row text-center g-3">
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <div class="h5 text-success mb-0"><?= number_format($server->votes) ?></div>
                    <small class="text-muted"><?= lang('votes') ?></small>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded">
                    <div class="h5 text-<?= $server->active ? 'success' : 'warning' ?> mb-0">
                        <?= $server->active ? lang('active') : lang('inactive') ?>
                    </div>
                    <small class="text-muted"><?= lang('server_status') ?></small>
                </div>
            </div>
        </div>
        
        <?php if ($server->last_check): ?>
            <div class="mt-3 p-2 bg-light rounded">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i><?= lang('last_checked') ?>: <?= timeAgo($server->last_check) ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-gear text-primary me-2"></i><?= lang('server_settings') ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
            <span class="fw-semibold"><?= lang('visibility') ?></span>
            <span class="badge bg-<?= $server->private ? 'warning' : 'success' ?>">
                <i class="bi bi-<?= $server->private ? 'lock' : 'unlock' ?> me-1"></i>
                <?= $server->private ? lang('private') : lang('public') ?>
            </span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
            <span class="fw-semibold"><?= lang('premium_status') ?></span>
            <span class="badge bg-<?= $server->highlight ? 'warning' : 'secondary' ?>">
                <i class="bi bi-<?= $server->highlight ? 'star-fill' : 'star' ?> me-1"></i>
                <?= $server->highlight ? lang('premium') : lang('standard') ?>
            </span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
            <span class="fw-semibold"><?= lang('created') ?></span>
            <small class="text-muted"><?= date('M j, Y', strtotime($server->created_at)) ?></small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-lightning text-primary me-2"></i><?= lang('quick_actions') ?>
        </h6>
    </div>
    <div class="card-body">
        <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" 
           class="btn btn-primary w-100 mb-2">
            <i class="bi bi-eye me-2"></i><?= lang('view_server_page') ?>
        </a>
        
        <?php if (isset($isAdmin) && $isAdmin): ?>
            <form method="POST" action="<?= url('/admin/servers/action/' . $server->id) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="<?= $server->active ? 'deactivate' : 'activate' ?>">
                <button type="submit" 
                        class="btn btn-outline-secondary w-100 mb-2" 
                        onclick="return confirm('<?= $server->active ? lang('confirm_deactivate') : lang('confirm_activate') ?>')">
                    <i class="bi bi-<?= $server->active ? 'pause' : 'play' ?> me-2"></i><?= $server->active ? lang('deactivate') : lang('activate') ?>
                </button>
            </form>
            
            <form method="POST" action="<?= url('/admin/servers/action/' . $server->id) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="<?= $server->highlight ? 'remove_highlight' : 'add_highlight' ?>">
                <button type="submit" 
                        class="btn btn-outline-warning w-100 mb-2">
                    <i class="bi bi-<?= $server->highlight ? 'star-fill' : 'star' ?> me-2"></i><?= $server->highlight ? lang('server_remove_highlight') : lang('server_highlight') ?>
                </button>
            </form>
        <?php endif; ?>
        
        <?php if ($server->private): ?>
            <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="make_public">
                <button type="submit" 
                        class="btn btn-outline-secondary w-100 mb-2" 
                        onclick="return confirm('<?= lang('confirm_make_public') ?>')">
                    <i class="bi bi-unlock me-2"></i><?= lang('make_public') ?>
                </button>
            </form>
        <?php else: ?>
            <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="make_private">
                <button type="submit" 
                        class="btn btn-outline-secondary w-100 mb-2" 
                        onclick="return confirm('<?= lang('confirm_make_private') ?>')">
                    <i class="bi bi-lock me-2"></i><?= lang('make_private') ?>
                </button>
            </form>
        <?php endif; ?>
        
        <form method="POST" action="<?= url('/server-action/' . $server->id) ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" 
                    class="btn btn-outline-danger w-100" 
                    onclick="return confirm('<?= lang('confirm_delete_server') ?>')">
                <i class="bi bi-trash me-2"></i><?= lang('server_delete') ?>
            </button>
        </form>
    </div>
</div>
