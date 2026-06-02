/**
 * Sidebar Filters Partial JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    const filterForms = document.querySelectorAll('.sidebar-filter-form');
    
    filterForms.forEach(form => {
        const prefix = form.dataset.prefix || '';
        const checkboxes = form.querySelectorAll('.category-check');
        const hiddenInput = document.getElementById(prefix + 'categoriesInput');
        
        if (!hiddenInput) return;

        const updateCategories = () => {
            const selected = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            hiddenInput.value = selected.join(',');
        };

        checkboxes.forEach(cb => cb.addEventListener('change', updateCategories));
    });
});
