<?php
$successMessage = getFlash('success');
$errorMessage = getFlash('error');
$infoMessage = getFlash('info');

$hasMessage = $successMessage || $errorMessage || $infoMessage;
if ($hasMessage):
    $type = 'info';
    $title = lang('notice', 'Notice');
    $message = '';
    $icon = 'bi-info-circle-fill';
    $colorClass = 'text-primary';
    
    if ($successMessage) {
        $type = 'success';
        $title = lang('success', 'Success!');
        $message = $successMessage;
        $icon = 'bi-check-circle-fill';
        $colorClass = 'text-success';
    } elseif ($errorMessage) {
        $type = 'error';
        $title = lang('error', 'Error!');
        $message = $errorMessage;
        $icon = 'bi-exclamation-triangle-fill';
        $colorClass = 'text-danger';
    } else {
        $type = 'info';
        $title = lang('info', 'Information');
        $message = $infoMessage;
        $icon = 'bi-info-circle-fill';
        $colorClass = 'text-primary';
    }
?>
<!-- Premium Non-Intrusive Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
    <div id="notificationToast" class="toast border-0 shadow-lg toast-<?= $type ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" style="border-radius: 1rem; overflow: hidden; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div class="toast-body p-3 d-flex align-items-center">
            <!-- Icon with dynamic translucent circular background -->
            <div class="me-3 d-inline-flex align-items-center justify-content-center rounded-circle p-2" style="background: rgba(var(--toast-bg-rgb), 0.1); width: 44px; height: 44px; flex-shrink: 0;">
                <i class="bi <?= $icon ?> <?= $colorClass ?>" style="font-size: 1.5rem; line-height: 1;"></i>
            </div>
            
            <!-- Text Content -->
            <div class="flex-grow-1 me-2">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;"><?= $title ?></h6>
                <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem; font-weight: 450; line-height: 1.35; word-break: break-word;">
                    <?= $message ?>
                </p>
            </div>
            
            <!-- Close Button -->
            <button type="button" class="btn-close shadow-none ms-2" data-bs-dismiss="toast" aria-label="<?= lang('close', 'Close') ?>"></button>
        </div>
        
        <!-- Premium accent progress bar indicating timer before auto-hide (width animated dynamically) -->
        <div class="toast-progress-bar" style="height: 3px; background: rgba(var(--toast-bg-rgb), 1); width: 100%;"></div>
    </div>
</div>

<?php endif; ?>
