<?php
$subject = lang('contact_form_subject');
ob_start();
?>

<h2><?= lang('contact_form_title') ?></h2>

<p><?= lang('contact_form_received') ?></p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <p><strong><?= lang('name') ?>:</strong> <?= /** @noinspection PhpUndefinedVariableInspection */
        sanitize($name) ?></p>
    <p><strong><?= lang('email') ?>:</strong> <?= /** @noinspection PhpUndefinedVariableInspection */
        sanitize($email) ?></p>
    <p><strong><?= lang('subject') ?>:</strong> <?= /** @noinspection PhpUndefinedVariableInspection */
        sanitize($messageSubject) ?></p>
    <p><strong><?= lang('message') ?>:</strong></p>
    <p><?= /** @noinspection PhpUndefinedVariableInspection */
        nl2br(sanitize($message)) ?></p>
</div>

<p><?= lang('contact_form_reply') ?></p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
