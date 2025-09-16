<?php
$subject = lang('reset_password_email_subject');
ob_start();
?>

<h2><?= lang('reset_password_email_title') ?></h2>

<p><?= lang('reset_password_email_greeting') ?> <strong><?= htmlspecialchars($name) ?></strong>,</p>

<p><?= lang('reset_password_email_message') ?></p>

<div style="text-align: center;">
    <a href="<?= $resetUrl ?>" class="button"><?= lang('reset_password') ?></a>
</div>

<p><?= lang('reset_password_email_alternative') ?></p>
<p><a href="<?= $resetUrl ?>"><?= $resetUrl ?></a></p>

<p><?= lang('reset_password_email_expires') ?></p>

<p><?= lang('reset_password_email_ignore') ?></p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
