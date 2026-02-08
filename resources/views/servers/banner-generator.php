<?php ob_start(); ?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="fw-bold mb-0">
                            <i class="bi bi-images text-primary me-2"></i>Banner Generator
                        </h4>
                        <?php /** @noinspection PhpUndefinedVariableInspection */
                        if ($server): ?>
                            <a href="<?= url('/server/' . $server->address . ':' . $server->port) ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Back to Server
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if ($server): ?>
                        <p class="text-muted mb-0 mt-2">Creating banner for: <strong><?= htmlspecialchars($server->name) ?></strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <canvas id="canvas" width="468" height="60" class="border rounded" style="max-width: 100%; height: auto;"></canvas>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button id="playBtn" class="btn btn-success btn-sm">
                            <i class="bi bi-play-fill"></i> Play
                        </button>
                        <button id="stopBtn" class="btn btn-danger btn-sm" disabled>
                            <i class="bi bi-stop-fill"></i> Stop
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Frame Timeline</label>
                        <input type="range" class="form-range" id="frameSlider" min="0" max="1" value="0">
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Frame <span id="currentFrameNum">1</span></span>
                            <span>Total: <span id="totalFrames">2</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-download text-primary me-2"></i>Export Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 mb-3">
                        <button id="generateBtn" class="btn btn-primary">
                            <i class="bi bi-magic me-1"></i>Generate Preview
                        </button>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button id="downloadBtn" class="btn btn-success w-100" disabled>
                                    <i class="bi bi-download me-1"></i>Download GIF
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button id="copyHtmlBtn" class="btn btn-info w-100" disabled>
                                    <i class="bi bi-code-slash me-1"></i>Copy HTML
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button id="copyBbcodeBtn" class="btn btn-warning w-100" disabled>
                                    <i class="bi bi-chat-square-quote me-1"></i>Copy BBCode
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="previewContainer" style="display: none;">
                        <label class="form-label fw-semibold">Animated Preview</label>
                        <div class="text-center bg-light rounded p-3 mb-3">
                            <img id="gifPreview" class="border rounded" style="max-width: 100%;">
                        </div>
                    </div>
                    <div id="codeContainer" style="display: none;">
                        <label class="form-label fw-semibold">Generated Code</label>
                        <textarea id="generatedCode" class="form-control small" rows="4" readonly onclick="this.select()"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-sliders text-primary me-2"></i>Frame Controls
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex gap-2 mb-3">
                            <button id="addFrameBtn" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="bi bi-plus-lg"></i> Add Frame
                            </button>
                            <button id="removeFrameBtn" class="btn btn-sm btn-outline-danger flex-fill">
                                <i class="bi bi-dash-lg"></i> Remove Frame
                            </button>
                        </div>
                        <div id="frameTabs" class="btn-group w-100 mb-3" role="group"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Frame Duration (ms)</label>
                        <input type="number" id="frameDuration" class="form-control" value="1000" min="100" max="5000" step="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Background</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bgType" id="bgTypeTemplate" value="template" checked>
                                <label class="form-check-label" for="bgTypeTemplate">Template</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bgType" id="bgTypeUrl" value="url">
                                <label class="form-check-label" for="bgTypeUrl">Custom URL</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bgType" id="bgTypeColor" value="color">
                                <label class="form-check-label" for="bgTypeColor">Solid Color</label>
                            </div>
                        </div>
                        <select id="bgTemplateSelect" class="form-select mb-2">
                            <?php /** @noinspection PhpUndefinedVariableInspection */
                            foreach ($backgrounds as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="url" id="bgUrlInput" class="form-control mb-2" placeholder="https://example.com/image.jpg" style="display: none;">
                        <input type="color" id="bgColorInput" class="form-control form-control-color w-100" value="#667eea" style="display: none;">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">Text Layers</label>
                            <button id="addTextBtn" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-plus-lg"></i> Add Text
                            </button>
                        </div>
                        <div id="textLayersContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@font-face {
    font-family: 'Minecraft';
    src: url('<?= asset('fonts/minecraft.ttf') ?>');
}

#canvas {
    background: #f8f9fa;
    image-rendering: pixelated;
    image-rendering: -moz-crisp-edges;
    image-rendering: crisp-edges;
}

.text-layer-card {
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border: 1px solid #dee2e6;
}

#loadingOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner-container {
    text-align: center;
    color: white;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>

<script src="<?= asset('js/gif.min.js') ?>"></script>
<script src="<?= asset('js/banner-generator.js') ?>"></script>
<script>
window.bannerConfig = {
    serverId: <?= $server ? $server->id : 'null' ?>,
    serverAddress: <?= $server ? "'" . $server->address . "'" : 'null' ?>,
    serverPort: <?= $server ? $server->port : 'null' ?>,
    serverName: <?= $server ? "'" . htmlspecialchars($server->name, ENT_QUOTES) . "'" : 'null' ?>,
    backgrounds: <?= json_encode($backgrounds) ?>,
    assetUrl: '<?= url('/') ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    window.bannerGenerator = new BannerGenerator();
});
</script>

<?php $content = ob_get_clean(); ?>
<?php $title = 'Banner Generator - ' . setting('title'); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
