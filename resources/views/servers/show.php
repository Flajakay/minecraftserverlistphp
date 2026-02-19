<?php ob_start(); ?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Hero Banner Section -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <?php /** @noinspection PhpUndefinedVariableInspection */
                if ($server->image): ?>
                    <div class="position-relative">
                        <img src="<?= url('/uploads/banners/' . $server->image) ?>" 
                             class="card-img-top" 
                             alt="<?= sanitize($server->name) ?> <?= lang('banner') ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25"></div>
                        <div class="position-absolute bottom-0 start-0 p-4 text-white">
                            <div class="d-flex align-items-center mb-2">
                                <h1 class="h2 fw-bold mb-0 me-3"><?= sanitize($server->name) ?></h1>
                                <?php if ($server->highlight): ?>
                                    <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill me-1"></i><?= lang('premium_server') ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($is_verified)): ?>
                                    <span class="badge bg-success ms-2">
                                        <i class="bi bi-patch-check-fill me-1"></i><?= lang('server_claim_badge_verified') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="mb-2 text-white-50">
                                <i class="bi bi-globe me-1"></i>
                                <?= $server->address ?><?= $server->port != 25565 ? ':' . $server->port : '' ?>
                            </p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> me-2">
                                    <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                                    <?= $server->status ? lang('online') : lang('offline') ?>
                                </span>
                                <?php if ($server->status && $server->max_players > 0): ?>
                                    <span class="badge bg-info">
                                        <i class="bi bi-people me-1"></i>
                                        <?= $server->players ?>/<?= $server->max_players ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-primary text-white p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="d-flex align-items-center mb-2">
                                    <h1 class="h2 fw-bold mb-0 me-3"><?= sanitize($server->name) ?></h1>
                                    <?php if ($server->highlight): ?>
                                        <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i><?= lang('premium_server') ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($is_verified)): ?>
                                        <span class="badge bg-success ms-2">
                                            <i class="bi bi-patch-check-fill me-1"></i><?= lang('server_claim_badge_verified') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-2 text-white-50">
                                    <i class="bi bi-globe me-1"></i>
                                    <?= $server->address ?><?= $server->port != 25565 ? ':' . $server->port : '' ?>
                                </p>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?= $server->status ? 'success' : 'danger' ?> me-2">
                                    <i class="bi bi-<?= $server->status ? 'wifi' : 'wifi-off' ?> me-1"></i>
                                    <?= $server->status ? lang('online') : lang('offline') ?>
                                    </span>
                                    <?php if ($server->status && $server->max_players > 0): ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-people me-1"></i>
                                            <?= $server->players ?>/<?= $server->max_players ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                        

                    </div>
                <?php endif; ?>
            </div>

            <!-- Main Content Tabs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <ul class="nav nav-pills nav-fill" id="serverTabs" role="tablist" aria-label="<?= lang('server_information') ?>">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill" 
                                    id="general-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#general" 
                                    type="button" 
                                    role="tab"
                                    aria-controls="general"
                                    aria-selected="true">
                                <i class="bi bi-info-circle me-1"></i><?= lang('general') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill" 
                                    id="statistics-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#statistics" 
                                    type="button" 
                                    role="tab"
                                    aria-controls="statistics"
                                    aria-selected="false">
                                <i class="bi bi-bar-chart me-1"></i><?= lang('statistics') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill" 
                                    id="comments-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#comments" 
                                    type="button" 
                                    role="tab"
                                    aria-controls="comments"
                                    aria-selected="false">
                                <i class="bi bi-chat-dots me-1"></i><?= lang('comments') ?> (<?= /** @noinspection PhpUndefinedVariableInspection */
                                count($comments) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill" 
                                    id="blog-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#blog" 
                                    type="button" 
                                    role="tab"
                                    aria-controls="blog"
                                    aria-selected="false">
                                <i class="bi bi-journal-text me-1"></i><?= lang('blog') ?> (<?= /** @noinspection PhpUndefinedVariableInspection */
                                $blog_posts_count ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill" 
                                    id="banners-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#banners" 
                                    type="button" 
                                    role="tab"
                                    aria-controls="banners"
                                    aria-selected="false">
                                <i class="bi bi-image me-1"></i><?= lang('banners') ?>
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4">
                    <div class="tab-content" id="serverTabsContent">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="h6 fw-semibold text-dark mb-3">
                                        <i class="bi bi-server text-primary me-2"></i><?= lang('server_information') ?>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-link-45deg me-2"></i><?= lang('server_address') ?>
                                            </span>
                                            <code class="bg-light px-2 py-1 rounded"><?= $server->address ?></code>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-hdd-network me-2"></i><?= lang('server_connection_port') ?>
                                            </span>
                                            <span><?= $server->port ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-folder me-2"></i><?= lang('server_category') ?>
                                            </span>
                                            <span>
                                                <?php 
                                                $displayLimit = 2;
                                                /** @noinspection PhpUndefinedVariableInspection */
                                                $totalCategories = count($categories);
                                                $displayCategories = array_slice($categories, 0, $displayLimit);
                                                $remainingCount = $totalCategories - $displayLimit;
                                                ?>
                                                <?php foreach ($displayCategories as $index => $cat): ?>
                                                    <a href="<?= url('/servers?categories=' . $cat->category_id) ?>" class="text-decoration-none">
                                                        <?= sanitize($cat->category_name) ?>
                                                    </a><?php if ($index < count($displayCategories) - 1): ?>, <?php endif; ?>
                                                <?php endforeach; ?>
                                                <?php if ($remainingCount > 0): ?>
                                                    <?php
                                                    $popoverContent = implode(', ', array_map(function($c) {
                                                        return '<a href="' . url('/servers?categories=' . $c->category_id) . '" class="text-decoration-none">' . sanitize($c->category_name) . '</a>';
                                                    }, array_slice($categories, $displayLimit)));
                                                    ?>
                                                    <button type="button" 
                                                       class="btn btn-link p-0 text-decoration-none text-muted ms-1 align-baseline" 
                                                       data-bs-toggle="popover" 
                                                       data-bs-trigger="hover focus"
                                                       data-bs-placement="bottom"
                                                       data-bs-html="true"
                                                       data-bs-content='<?= $popoverContent ?>'>
                                                        +<?= $remainingCount ?> <?= lang('more') ?>
                                                    </button>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-person me-2"></i><?= lang('owner') ?>
                                            </span>
                                            <span><?= sanitize($server->owner_username) ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-geo-alt me-2"></i><?= lang('location') ?>
                                            </span>
                                            <span>
                                                <img src="<?= url('/assets/flags/' . $server->country . '.png') ?>" 
                                                     width="16" height="11" alt="<?= getCountryName($server->country) ?>" class="me-1">
                                                <?= getCountryName($server->country) ?>
                                            </span>
                                        </div>
                                        <?php if ($server->website): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted">
                                                    <i class="bi bi-link me-2"></i><?= lang('website') ?>
                                                </span>
                                                <span>
                                                <a href="<?= sanitize($server->website) ?>" 
                                                    target="_blank" 
                                                    rel="noopener" 
                                                    class="text-decoration-none">
                                                    <?= sanitize($server->website) ?>
                                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                </a>  
                                                </span>
                                            </div>
                                        <?php endif; ?>



                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="h6 fw-semibold text-dark mb-3">
                                        <i class="bi bi-activity text-primary me-2"></i><?= lang('server_stats') ?>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <?php if ($server->status): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted">
                                                    <i class="bi bi-people me-2"></i><?= lang('players_online') ?>
                                                </span>
                                                <span><?= $server->players ?>/<?= $server->max_players ?></span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted">
                                                    <i class="bi bi-tag me-2"></i><?= lang('version') ?>
                                                </span>
                                                <span class="badge bg-light text-dark"><?= sanitize($server->version) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-arrow-up me-2"></i><?= lang('total_votes') ?>
                                            </span>
                                            <span class="text-success fw-semibold"><?= number_format($server->votes) ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-eye me-2"></i><?= lang('monthly_views') ?>
                                            </span>
                                            <span><?= /** @noinspection PhpUndefinedVariableInspection */
                                                number_format($monthly_hits) ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <span class="text-muted">
                                                <i class="bi bi-clock me-2"></i><?= lang('last_check') ?>
                                            </span>
                                            <span><?= timeAgo($server->last_check) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            
                            <?php if ($server->description): ?>
                                <div class="mt-4">
                                    <div class="h6 fw-semibold text-dark mb-3">
                                        <i class="bi bi-file-text text-primary me-2"></i><?= lang('about_server') ?>
                                    </div>
                                    <div class="p-3 bg-light rounded jodit-content">
                                        <?= displayHtml($server->description) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($server->youtube_id): ?>
                                <div class="mt-4">
                                    <div class="h6 fw-semibold text-dark mb-3">
                                        <i class="bi bi-play-circle text-primary me-2"></i><?= lang('server_showcase') ?>
                                    </div>
                                    <div class="ratio ratio-16x9">
                                        <iframe src="https://www.youtube.com/embed/<?= sanitize($server->youtube_id) ?>" 
                                                title="<?= sanitize($server->name) ?> - <?= lang('server_showcase') ?>"
                                                allowfullscreen 
                                                class="rounded"></iframe>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Statistics Tab -->
                        <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="statistics-tab" tabindex="0">
                            <div class="h6 fw-semibold text-dark mb-4">
                                <i class="bi bi-bar-chart text-primary me-2"></i><?= lang('server_performance') ?>
                            </div>
                            
                            <div class="mb-4">
                                <canvas id="statisticsChart" height="400"></canvas>
                            </div>
                            

                        </div>
                        
                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab" tabindex="0">
                            <?php if (isLoggedIn()): ?>
                                <div class="card border-0 bg-light mb-4">
                                    <div class="card-body">
                                        <form id="commentForm">
                                            <div class="mb-3">
                                                <label for="comment" class="form-label fw-semibold"><?= lang('share_thoughts') ?></label>
                                                <textarea class="form-control border-0 shadow-sm" 
                                                          id="comment" 
                                                          name="comment" 
                                                          rows="3" 
                                                          maxlength="512" 
                                                          placeholder="<?= lang('comment_placeholder') ?>"
                                                          required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-chat-dots me-1"></i><?= lang('post_comment') ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info border-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <a href="<?= url('/login') ?>" class="text-decoration-none"><?= lang('menu.login') ?></a> <?= lang('login_to_comment') ?>
                                </div>
                            <?php endif; ?>
                            
                            <div id="commentsList">
                                <?php if (!empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body">
                                                <?php include dirname(__DIR__) . '/partials/comment-item.php'; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3"><?= lang('no_comments_yet') ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (count($comments) >= 10): ?>
                                <div class="text-center mt-4">
                                    <button id="loadMoreComments" 
                                            class="btn btn-outline-primary" 
                                            data-offset="10">
                                        <?= lang('load_more_comments') ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Blog Tab -->
                        <div class="tab-pane fade" id="blog" role="tabpanel" aria-labelledby="blog-tab" tabindex="0">
                            <?php /** @noinspection PhpUndefinedVariableInspection */
                            if ($is_owner): ?>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="h6 fw-semibold text-dark mb-0">
                                        <i class="bi bi-journal-text text-primary me-2"></i><?= lang('blog') ?>
                                    </div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#blogModal">
                                        <i class="bi bi-plus-lg me-1"></i><?= lang('create_blog_post') ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="h6 fw-semibold text-dark mb-4">
                                    <i class="bi bi-journal-text text-primary me-2"></i><?= lang('blog') ?>
                                </div>
                                <?php if (!isLoggedIn()): ?>
                                    <div class="alert alert-info border-0 mb-4">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <?= lang('only_owner_blog') ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div id="blogPostsList">
                                <?php if (!empty($blog_posts)): ?>
                                    <?php foreach ($blog_posts as $blogPost): ?>
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body">
                                                <?php include dirname(__DIR__) . '/partials/blog-post-item.php'; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3"><?= lang('no_blog_posts') ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php /** @noinspection PhpUndefinedVariableInspection */
                            if (count($blog_posts) >= 5): ?>
                                <div class="text-center mt-4">
                                    <button id="loadMoreBlogPosts" 
                                            class="btn btn-outline-primary" 
                                            data-offset="5">
                                        <?= lang('load_more_posts') ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Banners Tab -->
                        <div class="tab-pane fade" id="banners" role="tabpanel" aria-labelledby="banners-tab" tabindex="0">
                            <div class="h6 fw-semibold text-dark mb-3">
                                <i class="bi bi-megaphone text-primary me-2"></i><?= lang('promote_server') ?>
                            </div>
                            <p class="text-muted mb-4"><?= lang('promote_description') ?></p>
                            
                            <?php 
                            $serverUrl = url("/server/{$server->address}:{$server->port}");
                            $bannerTypes = [
                                ['name' => lang('orange_vote_button'), 'file' => 'vote_orange.png'],
                                ['name' => lang('blue_vote_button'), 'file' => 'vote_blue.png'],
                                ['name' => lang('orange_server_button'), 'file' => 'vote_for_server_orange.png'],
                                ['name' => lang('blue_server_button'), 'file' => 'vote_for_server_blue.png'],
                                ['name' => lang('default_vote_button'), 'file' => 'vote.png']
                            ];
                            ?>
                            
                            <div class="row g-3 mb-5">
                                <?php foreach ($bannerTypes as $banner): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body text-center">
                                                <h6 class="card-title"><?= $banner['name'] ?></h6>
                                                <div class="mb-3">
                                                    <a href="<?= $serverUrl ?>" target="_blank">
                                                        <img src="<?= url('/assets/vote-buttons/' . $banner['file']) ?>" 
                                                             alt="<?= $banner['name'] ?>" 
                                                             class="img-fluid">
                                                    </a>
                                                </div>
                                                <textarea class="form-control small" 
                                                          rows="3" 
                                                          readonly 
                                                          aria-label="<?= $banner['name'] ?> HTML"
                                                          onclick="this.select()">&lt;a href="<?= $serverUrl ?>" target="_blank"&gt;&lt;img src="<?= url('/assets/vote-buttons/' . $banner['file']) ?>"&gt;&lt;/a&gt;</textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            

                                <div class="text-center py-5">
                                    <i class="bi bi-images text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="fw-semibold mb-3">Create Your Custom Animated Banner</h5>
                                    <p class="text-muted mb-4">Generate awesome animated GIF banners with multiple frames, custom text, and backgrounds!</p>
                                    <a href="<?= url('/banner-generator?server_id=' . $server->id) ?>" class="btn btn-primary">
                                        <i class="bi bi-magic me-1"></i>Open Banner Generator
                                    </a>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Buttons -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i><?= lang('quick_actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isLoggedIn()): ?>
                        <?php /** @noinspection PhpUndefinedVariableInspection */
                        if ($can_vote): ?>
                            <button type="button" 
                                    class="btn btn-success w-100 mb-3 py-2" 
                                    onclick="voteForServer(<?= $server->id ?>)">
                                <i class="bi bi-arrow-up me-2"></i><?= lang('vote_for_server') ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-light w-100 mb-3 py-2" disabled>
                                <i class="bi bi-check-circle me-2"></i><?= lang('already_voted') ?>
                            </button>
                        <?php endif; ?>

                        <?php if (empty($is_verified)): ?>
                            <a class="btn btn-outline-primary w-100 mb-3 py-2" href="<?= url('/server-claim/' . $server->id) ?>">
                                <i class="bi bi-shield-lock me-2"></i><?= $is_owner ? lang('server_verify_cta') : lang('server_claim_cta') ?>
                            </a>
                        <?php endif; ?>
                        
                        <button type="button" 
                                class="btn btn-outline-warning w-100 mb-3 py-2" 
                                onclick="reportServer(<?= $server->id ?>)">
                            <i class="bi bi-flag me-2"></i><?= lang('report_server') ?>
                        </button>
						
						<?php if ($is_owner): ?>
	                        <button type="button" 
                                class="btn btn-outline-info w-100 mb-3 py-2" 
                                onclick="editServer(<?= $server->id ?>)">
								<i class="bi bi-pencil me-2"></i><?= lang('edit_server') ?>
							</button>
						<?php endif; ?>
						
                    <?php else: ?>
                        <div class="alert alert-info border-0 mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <a href="<?= url('/login') ?>" class="text-decoration-none"><?= lang('menu.login') ?></a> <?= lang('login_to_vote') ?>
                        </div>
                    <?php endif; ?>
                    
                    <button type="button" 
                            class="btn btn-outline-secondary w-100 py-2" 
                            onclick="copyServerAddress()">
                        <i class="bi bi-clipboard me-2"></i><?= lang('copy_server_address') ?>
                    </button>
					

					
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Blog Modal -->
<div class="modal fade" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold" id="blogModalLabel">
                    <i class="bi bi-journal-text text-primary me-2"></i><?= lang('create_blog_post') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="blogForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="blogTitle" class="form-label fw-semibold"><?= lang('blog_title') ?> *</label>
                        <input type="text" 
                               class="form-control" 
                               id="blogTitle" 
                               name="title" 
                               maxlength="255" 
                               placeholder="<?= lang('blog_title_placeholder') ?>"
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="blogContent" class="form-label fw-semibold"><?= lang('blog_content') ?> *</label>
                        <textarea id="blogContent" 
                                  name="content" 
                                  class="form-control"
                                  rows="8"
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= lang('cancel') ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i><?= lang('save_blog_post') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold" id="reportModalLabel">
                    <i class="bi bi-flag text-warning me-2"></i><?= lang('report_server') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reportReason" class="form-label fw-semibold"><?= lang('reason_for_reporting') ?></label>
                        <textarea class="form-control" 
                                  id="reportReason" 
                                  name="message" 
                                  rows="4" 
                                  maxlength="512" 
                                  required 
                                  placeholder="<?= lang('report_placeholder') ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= lang('cancel') ?></button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-flag me-1"></i><?= lang('submit_report') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= asset('css/category-popover.css') ?>">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php joditAssets(); ?>

<script src="<?= asset('js/server-show.js') ?>"></script>

<script>
window.serverStatistics = <?= /** @noinspection PhpUndefinedVariableInspection */json_encode($statistics) ?>;
window.lang = {
    content_placeholder: '<?= lang('content_placeholder') ?>',
    blog_content_required: '<?= lang('blog_content_required') ?>',
    confirm_delete: '<?= lang('confirm_delete') ?>',
    jodit_code: '<?= lang('_jodit_code', 'en') ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    window.serverShowInstance = new ServerShow(
        <?= $server->id ?>,
        '<?= $server->address ?>',
        <?= $server->port ?>,
        '<?= csrf() ?>'
    );
    
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        const popover = new bootstrap.Popover(popoverTriggerEl, {
            trigger: 'manual'
        });

        let isHoveringTrigger = false;
        let isHoveringPopover = false;

        const showPopover = () => {
            popoverTriggerEl.setAttribute('aria-expanded', 'true');
            popover.show();

            setTimeout(function() {
                const popoverElement = document.querySelector('.popover');
                if (!popoverElement) return;

                popoverElement.addEventListener('mouseenter', function() {
                    isHoveringPopover = true;
                });

                popoverElement.addEventListener('mouseleave', function() {
                    isHoveringPopover = false;
                    setTimeout(function() {
                        if (!isHoveringTrigger && !isHoveringPopover) {
                            hidePopover();
                        }
                    }, 100);
                });
            }, 10);
        };

        const hidePopover = () => {
            popoverTriggerEl.setAttribute('aria-expanded', 'false');
            popover.hide();
        };

        popoverTriggerEl.setAttribute('aria-haspopup', 'true');
        popoverTriggerEl.setAttribute('aria-expanded', 'false');

        popoverTriggerEl.addEventListener('mouseenter', function() {
            isHoveringTrigger = true;
            showPopover();
        });

        popoverTriggerEl.addEventListener('mouseleave', function() {
            isHoveringTrigger = false;
            setTimeout(function() {
                if (!isHoveringTrigger && !isHoveringPopover) {
                    hidePopover();
                }
            }, 100);
        });

        popoverTriggerEl.addEventListener('focus', function() {
            showPopover();
        });

        popoverTriggerEl.addEventListener('blur', function() {
            setTimeout(function() {
                if (!isHoveringTrigger && !isHoveringPopover) {
                    hidePopover();
                }
            }, 100);
        });

        popoverTriggerEl.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hidePopover();
                popoverTriggerEl.blur();
            }
        });

        return popover;
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php $title = sanitize($server->name) . ' - ' . lang('minecraft_server') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
