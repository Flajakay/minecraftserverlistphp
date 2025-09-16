/**
 * Jodit Editor Helper
 * Centralized jodit editor initialization and management
 */

class JoditHelper {
    constructor() {
        this.editors = new Map();
        this.isLoaded = false;
        this.language = 'en';
        this.loadingPromise = null;
    }

    /**
     * Set the language for all future editors
     */
    setLanguage(lang) {
        this.language = lang;
    }

    /**
     * Get current language
     */
    getLanguage() {
        return window.lang?.jodit_code || this.language || 'en';
    }

    /**
     * Load jodit assets if not already loaded
     */
    async loadAssets() {
        if (this.isLoaded) return Promise.resolve();
        if (this.loadingPromise) return this.loadingPromise;

        this.loadingPromise = new Promise((resolve, reject) => {
            // Check if already loaded
            if (window.Jodit) {
                this.isLoaded = true;
                resolve();
                return;
            }

            // Load CSS
            if (!document.querySelector('link[href*="jodit"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/jodit@3/build/jodit.min.css';
                document.head.appendChild(link);
            }

            // Load JS
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/jodit@3/build/jodit.min.js';
            script.onload = () => {
                this.isLoaded = true;
                resolve();
            };
            script.onerror = () => {
                reject(new Error('Failed to load Jodit'));
            };
            document.head.appendChild(script);
        });

        return this.loadingPromise;
    }

    /**
     * Get default jodit configuration
     */
    getDefaultConfig(overrides = {}) {
        return {
            height: 400,
            toolbar: true,
            spellcheck: true,
            language: this.getLanguage(),
            toolbarSticky: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            buttons: [
                'bold', 'italic', 'underline', '|',
                'ul', 'ol', '|',
                'font', 'fontsize', '|',
                'paragraph', '|',
                'image', 'link', '|',
                'align', '|',
                'undo', 'redo', '|',
                'hr', 'eraser', 'fullsize'
            ],
            removeButtons: ['about'],
            placeholder: 'Write your content here...',
            ...overrides
        };
    }

    /**
     * Initialize editor on a selector
     */
    async init(selector, config = {}) {
        await this.loadAssets();
        
        const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!element) {
            throw new Error(`Element not found: ${selector}`);
        }

        // Destroy existing editor if exists
        if (this.editors.has(selector)) {
            this.destroy(selector);
        }

        const finalConfig = this.getDefaultConfig(config);
        const editor = window.Jodit.make(element, finalConfig);
        
        this.editors.set(selector, editor);
        return editor;
    }

    /**
     * Initialize editor with specific preset configurations
     */
    async initBlogEditor(selector, placeholder = 'Write your blog post content here...') {
        return this.init(selector, {
            height: 300,
            placeholder: placeholder,
            buttons: [
                'bold', 'italic', 'underline', '|',
                'ul', 'ol', '|',
                'font', 'fontsize', '|',
                'paragraph', '|',
                'image', 'link', '|',
                'align', '|',
                'undo', 'redo', '|',
                'hr', 'eraser'
            ],
            removeButtons: ['about', 'fullsize']
        });
    }

    /**
     * Initialize editor for full page editing
     */
    async initPageEditor(selector, placeholder = 'Write your content here...') {
        return this.init(selector, {
            height: 400,
            placeholder: placeholder
        });
    }

    /**
     * Get editor instance
     */
    getEditor(selector) {
        return this.editors.get(selector);
    }

    /**
     * Destroy editor
     */
    destroy(selector) {
        const editor = this.editors.get(selector);
        if (editor) {
            editor.destruct();
            this.editors.delete(selector);
        }
    }

    /**
     * Destroy all editors
     */
    destroyAll() {
        this.editors.forEach((editor, selector) => {
            this.destroy(selector);
        });
    }

    /**
     * Validate editor content
     */
    validateContent(selector, minLength = 10) {
        const editor = this.getEditor(selector);
        if (!editor) return { valid: false, message: 'Editor not found' };

        const content = editor.value.trim();
        if (content.length < minLength) {
            return { 
                valid: false, 
                message: `Content must be at least ${minLength} characters long` 
            };
        }

        return { valid: true, content: content };
    }
}

// Create global instance
window.joditHelper = new JoditHelper();

// Export for use in modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = JoditHelper;
}
