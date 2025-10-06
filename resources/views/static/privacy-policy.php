<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-shield-check text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('privacy-policy') ?></h2>
                <p class="text-muted"><?= lang('last_updated') ?> September 17, 2025</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="prose">
                        <h3 class="h5 fw-bold mb-3">Information We Collect</h3>
                        <p class="mb-4">
                            We collect information you provide directly to us, such as when you create an account, submit a server listing, post comments, or contact us. This may include your username, email address, and any content you submit.
                        </p>

                        <h3 class="h5 fw-bold mb-3">How We Use Your Information</h3>
                        <p class="mb-4">
                            We use the information we collect to provide, maintain, and improve our services, process transactions, send you technical notices and support messages, and communicate with you about our services.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Information Sharing</h3>
                        <p class="mb-4">
                            We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy. We may share information in response to legal requests or to protect our rights.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Data Security</h3>
                        <p class="mb-4">
                            We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Cookies and Tracking</h3>
                        <p class="mb-4">
                            We use cookies and similar tracking technologies to enhance your experience on our website. You can choose to disable cookies in your browser settings, but this may limit your ability to use certain features.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Third-Party Services</h3>
                        <p class="mb-4">
                            Our website may contain links to third-party websites or services. We are not responsible for the privacy practices of these third parties. We encourage you to review their privacy policies.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Data Retention</h3>
                        <p class="mb-4">
                            We retain your information for as long as necessary to provide our services and fulfill the purposes outlined in this policy, unless a longer retention period is required by law.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Your Rights</h3>
                        <p class="mb-4">
                            You have the right to access, update, or delete your personal information. You may also opt out of certain communications from us. Contact us to exercise these rights.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Children's Privacy</h3>
                        <p class="mb-4">
                            Our service is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Changes to This Policy</h3>
                        <p class="mb-4">
                            We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.
                        </p>

                        <h3 class="h5 fw-bold mb-3">Contact Us</h3>
                        <p class="mb-0">
                            If you have any questions about this Privacy Policy, please contact us through our <a href="<?= url('/contact') ?>" class="text-decoration-none">contact page</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('privacy-policy') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
