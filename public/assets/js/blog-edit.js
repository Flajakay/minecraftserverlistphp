/**
 * Blog Edit Page JavaScript
 * Handles functionality for editing blog posts
 */

class BlogEdit {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
        this.editor = null;
        
        this.init();
    }

    async init() {
        await this.initializeEditor();
        this.initEventListeners();
    }

    async initializeEditor() {
        try {
            // Ensure Jodit assets are loaded
            if (window.joditHelper) {
                await window.joditHelper.loadAssets();
                this.editor = await window.joditHelper.initPageEditor('#content', window.lang?.content_placeholder || 'Write your content here...');
            } else {
                // Direct Jodit initialization if helper is not available
                if (window.Jodit) {
                    this.editor = window.Jodit.make('#content', {
                        height: 400,
                        toolbar: true,
                        spellcheck: true,
                        language: window.lang?.jodit_code || 'en',
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
                        placeholder: window.lang?.content_placeholder || 'Write your content here...'
                    });
                }
            }
        } catch (error) {
            console.error('Failed to initialize blog editor:', error);
            // Fallback to basic textarea
        }
    }

    initEventListeners() {
        const blogForm = document.getElementById('blogForm');
        if (blogForm) {
            blogForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    handleFormSubmit(e) {
        if (this.editor) {
            const content = this.editor.value.trim();
            if (content.length < 10) {
                e.preventDefault();
                alert(window.lang?.blog_content_required || 'Content must be at least 10 characters long');
                return false;
            }
        }
        
        // Form validation passed, allow normal submission
        return true;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.csrfToken) {
        window.blogEditInstance = new BlogEdit(window.csrfToken);
    }
});