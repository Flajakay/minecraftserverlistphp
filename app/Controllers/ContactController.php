<?php

namespace App\Controllers;

use App\Core\System\Mail;
use Exception;

/**
 * Contact form controller.
 *
 * Renders the contact page and sends a templated email to the configured contact address.
 */
class ContactController
{
    /**
     * Render the contact form page.
     */
    public function show(): void
    {
        view('static.contact');
    }

    /**
     * Validate and send a contact form submission.
     *
     * Uses PRG (flash + redirect) to avoid duplicate submissions on refresh.
     */
    public function send(): void
    {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        $errors = [];

        if (strlen($name) < 3) {
            $errors[] = lang('name_length');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = lang('invalid_email');
        }

        if (strlen($subject) < 3) {
            $errors[] = lang('subject_required', 'Subject is required');
        }

        if (strlen($message) < 10) {
            $errors[] = lang('message_too_short');
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('/contact');
        }

        try {
            Mail::create()
                ->to(setting('contact_email'))
                ->subject(lang('contact_form_subject', 'Contact Form: ') . $subject)
                ->template('contact', [
                    'name' => $name,
                    'email' => $email,
                    'messageSubject' => $subject,
                    'message' => $message
                ])
                ->send();

            flash('success', lang('contact_success', 'Your message has been sent successfully!'));
        } catch (Exception $e) {
            flash('error', lang('email_send_failed', 'Failed to send email. Please try again later.'));
        }

        redirect('/contact');
    }
}
