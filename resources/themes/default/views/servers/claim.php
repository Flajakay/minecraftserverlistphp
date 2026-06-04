<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-shield-lock text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('server_claim_title') ?></h2>
                <p class="text-muted mb-0">
                    <?= /** @noinspection PhpUndefinedVariableInspection */
                    sanitize($server->name) ?>
                    <span class="ms-2">(<?= sanitize($server->address) ?><?= ($server->protocol === 'minecraft_java' && $server->port == 25565) || ($server->protocol === 'minecraft_bedrock' && $server->port == 19132) ? '' : ':' . sanitize($server->port) ?>)</span>
                </p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php
                    /** @noinspection PhpUndefinedVariableInspection */
                    $isMinecraft = ($server->protocol ?? 'minecraft_java') === 'minecraft_java';
                    ?>
                    <?php if (!$isMinecraft): ?>
                        <div class="alert alert-info border-0">
                            <div class="fw-semibold mb-2"><?= lang('server_claim_not_supported_title') ?></div>
                            <div><?= lang('server_claim_not_supported_body') ?></div>
                        </div>
                    <?php /** @noinspection PhpUndefinedVariableInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    elseif ($is_pending && $is_requestor): ?>
                        <div class="alert alert-info border-0">
                            <div class="fw-semibold mb-2"><?= lang('server_claim_instructions_title') ?></div>
                            <div class="mb-3"><?= lang('server_claim_instructions_body') ?></div>
                            <div class="mb-2">
                                <span class="text-muted"><?= lang('server_claim_token_label') ?></span>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <code class="bg-light px-3 py-2 rounded flex-grow-1"><?= sanitize($server->verification_token) ?></code>
                                </div>
                                <?php if (!empty($server->verification_token_expires_at)): ?>
                                    <div class="small text-muted mt-2">
                                        <?= lang('server_claim_token_expires') ?>: <?= sanitize($server->verification_token_expires_at) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-center pt-2">
                            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                <form method="POST" action="<?= url("/server-claim/{$server->id}/verify") ?>" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                                        <i class="bi bi-shield-check me-1"></i><?= lang('server_claim_verify_now') ?>
                                    </button>
                                </form>

                                <form method="POST" action="<?= url("/server-claim/{$server->id}/cancel") ?>" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                    <button type="submit" class="btn btn-light px-5 py-2 fw-semibold">
                                        <?= lang('server_claim_cancel') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0">
                            <div class="fw-semibold mb-2"><?= lang('server_claim_start_title') ?></div>
                            <div><?= lang('server_claim_start_body') ?></div>
                        </div>

                        <div class="text-center pt-2">
                            <form method="POST" action="<?= url("/server-claim/{$server->id}/start") ?>" class="m-0">
                                <input type="hidden" name="csrf_token" value="<?= csrf() ?>">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-semibold">
                                    <i class="bi bi-shield-lock me-1"></i><?= lang('server_claim_start') ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('server_claim_title'); ?>
<?php include layout('app'); ?>
