/**
 * Server Edit Form Partials JavaScript
 * Handles image upload previewing and clearing
 */
(function() {
    function previewImage(input, previewId, previewBoxId) {
        const file = input.files[0];
        const previewImg = document.getElementById(previewId);
        const previewBox = document.getElementById(previewBoxId);
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewBox.classList.remove('d-none');
            };
            
            reader.readAsDataURL(file);
        }
    }

    function clearImagePreview(inputId, previewId, previewBoxId) {
        const input = document.getElementById(inputId);
        const previewBox = document.getElementById(previewBoxId);
        
        // Clear the file input
        input.value = '';
        
        // Hide the preview box with animation
        previewBox.style.opacity = '0';
        previewBox.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            previewBox.classList.add('d-none');
            previewBox.style.opacity = '';
            previewBox.style.transform = '';
        }, 300);
    }

    // Expose functions globally for inline HTML event handlers (onchange, onclick)
    window.previewImage = previewImage;
    window.clearImagePreview = clearImagePreview;
})();
