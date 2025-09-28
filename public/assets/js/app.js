function updateFilter(key, value) {
    const url = new URL(window.location);
    if (value) {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }
    window.location.href = url.toString();
}

function removeFilter(key) {
    const url = new URL(window.location);
    url.searchParams.delete(key);
    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location);
    url.search = '';
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    
    document.querySelectorAll('select[onchange]').forEach(select => {
        const param = select.getAttribute('onchange').match(/'([^']+)'/)[1];
        if (urlParams.has(param)) {
            select.value = urlParams.get(param);
        }
    });
    
    document.querySelectorAll('input[type="checkbox"][onchange]').forEach(checkbox => {
        const param = checkbox.getAttribute('onchange').match(/'([^']+)'/)[1];
        if (urlParams.has(param)) {
            checkbox.checked = true;
        }
    });
});

function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

function formatRelativeTime(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diff = now - time;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
    if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    return 'Just now';
}

function toggleFavorite(serverId) {
    if (window.serverShowInstance) {
        return window.serverShowInstance.toggleFavorite();
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        return;
    }
    
    const button = document.getElementById(`favorite-btn-${serverId}`);
    if (button && button.disabled) {
        return;
    }
    
    if (button) {
        button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';
        button.disabled = true;
    }
    
    fetch('/favorite', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            server_id: serverId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (button) {
                if (data.action === 'added') {
                    button.className = 'btn btn-danger btn-sm';
                    button.innerHTML = '<i class="bi bi-heart-fill me-1"></i>Remove from Favorites';
                } else {
                    button.className = 'btn btn-outline-danger btn-sm';
                    button.innerHTML = '<i class="bi bi-heart me-1"></i>Add to Favorites';
                }
                button.disabled = false;
            }

            const countElement = document.getElementById(`favorite-count-${serverId}`);
            if (countElement) {
                countElement.textContent = data.new_count;
            }
        } else {
            alert(data.message || 'Error occurred');
            if (button) {
                button.disabled = false;
                button.innerHTML = button.innerHTML.includes('heart-fill') ? 
                    '<i class="bi bi-heart-fill me-1"></i>Remove from Favorites' : 
                    '<i class="bi bi-heart me-1"></i>Add to Favorites';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating favorites');
        if (button) {
            button.disabled = false;
            button.innerHTML = button.innerHTML.includes('heart-fill') ? 
                '<i class="bi bi-heart-fill me-1"></i>Remove from Favorites' : 
                '<i class="bi bi-heart me-1"></i>Add to Favorites';
        }
    });
}


