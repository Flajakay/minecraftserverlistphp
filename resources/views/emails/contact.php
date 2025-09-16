<?php
$subject = lang('contact_form_subject');
ob_start();
?>

<h2><?= lang('contact_form_title') ?></h2>

<p><?= lang('contact_form_received') ?></p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <p><strong><?= lang('name') ?>:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong><?= lang('email') ?>:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong><?= lang('subject') ?>:</strong> <?= htmlspecialchars($messageSubject) ?></p>
    <p><strong><?= lang('message') ?>:</strong></p>
    <p><?= nl2br(htmlspecialchars($message)) ?></p>
</div>

<p><?= lang('contact_form_reply') ?></p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
