class CategoryCloud {
    constructor() {
        this.selectedCategories = new Set();
        this.expandedParents = new Set();
        this.categories = {};
        this.subcategories = {};
        this.init();
    }

    init() {
        this.loadCategoriesData();
        this.createCloud();
        this.bindEvents();
        this.loadInitialSelections();
    }

    loadCategoriesData() {
        const categoryData = window.categoryCloudData;
        if (!categoryData) return;

        categoryData.forEach(cat => {
            if (cat.parent_id == 0) {
                this.categories[cat.id] = cat;
            } else {
                if (!this.subcategories[cat.parent_id]) {
                    this.subcategories[cat.parent_id] = [];
                }
                this.subcategories[cat.parent_id].push(cat);
            }
        });
    }

    createCloud() {
        const container = document.getElementById('category-cloud');
        if (!container) return;

        container.innerHTML = '';

        Object.values(this.categories).forEach(category => {
            this.renderMainCategory(container, category);
            
            if (this.expandedParents.has(category.id)) {
                this.renderSubcategories(container, category.id);
            }
        });

        this.updateHiddenInputs();
    }

    renderMainCategory(container, category) {
        const tag = document.createElement('div');
        tag.className = `category-tag main-category ${this.selectedCategories.has(category.id) ? 'selected' : ''}`;
        tag.dataset.categoryId = category.id;
        tag.dataset.categoryType = 'main';
        
        tag.innerHTML = `
            ${category.name}
            <span class="checkmark">✓</span>
        `;

        container.appendChild(tag);
    }

    renderSubcategories(container, parentId) {
        const subcats = this.subcategories[parentId];
        if (!subcats) return;

        const parentTag = container.querySelector(`[data-category-id="${parentId}"]`);
        if (!parentTag) return;

        subcats.forEach(subcat => {
            const tag = document.createElement('div');
            tag.className = `category-tag sub-category ${this.selectedCategories.has(subcat.id) ? 'selected' : ''}`;
            tag.dataset.categoryId = subcat.id;
            tag.dataset.categoryType = 'sub';
            tag.dataset.parentId = parentId;
            
            tag.innerHTML = `
                ${subcat.name}
                <span class="checkmark">✓</span>
            `;

            parentTag.insertAdjacentElement('afterend', tag);
        });
    }

    bindEvents() {
        const container = document.getElementById('category-cloud');
        if (!container) return;

        container.addEventListener('click', (e) => {
            const tag = e.target.closest('.category-tag');
            if (!tag) return;

            const categoryId = parseInt(tag.dataset.categoryId);
            const categoryType = tag.dataset.categoryType;

            if (categoryType === 'main') {
                this.toggleMainCategory(categoryId);
            } else {
                this.toggleSubcategory(categoryId);
            }
        });
    }

    toggleMainCategory(categoryId) {
        const wasSelected = this.selectedCategories.has(categoryId);
        
        if (wasSelected) {
            this.selectedCategories.delete(categoryId);
            this.expandedParents.delete(categoryId);
            
            if (this.subcategories[categoryId]) {
                this.subcategories[categoryId].forEach(subcat => {
                    this.selectedCategories.delete(subcat.id);
                });
            }
        } else {
            this.selectedCategories.add(categoryId);
            if (this.subcategories[categoryId] && this.subcategories[categoryId].length > 0) {
                this.expandedParents.add(categoryId);
            }
        }

        this.createCloud();
    }

    toggleSubcategory(categoryId) {
        if (this.selectedCategories.has(categoryId)) {
            this.selectedCategories.delete(categoryId);
        } else {
            this.selectedCategories.add(categoryId);
        }

        this.createCloud();
    }

    loadInitialSelections() {
        const hiddenInputs = document.querySelectorAll('input[name="category_ids[]"]');
        
        hiddenInputs.forEach(input => {
            const categoryId = parseInt(input.value);
            if (input.checked) {
                this.selectedCategories.add(categoryId);
                
                const category = this.categories[categoryId];
                if (category) {
                    if (this.subcategories[categoryId] && this.subcategories[categoryId].length > 0) {
                        this.expandedParents.add(categoryId);
                    }
                } else {
                    Object.values(this.categories).forEach(parentCat => {
                        if (this.subcategories[parentCat.id]) {
                            const found = this.subcategories[parentCat.id].find(sub => sub.id === categoryId);
                            if (found && this.selectedCategories.has(parentCat.id)) {
                                this.expandedParents.add(parentCat.id);
                            }
                        }
                    });
                }
            }
        });

        this.createCloud();
    }

    updateHiddenInputs() {
        const container = document.getElementById('hidden-category-inputs');
        if (!container) return;

        container.innerHTML = '';

        this.selectedCategories.forEach(categoryId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_ids[]';
            input.value = categoryId;
            input.checked = true;
            container.appendChild(input);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('category-cloud')) {
        new CategoryCloud();
    }
});