/**
 * Admin Blog Posts Page JavaScript
 */
(function() {
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    function deleteBlogPost(id) {
        const configEl = document.getElementById('admin-config');
        if (!configEl) {
            console.error('Configuration element #admin-config not found.');
            return;
        }

        let config;
        try {
            config = JSON.parse(configEl.textContent);
        } catch (e) {
            console.error('Failed to parse admin configuration:', e);
            return;
        }

        const confirmMsg = config.lang?.confirmDelete || 'Are you sure you want to delete this blog post?';
        const deleteUrl = config.urls?.deleteBlogPost;
        const csrfToken = config.csrfToken;
        const errorMsg = config.lang?.errorOccurred || 'An error occurred';

        if (!confirm(confirmMsg)) return;
        
        fetch(`${deleteUrl}${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                ajax: '1'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`tr[data-blog-id="${id}"]`);
                if (row) row.remove();
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message || errorMsg);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', errorMsg);
        });
    }

    // Expose deleteBlogPost to the global window object for onclick handlers
    window.deleteBlogPost = deleteBlogPost;
})();
