<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-semibold"><?= lang('blog_posts_management') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('/admin/settings') ?>"><?= lang('menu.admin') ?></a></li>
                <li class="breadcrumb-item active"><?= lang('blog_posts_management') ?></li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold"><?= lang('search') ?></label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="<?= lang('search_posts_authors_servers') ?>"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold"><?= lang('blog_post_server') ?></label>
                    <select name="server_id" class="form-select">
                        <option value=""><?= lang('all_servers') ?></option>
                        <?php foreach ($servers as $server): ?>
                            <option value="<?= $server->id ?>" <?= $filters['server_id'] == $server->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($server->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold"><?= lang('blog_post_author') ?></label>
                    <select name="user_id" class="form-select">
                        <option value=""><?= lang('all_authors') ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->id ?>" <?= $filters['user_id'] == $user->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user->username) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i><?= lang('filter') ?>
                        </button>
                        <a href="<?= url('/admin/blog-posts') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <?php if (!empty($blog_posts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= lang('blog_post_title') ?></th>
                                <th><?= lang('blog_post_author') ?></th>
                                <th><?= lang('blog_post_server') ?></th>
                                <th><?= lang('blog_post_date') ?></th>
                                <th width="100"><?= lang('tools') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                                <tr data-blog-id="<?= $post->id ?>">
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($post->title) ?></div>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars(substr(strip_tags($post->content), 0, 100)) ?>...
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <div class="fw-semibold"><?= htmlspecialchars($post->name ?? $post->username) ?></div>
                                                <div class="text-muted small">@<?= htmlspecialchars($post->username) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= url("/server/{$post->address}:{$post->port}") ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($post->server_name) ?>
                                        </a>
                                        <div class="text-muted small"><?= $post->address ?>:<?= $post->port ?></div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><?= date('M j, Y', strtotime($post->created_at)) ?></div>
                                            <div class="text-muted"><?= date('H:i', strtotime($post->created_at)) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteBlogPost(<?= $post->id ?>)"
                                                    title="<?= lang('delete') ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <?= sprintf(lang('total_results'), $total_blog_posts) ?>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3"><?= lang('no_results') ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteBlogPost(id) {
    if (!confirm('<?= lang('confirm_delete') ?>')) return;
    
    fetch(`<?= url('/admin/blog-posts/delete/') ?>${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            csrf_token: '<?= csrf() ?>',
            ajax: '1'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`tr[data-blog-id="${id}"]`).remove();
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message || '<?= lang('error_occurred') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', '<?= lang('error_occurred') ?>');
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}
</script>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.blog_posts_management') . ' - ' . setting('title');  ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
