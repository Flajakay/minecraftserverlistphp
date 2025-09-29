/**
 * Server Show Page JavaScript
 * Handles all functionality for the server detail page
 */

class ServerShow {
    constructor(serverId, serverAddress, serverPort, csrfToken) {
        this.serverId = serverId;
        this.serverAddress = serverAddress;
        this.serverPort = serverPort;
        this.csrfToken = csrfToken;
        this.statisticsChart = null;
        this.blogEditor = null;
        this.blogEditorInitialized = false;
        
        this.init();
    }

    init() {
        this.initEventListeners();
        this.initBannerCode();
        this.handleLocationHash();
    }

    initEventListeners() {
        // Statistics chart initialization
        const statisticsTab = document.getElementById('statistics-tab');
        if (statisticsTab) {
            statisticsTab.addEventListener('shown.bs.tab', () => {
                setTimeout(() => {
                    if (!this.statisticsChart) {
                        this.initializeStatisticsChart();
                    }
                }, 100);
            });
        }

        // Blog modal events
        const blogModal = document.getElementById('blogModal');
        if (blogModal) {
            blogModal.addEventListener('shown.bs.modal', () => {
                this.initializeBlogEditor();
            });
            
            blogModal.addEventListener('hidden.bs.modal', () => {
                this.resetBlogForm();
            });
        }

        // Form submissions
        this.initFormHandlers();
        
        // Load more buttons
        this.initLoadMoreButtons();
    }

