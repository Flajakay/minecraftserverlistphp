<?php
$subject = lang('activation_email_subject');
ob_start();
?>

<h2><?= lang('activation_email_title') ?></h2>

<p><?= lang('activation_email_greeting') ?> <strong><?= /** @noinspection PhpUndefinedVariableInspection */
        sanitize($name) ?></strong>,</p>

<p><?= lang('activation_email_message') ?></p>

<div style="text-align: center;">
    <a href="<?= /** @noinspection PhpUndefinedVariableInspection */
    $activationUrl ?>" class="button"><?= lang('activate_account') ?></a>
</div>

<p><?= lang('activation_email_alternative') ?></p>
<p><a href="<?= $activationUrl ?>"><?= $activationUrl ?></a></p>

<p><?= lang('activation_email_expires') ?></p>

<p><?= lang('activation_email_ignore') ?></p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
