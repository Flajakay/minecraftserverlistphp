class BannerGenerator {
    constructor() {
        this.frames = [];
        this.currentFrameIndex = 0;
        this.currentLayerIndex = 0;
        this.canvas = document.getElementById('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.gif = null;
        this.isGenerating = false;
        this.isPlaying = false;
        this.playInterval = null;
        this.currentGifBlob = null;
        
        this.init();
    }
    
    init() {
        this.initDefaultFrames();
        this.initEventListeners();
        this.updateUI();
        this.renderCurrentFrame();
    }
    
    initDefaultFrames() {
        this.frames = [
            {
                background: { type: 'template', value: 'default' },
                textLayers: [
                    {
                        text: 'Welcome!',
                        x: 234,
                        y: 35,
                        fontSize: 20,
                        color: '#ffffff',
                        align: 'center'
                    }
                ],
                duration: 1000
            },
            {
                background: { type: 'template', value: 'default' },
                textLayers: [
                    {
                        text: 'Vote Now!',
                        x: 234,
                        y: 35,
                        fontSize: 20,
                        color: '#ffffff',
                        align: 'center'
                    }
                ],
                duration: 1000
            }
        ];
    }
    
    initEventListeners() {
        document.getElementById('addFrameBtn').addEventListener('click', () => this.addFrame());
        document.getElementById('removeFrameBtn').addEventListener('click', () => this.removeFrame());
        document.getElementById('addTextBtn').addEventListener('click', () => this.addTextLayer());
        document.getElementById('playBtn').addEventListener('click', () => this.playPreview());
        document.getElementById('stopBtn').addEventListener('click', () => this.stopPreview());
        document.getElementById('generateBtn').addEventListener('click', () => this.generateGIF());
        document.getElementById('downloadBtn').addEventListener('click', () => this.downloadGIF());
        document.getElementById('copyHtmlBtn').addEventListener('click', () => this.copyHTML());
        document.getElementById('copyBbcodeBtn').addEventListener('click', () => this.copyBBCode());
        
        document.getElementById('frameDuration').addEventListener('input', (e) => {
            this.frames[this.currentFrameIndex].duration = parseInt(e.target.value);
        });
        
        document.getElementById('frameSlider').addEventListener('input', (e) => {
            this.selectFrame(parseInt(e.target.value));
        });
        
        document.querySelectorAll('input[name="bgType"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleBackgroundTypeChange(e.target.value));
        });
        
        document.getElementById('bgTemplateSelect').addEventListener('change', (e) => {
            this.frames[this.currentFrameIndex].background = { type: 'template', value: e.target.value };
            this.renderCurrentFrame();
        });
        
        document.getElementById('bgUrlInput').addEventListener('change', (e) => {
            this.frames[this.currentFrameIndex].background = { type: 'url', value: e.target.value };
            this.renderCurrentFrame();
        });
        
        document.getElementById('bgColorInput').addEventListener('change', (e) => {
            this.frames[this.currentFrameIndex].background = { type: 'color', value: e.target.value };
            this.renderCurrentFrame();
        });
    }
    
    addFrame() {
        if (this.frames.length >= 6) {
            alert('Maximum 6 frames allowed');
            return;
        }
        
        const newFrame = {
            background: { type: 'template', value: 'default' },
            textLayers: [],
            duration: 1000
        };
        
        this.frames.push(newFrame);
        this.selectFrame(this.frames.length - 1);
        this.updateUI();
    }
    
    removeFrame() {
        if (this.frames.length <= 2) {
            alert('Minimum 2 frames required');
            return;
        }
        
        if (!confirm('Remove this frame?')) {
            return;
        }
        
        this.frames.splice(this.currentFrameIndex, 1);
        if (this.currentFrameIndex >= this.frames.length) {
            this.currentFrameIndex = this.frames.length - 1;
        }
        this.updateUI();
        this.renderCurrentFrame();
    }
    
    selectFrame(index) {
        this.currentFrameIndex = index;
        this.currentLayerIndex = 0;
        this.updateUI();
        this.renderCurrentFrame();
    }
    
    addTextLayer() {
        const frame = this.frames[this.currentFrameIndex];
        if (frame.textLayers.length >= 5) {
            alert('Maximum 5 text layers per frame');
            return;
        }
        
        frame.textLayers.push({
            text: 'Sample Text',
            x: 234,
            y: 30,
            fontSize: 16,
            color: '#ffffff',
            align: 'center'
        });
        
        this.currentLayerIndex = frame.textLayers.length - 1;
        this.updateUI();
        this.renderCurrentFrame();
    }
    
    removeTextLayer(layerIndex) {
        this.frames[this.currentFrameIndex].textLayers.splice(layerIndex, 1);
        if (this.currentLayerIndex >= this.frames[this.currentFrameIndex].textLayers.length) {
            this.currentLayerIndex = Math.max(0, this.frames[this.currentFrameIndex].textLayers.length - 1);
        }
        this.updateUI();
        this.renderCurrentFrame();
    }
    
    selectLayer(index) {
        this.currentLayerIndex = index;
        this.updateUI();
    }
    
    updateTextLayer(layerIndex, property, value) {
        if (property === 'fontSize' || property === 'x' || property === 'y') {
            value = parseInt(value);
        }
        this.frames[this.currentFrameIndex].textLayers[layerIndex][property] = value;
        this.renderCurrentFrame();
    }
    
    handleBackgroundTypeChange(type) {
        const templateSelect = document.getElementById('bgTemplateSelect');
        const urlInput = document.getElementById('bgUrlInput');
        const colorInput = document.getElementById('bgColorInput');
        
        templateSelect.style.display = 'none';
        urlInput.style.display = 'none';
        colorInput.style.display = 'none';
        
        if (type === 'template') {
            templateSelect.style.display = 'block';
            this.frames[this.currentFrameIndex].background = { type: 'template', value: templateSelect.value };
        } else if (type === 'url') {
            urlInput.style.display = 'block';
            this.frames[this.currentFrameIndex].background = { type: 'url', value: urlInput.value };
        } else if (type === 'color') {
            colorInput.style.display = 'block';
            this.frames[this.currentFrameIndex].background = { type: 'color', value: colorInput.value };
        }
        
        this.renderCurrentFrame();
    }
    
    updateUI() {
        this.updateFrameTabs();
        this.updateFrameControls();
        this.updateTextLayersUI();
        
        document.getElementById('currentFrameNum').textContent = this.currentFrameIndex + 1;
        document.getElementById('totalFrames').textContent = this.frames.length;
        document.getElementById('frameSlider').max = this.frames.length - 1;
        document.getElementById('frameSlider').value = this.currentFrameIndex;
    }
    
    updateFrameTabs() {
        const container = document.getElementById('frameTabs');
        container.innerHTML = '';
        
        this.frames.forEach((frame, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm ${index === this.currentFrameIndex ? 'btn-primary' : 'btn-outline-primary'}`;
            btn.textContent = `Frame ${index + 1}`;
            btn.addEventListener('click', () => this.selectFrame(index));
            container.appendChild(btn);
        });
    }
    
    updateFrameControls() {
        const frame = this.frames[this.currentFrameIndex];
        document.getElementById('frameDuration').value = frame.duration;
        
        const bgType = frame.background.type;
        const radioBtn = document.querySelector(`input[name="bgType"][value="${bgType}"]`);
        if (radioBtn) {
            radioBtn.checked = true;
        }
        
        document.getElementById('bgTemplateSelect').style.display = bgType === 'template' ? 'block' : 'none';
        document.getElementById('bgUrlInput').style.display = bgType === 'url' ? 'block' : 'none';
        document.getElementById('bgColorInput').style.display = bgType === 'color' ? 'block' : 'none';
        
        if (bgType === 'template') {
            document.getElementById('bgTemplateSelect').value = frame.background.value;
        } else if (bgType === 'url') {
            document.getElementById('bgUrlInput').value = frame.background.value;
        } else if (bgType === 'color') {
            document.getElementById('bgColorInput').value = frame.background.value;
        }
    }
    
    updateTextLayersUI() {
        const container = document.getElementById('textLayersContainer');
        container.innerHTML = '';
        
        const frame = this.frames[this.currentFrameIndex];
        
        if (frame.textLayers.length === 0) {
            container.innerHTML = '<p class="text-muted small text-center">No text layers. Click "Add Text" to create one.</p>';
            return;
        }
        
        const layerTabs = document.createElement('div');
        layerTabs.className = 'btn-group w-100 mb-3';
        layerTabs.setAttribute('role', 'group');
        
        frame.textLayers.forEach((layer, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm ${index === this.currentLayerIndex ? 'btn-success' : 'btn-outline-success'}`;
            btn.textContent = `Layer ${index + 1}`;
            btn.addEventListener('click', () => this.selectLayer(index));
            layerTabs.appendChild(btn);
        });
        
        container.appendChild(layerTabs);
        
        const currentLayer = frame.textLayers[this.currentLayerIndex];
        
        const card = document.createElement('div');
        card.className = 'text-layer-card';
        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="small">Layer ${this.currentLayerIndex + 1} Settings</strong>
                <button class="btn btn-sm btn-outline-danger" onclick="bannerGenerator.removeTextLayer(${this.currentLayerIndex})">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Text</label>
                <input type="text" class="form-control form-control-sm" 
                       value="${currentLayer.text}" 
                       placeholder="Enter text"
                       maxlength="100"
                       onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'text', this.value)">
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Font Size</label>
                    <input type="number" class="form-control form-control-sm" 
                           value="${currentLayer.fontSize}" 
                           min="8" max="72"
                           onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'fontSize', this.value)">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Color</label>
                    <input type="color" class="form-control form-control-color form-control-sm w-100" 
                           value="${currentLayer.color}"
                           onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'color', this.value)">
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small mb-1">X Position</label>
                    <input type="number" class="form-control form-control-sm" 
                           value="${currentLayer.x}" 
                           min="0" max="468"
                           onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'x', this.value)">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Y Position</label>
                    <input type="number" class="form-control form-control-sm" 
                           value="${currentLayer.y}" 
                           min="0" max="60"
                           onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'y', this.value)">
                </div>
            </div>
            <div>
                <label class="form-label small mb-1">Text Align</label>
                <select class="form-select form-select-sm" 
                        onchange="bannerGenerator.updateTextLayer(${this.currentLayerIndex}, 'align', this.value)">
                    <option value="left" ${currentLayer.align === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${currentLayer.align === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${currentLayer.align === 'right' ? 'selected' : ''}>Right</option>
                </select>
            </div>
        `;
        container.appendChild(card);
    }
    
    async renderCurrentFrame() {
        const frame = this.frames[this.currentFrameIndex];
        
        this.ctx.clearRect(0, 0, 468, 60);
        
        try {
            if (frame.background.type === 'template') {
                const bgImage = await this.loadImage(`${window.bannerConfig.assetUrl}/public/assets/banners/${frame.background.value}.jpg`);
                this.ctx.drawImage(bgImage, 0, 0, 468, 60);
            } else if (frame.background.type === 'url' && frame.background.value) {
                const bgImage = await this.loadImage(frame.background.value);
                this.ctx.drawImage(bgImage, 0, 0, 468, 60);
            } else if (frame.background.type === 'color') {
                this.ctx.fillStyle = frame.background.value;
                this.ctx.fillRect(0, 0, 468, 60);
            }
        } catch (error) {
            console.error('Error loading background:', error);
            this.ctx.fillStyle = '#667eea';
            this.ctx.fillRect(0, 0, 468, 60);
        }
        
        frame.textLayers.forEach(layer => {
            this.ctx.font = `${layer.fontSize}px Minecraft, Arial, sans-serif`;
            this.ctx.fillStyle = layer.color;
            this.ctx.textAlign = layer.align;
            this.ctx.textBaseline = 'middle';
            this.ctx.fillText(layer.text, layer.x, layer.y);
        });
    }
    
    loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    }
    
    playPreview() {
        if (this.isPlaying) return;
        
        this.isPlaying = true;
        document.getElementById('playBtn').disabled = true;
        document.getElementById('stopBtn').disabled = false;
        
        let frameIndex = 0;
        const playNext = () => {
            if (!this.isPlaying) return;
            
            this.selectFrame(frameIndex);
            frameIndex = (frameIndex + 1) % this.frames.length;
            
            const delay = this.frames[this.currentFrameIndex].duration;
            this.playInterval = setTimeout(playNext, delay);
        };
        
        playNext();
    }
    
    stopPreview() {
        this.isPlaying = false;
        if (this.playInterval) {
            clearTimeout(this.playInterval);
            this.playInterval = null;
        }
        document.getElementById('playBtn').disabled = false;
        document.getElementById('stopBtn').disabled = true;
    }
    
    async generateGIF() {
        if (this.isGenerating) return;
        
        this.isGenerating = true;
        this.showLoading();
        
        try {
            const gif = new GIF({
                workers: 2,
                quality: 10,
                width: 468,
                height: 60,
                workerScript: `${window.bannerConfig.assetUrl}/assets/js/gif.worker.js`
            });
            
            for (let i = 0; i < this.frames.length; i++) {
                const originalIndex = this.currentFrameIndex;
                this.currentFrameIndex = i;
                await this.renderCurrentFrame();
                
                gif.addFrame(this.canvas, {
                    delay: this.frames[i].duration,
                    copy: true
                });
            }
            
            gif.on('finished', (blob) => {
                this.hideLoading();
                this.isGenerating = false;
                this.currentGifBlob = blob;
                this.showPreview(blob);
                
                document.getElementById('downloadBtn').disabled = false;
                document.getElementById('copyHtmlBtn').disabled = false;
                document.getElementById('copyBbcodeBtn').disabled = false;
            });
            
            gif.render();
        } catch (error) {
            console.error('Error generating GIF:', error);
            alert('Error generating GIF. Please try again.');
            this.hideLoading();
            this.isGenerating = false;
        }
    }
    
    showPreview(blob) {
        const url = URL.createObjectURL(blob);
        document.getElementById('gifPreview').src = url;
        document.getElementById('previewContainer').style.display = 'block';
    }
    
    downloadGIF() {
        if (!this.currentGifBlob) return;
        
        const url = URL.createObjectURL(this.currentGifBlob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `banner_${Date.now()}.gif`;
        a.click();
        URL.revokeObjectURL(url);
    }
    
    async copyHTML() {
        if (!this.currentGifBlob) return;
        
        const base64 = await this.blobToBase64(this.currentGifBlob);
        let code;
        
        if (window.bannerConfig.serverId) {
            const serverUrl = `${window.bannerConfig.assetUrl}/server/${window.bannerConfig.serverAddress}:${window.bannerConfig.serverPort}`;
            code = `<a href="${serverUrl}" target="_blank"><img src="${base64}" alt="${window.bannerConfig.serverName} Banner"></a>`;
        } else {
            code = `<img src="${base64}" alt="Banner">`;
        }
        
        document.getElementById('generatedCode').value = code;
        document.getElementById('codeContainer').style.display = 'block';
        
        try {
            await navigator.clipboard.writeText(code);
            this.showToast('HTML code copied to clipboard!', 'success');
        } catch (error) {
            alert('Please manually copy the code from the text area below.');
        }
    }
    
    async copyBBCode() {
        if (!this.currentGifBlob) return;
        
        const base64 = await this.blobToBase64(this.currentGifBlob);
        let code;
        
        if (window.bannerConfig.serverId) {
            const serverUrl = `${window.bannerConfig.assetUrl}/server/${window.bannerConfig.serverAddress}:${window.bannerConfig.serverPort}`;
            code = `[url=${serverUrl}][img]${base64}[/img][/url]`;
        } else {
            code = `[img]${base64}[/img]`;
        }
        
        document.getElementById('generatedCode').value = code;
        document.getElementById('codeContainer').style.display = 'block';
        
        try {
            await navigator.clipboard.writeText(code);
            this.showToast('BBCode copied to clipboard!', 'success');
        } catch (error) {
            alert('Please manually copy the code from the text area below.');
        }
    }
    
    blobToBase64(blob) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.readAsDataURL(blob);
        });
    }
    
    showLoading() {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="spinner-container">
                <div class="spinner-border text-light" role="status"></div>
                <p class="mt-3">Generating GIF...</p>
                <small>This may take a few moments</small>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    
    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `position-fixed top-0 start-50 translate-middle-x alert alert-${type} alert-dismissible fade show mt-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
}