    initFormHandlers() {
        // Blog form
        const blogForm = document.getElementById('blogForm');
        if (blogForm) {
            blogForm.addEventListener('submit', (e) => this.handleBlogSubmit(e));
        }

        // Comment form
        const commentForm = document.getElementById('commentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => this.handleCommentSubmit(e));
        }

        // Report form
        const reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', (e) => this.handleReportSubmit(e));
        }
    }

    initLoadMoreButtons() {
        // Load more blog posts
        const loadMoreBlogPosts = document.getElementById('loadMoreBlogPosts');
        if (loadMoreBlogPosts) {
            loadMoreBlogPosts.addEventListener('click', (e) => this.loadMoreBlogPosts(e));
        }

        // Load more comments
        const loadMoreComments = document.getElementById('loadMoreComments');
        if (loadMoreComments) {
            loadMoreComments.addEventListener('click', (e) => this.loadMoreComments(e));
        }
    }

    async initializeBlogEditor() {
        if (this.blogEditorInitialized || !document.getElementById('blogContent')) {
            return;
        }

        try {
            // Ensure Jodit assets are loaded
            if (window.joditHelper) {
                await window.joditHelper.loadAssets();
                this.blogEditor = await window.joditHelper.initBlogEditor('#blogContent', window.lang?.content_placeholder || 'Write your blog post content here...');
            } else {
                // Direct Jodit initialization if helper is not available
                if (window.Jodit) {
                    this.blogEditor = window.Jodit.make('#blogContent', {
                        height: 300,
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
                            'hr', 'eraser'
                        ],
                        removeButtons: ['about', 'fullsize'],
                        placeholder: window.lang?.content_placeholder || 'Write your blog post content here...'
                    });
                }
            }
            this.blogEditorInitialized = true;
        } catch (error) {
            console.error('Failed to initialize blog editor:', error);
            // Fallback to basic textarea
        }
    }

    resetBlogForm() {
        const blogForm = document.getElementById('blogForm');
        if (blogForm) {
            blogForm.reset();
        }
        
        if (this.blogEditor) {
            this.blogEditor.value = '';
        }
    }

    initializeStatisticsChart() {
        const ctx = document.getElementById('statisticsChart');
        if (!ctx || !window.Chart) {
            console.warn('Chart.js not loaded or canvas not found');
            return;
        }

        const chartData = window.serverStatistics || [];
        
        this.statisticsChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Views',
                    data: chartData.map(d => d.hits),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Votes',
                    data: chartData.map(d => d.votes),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                }
            }
        });
    }

    async handleBlogSubmit(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        if (this.blogEditor) {
            const content = this.blogEditor.value.trim();
            if (content.length < 10) {
                alert(window.lang?.blog_content_required || 'Content must be at least 10 characters long');
                return;
            }
        }
        
        this.setButtonLoading(submitBtn, 'Creating...');
        
        try {
            const formData = new FormData();
            formData.append('server_id', this.serverId);
            formData.append('title', document.getElementById('blogTitle').value);
            formData.append('content', this.blogEditor ? this.blogEditor.value : document.getElementById('blogContent').value);
            formData.append('ajax', '1');
            formData.append('csrf_token', this.csrfToken);
            
            const response = await this.makeRequest('/blog', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('blogModal'));
                modal.hide();
                await this.refreshBlogPosts();
                this.showToast('Blog post created successfully!', 'success');
            } else {
                alert(data.message || 'Error occurred while creating blog post');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while creating blog post');
        } finally {
            this.resetButtonLoading(submitBtn, originalText);
        }
    }

    async handleCommentSubmit(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        this.setButtonLoading(submitBtn, 'Posting...');
        
        try {
            const formData = new FormData();
            formData.append('server_id', this.serverId);
            formData.append('comment', document.getElementById('comment').value);
            formData.append('ajax', '1');
            formData.append('csrf_token', this.csrfToken);
            
            const response = await this.makeRequest('/comment', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('comment').value = '';
                await this.refreshComments();
                this.showToast('Comment posted successfully!', 'success');
            } else {
                alert(data.message || 'Error occurred while posting comment');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while posting comment');
        } finally {
            this.resetButtonLoading(submitBtn, originalText);
        }
    }

    async handleReportSubmit(e) {
        e.preventDefault();
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        this.setButtonLoading(submitBtn, 'Submitting...');
        
        try {
            const formData = new FormData();
            formData.append('type', 2);
            formData.append('reported_id', this.serverId);
            formData.append('message', document.getElementById('reportReason').value);
            formData.append('csrf_token', this.csrfToken);
            
            const response = await this.makeRequest('/report', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Report submitted successfully. Our moderators will review it shortly.');
                const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                modal.hide();
                document.getElementById('reportReason').value = '';
            } else {
                alert(data.message || 'Error occurred while submitting report');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while submitting report');
        } finally {
            this.resetButtonLoading(submitBtn, originalText);
        }
    }

    async loadMoreBlogPosts(e) {
        const button = e.target;
        const offset = parseInt(button.dataset.offset);
        const originalText = button.innerHTML;
        
        this.setButtonLoading(button, 'Loading...');
        
        try {
            const response = await this.makeRequest(`/blog/load-more?server_id=${this.serverId}&offset=${offset}`);
            const data = await response.json();
            
            if (data.success) {
                const blogContainer = document.getElementById('blogPostsList');
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                
                Array.from(tempDiv.children).forEach(post => {
                    const cardWrapper = document.createElement('div');
                    cardWrapper.className = 'card border-0 shadow-sm mb-3';
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body';
                    cardBody.appendChild(post);
                    cardWrapper.appendChild(cardBody);
                    blogContainer.appendChild(cardWrapper);
                });
                
                if (data.has_more) {
                    button.dataset.offset = data.next_offset;
                    this.resetButtonLoading(button, originalText);
                } else {
                    button.style.display = 'none';
                }
            } else {
                alert('Error loading more blog posts');
                this.resetButtonLoading(button, originalText);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading more blog posts');
            this.resetButtonLoading(button, originalText);
        }
    }

    async loadMoreComments(e) {
        const button = e.target;
        const offset = parseInt(button.dataset.offset);
        const originalText = button.innerHTML;
        
        this.setButtonLoading(button, 'Loading...');
        
        try {
            const response = await this.makeRequest(`/comment/load-more?server_id=${this.serverId}&offset=${offset}`);
            const data = await response.json();
            
            if (data.success) {
                const commentsContainer = document.getElementById('commentsList');
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                
                Array.from(tempDiv.children).forEach(comment => {
                    const cardWrapper = document.createElement('div');
                    cardWrapper.className = 'card border-0 shadow-sm mb-3';
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body';
                    cardBody.appendChild(comment);
                    cardWrapper.appendChild(cardBody);
                    commentsContainer.appendChild(cardWrapper);
                });
                
                if (data.has_more) {
                    button.dataset.offset = data.next_offset;
                    this.resetButtonLoading(button, originalText);
                } else {
                    button.style.display = 'none';
                }
            } else {
                alert('Error loading more comments');
                this.resetButtonLoading(button, originalText);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading more comments');
            this.resetButtonLoading(button, originalText);
        }
    }

    // Action methods
    async voteForServer() {
        const username = prompt('Enter your Minecraft username (optional):');
        
        try {
            const response = await this.makeRequest('/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    server_id: this.serverId,
                    username: username || '',
                    csrf_token: this.csrfToken
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Vote recorded successfully! Thank you for supporting this server.');
                location.reload();
            } else {
                alert(data.message || 'Error occurred while voting');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while voting');
        }
    }

    async toggleFavorite() {
        try {
            const response = await this.makeRequest('/favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    server_id: this.serverId,
                    csrf_token: this.csrfToken
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error occurred');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }

    copyServerAddress() {
        const address = `${this.serverAddress}${this.serverPort !== 25565 ? ':' + this.serverPort : ''}`;
        
        navigator.clipboard.writeText(address).then(() => {
            this.showToast('Server address copied to clipboard!', 'success');
        }).catch(err => {
            alert('Failed to copy address. Please copy manually: ' + address);
        });
    }

    reportServer() {
        const modal = new bootstrap.Modal(document.getElementById('reportModal'));
        modal.show();
    }

    async deleteBlogPost(id) {
        if (!confirm(window.lang?.confirm_delete || 'Are you sure you want to delete this blog post?')) return;
        
        try {
            const response = await this.makeRequest('/blog/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    blog_post_id: id,
                    csrf_token: this.csrfToken
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.refreshBlogPosts();
                this.showToast('Blog post deleted successfully!', 'success');
            } else {
                alert(data.message || 'Error occurred while deleting blog post');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting blog post');
        }
    }

    async refreshComments() {
        try {
            const response = await this.makeRequest(`/comment/load-more?server_id=${this.serverId}&offset=0&limit=10`);
            const data = await response.json();
            
            if (data.success) {
                const commentsContainer = document.getElementById('commentsList');
                commentsContainer.innerHTML = '';
                
                if (data.html.trim()) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    Array.from(tempDiv.children).forEach(comment => {
                        const cardWrapper = document.createElement('div');
                        cardWrapper.className = 'card border-0 shadow-sm mb-3';
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body';
                        cardBody.appendChild(comment);
                        cardWrapper.appendChild(cardBody);
                        commentsContainer.appendChild(cardWrapper);
                    });
                } else {
                    commentsContainer.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No comments yet</p>
                        </div>
                    `;
                }
                
                const loadMoreBtn = document.getElementById('loadMoreComments');
                if (loadMoreBtn) {
                    if (data.has_more) {
                        loadMoreBtn.style.display = 'block';
                        loadMoreBtn.dataset.offset = data.next_offset;
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }
                }
                
                this.updateCommentCount();
            }
        } catch (error) {
            console.error('Error refreshing comments:', error);
        }
    }

    async refreshBlogPosts() {
        try {
            const response = await this.makeRequest(`/blog/load-more?server_id=${this.serverId}&offset=0&limit=5`);
            const data = await response.json();
            
            if (data.success) {
                const blogContainer = document.getElementById('blogPostsList');
                blogContainer.innerHTML = '';
                
                if (data.html.trim()) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    Array.from(tempDiv.children).forEach(post => {
                        const cardWrapper = document.createElement('div');
                        cardWrapper.className = 'card border-0 shadow-sm mb-3';
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body';
                        cardBody.appendChild(post);
                        cardWrapper.appendChild(cardBody);
                        blogContainer.appendChild(cardWrapper);
                    });
                } else {
                    blogContainer.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No blog posts</p>
                        </div>
                    `;
                }
                
                const loadMoreBtn = document.getElementById('loadMoreBlogPosts');
                if (loadMoreBtn) {
                    if (data.has_more) {
                        loadMoreBtn.style.display = 'block';
                        loadMoreBtn.dataset.offset = data.next_offset;
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }
                }
                
                this.updateBlogPostCount();
            }
        } catch (error) {
            console.error('Error refreshing blog posts:', error);
        }
    }

    updateCommentCount() {
        try {
            const commentsList = document.getElementById('commentsList');
            const commentCards = commentsList.querySelectorAll('.card');
            const count = commentCards.length;
            
            const commentTab = document.querySelector('#comments-tab');
            if (commentTab) {
                const iconAndText = commentTab.innerHTML.split('(')[0];
                commentTab.innerHTML = `${iconAndText}(${count})`;
            }
        } catch (error) {
            console.error('Error updating comment count:', error);
        }
    }

    updateBlogPostCount() {
        try {
            const blogList = document.getElementById('blogPostsList');
            const blogCards = blogList.querySelectorAll('.card');
            const count = blogCards.length;
            
            const blogTab = document.querySelector('#blog-tab');
            if (blogTab) {
                const iconAndText = blogTab.innerHTML.split('(')[0];
                blogTab.innerHTML = `${iconAndText}(${count})`;
            }
        } catch (error) {
            console.error('Error updating blog post count:', error);
        }
    }

    generateBanner() {
        const form = document.getElementById('bannerForm');
        const formData = new FormData(form);
        
        const params = new URLSearchParams({
            server_id: this.serverId,
            background: formData.get('background'),
            text_color: formData.get('text_color').replace('#', ''),
            border_color: formData.get('border_color').replace('#', '')
        });
        
        const bannerUrl = `/banner?${params.toString()}`;
        document.getElementById('bannerPreview').src = bannerUrl;
        
        const serverUrl = `/server/${this.serverAddress}:${this.serverPort}`;
        const htmlCode = `<a href="${serverUrl}" target="_blank"><img src="${bannerUrl}" alt="${this.serverAddress} Banner"></a>`;
        document.getElementById('bannerCode').value = htmlCode;
    }

    // Utility methods
    initBannerCode() {
        setTimeout(() => {
            const serverUrl = `/server/${this.serverAddress}:${this.serverPort}`;
            const bannerUrl = `/banner?server_id=${this.serverId}`;
            const htmlCode = `<a href="${serverUrl}" target="_blank"><img src="${bannerUrl}" alt="${this.serverAddress} Banner"></a>`;
            const bannerCodeEl = document.getElementById('bannerCode');
            if (bannerCodeEl) {
                bannerCodeEl.value = htmlCode;
            }
        }, 100);
    }

    handleLocationHash() {
        if (window.location.hash) {
            const element = document.querySelector(window.location.hash);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }

    setButtonLoading(button, text) {
        button.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>${text}`;
        button.disabled = true;
    }

    resetButtonLoading(button, originalText) {
        button.innerHTML = originalText;
        button.disabled = false;
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

    async makeRequest(url, options = {}) {
        const baseUrl = window.location.origin;
        const fullUrl = url.startsWith('http') ? url : baseUrl + url;
        
        return fetch(fullUrl, {
            ...options,
            credentials: 'same-origin'
        });
    }
}

// Global functions that need to be accessible from HTML onclick handlers
window.voteForServer = function(serverId) {
    if (window.serverShowInstance) {
        window.serverShowInstance.voteForServer();
    }
};

window.toggleFavorite = function(serverId) {
    if (window.serverShowInstance) {
        window.serverShowInstance.toggleFavorite();
    }
};

window.copyServerAddress = function() {
    if (window.serverShowInstance) {
        window.serverShowInstance.copyServerAddress();
    }
};

window.reportServer = function(serverId) {
    if (window.serverShowInstance) {
        window.serverShowInstance.reportServer();
    }
};

window.deleteBlogPost = function(id) {
    if (window.serverShowInstance) {
        window.serverShowInstance.deleteBlogPost(id);
    }
};

window.editServer = function(serverId) {
    window.location.href = `${window.location.origin}/edit-server/${serverId}`;
};

window.generateBanner = function() {
    if (window.serverShowInstance) {
        window.serverShowInstance.generateBanner();
    }
};