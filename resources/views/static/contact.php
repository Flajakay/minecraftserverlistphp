<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-envelope text-primary" style="font-size: 2.5rem;" aria-hidden="true"></i>
                </div>
                <h1 class="h3 fw-bold text-dark"><?= lang('headers.contact') ?></h1>
                <p class="text-muted"><?= lang('contact_subtitle') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= url('/contact') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold"><?= lang('name') ?> *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted" aria-hidden="true"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0" 
                                           id="name" 
                                           name="name" 
                                           value="<?= old('name') ?>" 
                                           autocomplete="name"
                                           placeholder="<?= lang('name_placeholder') ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold"><?= lang('email') ?> *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-muted" aria-hidden="true"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control border-start-0 ps-0" 
                                           id="email" 
                                           name="email" 
                                           value="<?= old('email') ?>" 
                                           autocomplete="email"
                                           placeholder="<?= lang('email_placeholder') ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold"><?= lang('subject') ?> *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-chat-left-text text-muted" aria-hidden="true"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="subject" 
                                       name="subject" 
                                       value="<?= old('subject') ?>" 
                                       placeholder="<?= lang('subject_placeholder') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold"><?= lang('message') ?> *</label>
                            <textarea class="form-control" 
                                      id="message" 
                                      name="message" 
                                      rows="6" 
                                      placeholder="<?= lang('message_placeholder') ?>"
                                      required><?= old('message') ?></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-semibold">
                                <i class="bi bi-send me-2" aria-hidden="true"></i><?= lang('send_message') ?>
                            </button>
                            <p class="text-muted mt-3 mb-0 small">
                                <?= lang('contact_response_note') ?>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Contact Info Section -->
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded-3">
                        <i class="bi bi-clock text-primary fs-2 mb-3" aria-hidden="true"></i>
                        <h5 class="h6 fw-semibold"><?= lang('response_time') ?></h5>
                        <p class="text-muted mb-0 small"><?= lang('response_time_desc') ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded-3">
                        <i class="bi bi-shield-check text-success fs-2 mb-3" aria-hidden="true"></i>
                        <h5 class="h6 fw-semibold"><?= lang('privacy') ?></h5>
                        <p class="text-muted mb-0 small"><?= lang('privacy_desc') ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-light rounded-3">
                        <i class="bi bi-headset text-info fs-2 mb-3" aria-hidden="true"></i>
                        <h5 class="h6 fw-semibold"><?= lang('support') ?></h5>
                        <p class="text-muted mb-0 small"><?= lang('support_desc') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('titles.contact'); ?>
<?php include layout('app'); ?>
