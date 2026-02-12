<?php ob_start(); ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-journal-text text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1"><?= lang('blog_posts_management') ?></h2>
                        <p class="text-muted mb-0"><?= sprintf(lang('total_results'), number_format($total_blog_posts)) ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i><?= lang('admin_panel') ?>
                    </span>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-funnel text-primary me-2"></i><?= lang('search_and_filters') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text"
                                           name="search"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="<?= lang('search_posts_authors_servers') ?>"
                                           value="<?= /** @noinspection PhpUndefinedVariableInspection */sanitize($search) ?>">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="bi bi-search me-1"></i><?= lang('search_button') ?>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-8">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <input type="hidden" name="search" value="<?= sanitize($search) ?>">

                                <div class="input-group" style="max-width: 260px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-server text-muted"></i>
                                    </span>
                                    <select name="server_id" class="form-select border-start-0">
                                        <option value=""><?= lang('all_servers') ?></option>
                                        <?php /** @noinspection PhpUndefinedVariableInspection */foreach ($servers as $server): ?>
                                            <option value="<?= $server->id ?>" <?= /** @noinspection PhpUndefinedVariableInspection */$filters['server_id'] == $server->id ? 'selected' : '' ?>>
                                                <?= sanitize($server->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="input-group" style="max-width: 220px;">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <select name="user_id" class="form-select border-start-0">
                                        <option value=""><?= lang('all_authors') ?></option>
                                        <?php /** @noinspection PhpUndefinedVariableInspection */foreach ($users as $user): ?>
                                            <option value="<?= $user->id ?>" <?= /** @noinspection PhpUndefinedVariableInspection */$filters['user_id'] == $user->id ? 'selected' : '' ?>>
                                                <?= sanitize($user->username) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-funnel me-1"></i><?= lang('filter') ?>
                                </button>
                                <a href="<?= url('/admin/blog-posts') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i><?= lang('clear_filters') ?>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i class="bi bi-table text-primary me-2"></i><?= lang('blog_posts_management') ?>
                        </h6>
                        <?php if (!empty($blog_posts)): ?>
                            <small class="text-muted"><?= sprintf(lang('total_results'), number_format($total_blog_posts)) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-0">
            <?php if (!empty($blog_posts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">
                                    <i class="bi bi-hash me-1"></i>ID
                                </th>
                                <th class="fw-semibold">
                                    <i class="bi bi-type me-1"></i><?= lang('blog_post_title') ?>
                                </th>
                                <th class="fw-semibold">
                                    <i class="bi bi-person me-1"></i><?= lang('blog_post_author') ?>
                                </th>
                                <th class="fw-semibold">
                                    <i class="bi bi-server me-1"></i><?= lang('blog_post_server') ?>
                                </th>
                                <th class="fw-semibold">
                                    <i class="bi bi-calendar me-1"></i><?= lang('blog_post_date') ?>
                                </th>
                                <th class="fw-semibold text-center" width="120">
                                    <i class="bi bi-tools me-1"></i><?= lang('tools') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                                <tr data-blog-id="<?= $post->id ?>">
                                    <td>
                                        <span class="badge bg-light text-dark"><?= $post->id ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= sanitize($post->title) ?></div>
                                        <div class="text-muted small">
                                            <?= sanitize(substr(strip_tags($post->content), 0, 100)) ?>...
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                    <span class="text-white fw-bold" style="font-size: 0.75rem;">
                                                        <?= strtoupper(substr($post->username ?? 'U', 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= sanitize($post->name ?? $post->username) ?></div>
                                                <div class="text-muted small">@<?= sanitize($post->username) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= url("/server/{$post->address}:{$post->port}") ?>" class="text-decoration-none">
                                            <?= sanitize($post->server_name) ?>
                                        </a>
                                        <div class="text-muted small"><?= $post->address ?>:<?= $post->port ?></div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><?= date('M j, Y', strtotime($post->created_at)) ?></div>
                                            <div class="text-muted"><?= date('H:i', strtotime($post->created_at)) ?></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm position-static">
                                            <button type="button"
                                                    class="btn btn-outline-danger"
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

                <?php /** @noinspection PhpUndefinedVariableInspection */if ($total_pages > 1): ?>
                    <div class="card-footer bg-light border-0">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php /** @noinspection PhpUndefinedVariableInspection */if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">
                                            <i class="bi bi-chevron-left"></i> <?= lang('previous') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                                            <?= lang('next') ?> <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3 mb-0"><?= lang('no_results') ?></p>
                </div>
            <?php endif; ?>
                </div>
            </div>
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
