<?php ob_start(); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-file-text text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="h3 fw-bold text-dark"><?= lang('terms-of-service') ?></h2>
                <p class="text-muted"><?= sprintf(lang('last_updated'), 'September 17, 2025') ?></p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="prose">
                        <h3 class="h5 fw-bold mb-3">1. Acceptance of Terms</h3>
                        <p class="mb-4">
                            By accessing and using this Minecraft server list website, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                        </p>

                        <h3 class="h5 fw-bold mb-3">2. Use License</h3>
                        <p class="mb-4">
                            Permission is granted to temporarily download one copy of the materials on our website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not modify or copy the materials.
                        </p>

                        <h3 class="h5 fw-bold mb-3">3. Server Listings</h3>
                        <p class="mb-4">
                            Users may submit Minecraft servers for listing on our platform. We reserve the right to moderate, edit, or remove any server listings that violate our community guidelines or terms of service.
                        </p>

                        <h3 class="h5 fw-bold mb-3">4. User Content</h3>
                        <p class="mb-4">
                            Users are responsible for all content they post, including server descriptions, comments, and other materials. Content must not be illegal, harmful, threatening, abusive, or otherwise objectionable.
                        </p>

                        <h3 class="h5 fw-bold mb-3">5. Privacy</h3>
                        <p class="mb-4">
                            Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the website, to understand our practices.
                        </p>

                        <h3 class="h5 fw-bold mb-3">6. Disclaimer</h3>
                        <p class="mb-4">
                            The materials on our website are provided on an 'as is' basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
                        </p>

                        <h3 class="h5 fw-bold mb-3">7. Limitations</h3>
                        <p class="mb-4">
                            In no event shall our company or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on our website.
                        </p>

                        <h3 class="h5 fw-bold mb-3">8. Contact Information</h3>
                        <p class="mb-0">
                            If you have any questions about these Terms of Service, please contact us through our <a href="<?= url('/contact') ?>" class="text-decoration-none">contact page</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $title = lang('terms-of-service') . ' - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
