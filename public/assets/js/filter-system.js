class FilterSystem {
    constructor() {
        this.state = {
            categories: [],
            includeSubcategories: false,
            orderBy: '',
            status: '',
            country: '',
            highlight: false
        };
        
        this.initializeFromURL();
        this.bindEvents();
    }

    initializeFromURL() {
        const params = new URLSearchParams(window.location.search);
        
        if (params.has('categories')) {
            this.state.categories = params.get('categories').split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
        }
        
        this.state.includeSubcategories = params.get('include_subcategories') === '1';
        this.state.orderBy = params.get('order_by') || '';
        this.state.status = params.get('status') || '';
        this.state.country = params.get('country') || '';
        this.state.highlight = params.get('highlight') === '1';
    }

    bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            this.updateUI();
            this.attachEventListeners();
        });
    }

    updateUI() {
        this.state.categories.forEach(categoryId => {
            const checkbox = document.getElementById(`cat_${categoryId}`);
            if (checkbox) checkbox.checked = true;
        });

        const includeSubcategories = document.getElementById('includeSubcategories');
        if (includeSubcategories) includeSubcategories.checked = this.state.includeSubcategories;

        const orderByFilter = document.getElementById('orderByFilter');
        if (orderByFilter) orderByFilter.value = this.state.orderBy;

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) statusFilter.value = this.state.status;

        const countryFilter = document.getElementById('countryFilter');
        if (countryFilter) countryFilter.value = this.state.country;

        const premiumFilter = document.getElementById('premiumFilter');
        if (premiumFilter) premiumFilter.checked = this.state.highlight;

        this.updateFilterCount();
    }

    attachEventListeners() {
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const categoryId = parseInt(e.target.value);
                if (e.target.checked) {
                    if (!this.state.categories.includes(categoryId)) {
                        this.state.categories.push(categoryId);
                    }
                } else {
                    this.state.categories = this.state.categories.filter(id => id !== categoryId);
                }
                this.updateFilterCount();
            });
        });

        const includeSubcategories = document.getElementById('includeSubcategories');
        if (includeSubcategories) {
            includeSubcategories.addEventListener('change', (e) => {
                this.state.includeSubcategories = e.target.checked;
            });
        }

        const orderByFilter = document.getElementById('orderByFilter');
        if (orderByFilter) {
            orderByFilter.addEventListener('change', (e) => {
                this.state.orderBy = e.target.value;
                this.updateFilterCount();
            });
        }

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.state.status = e.target.value;
                this.updateFilterCount();
            });
        }

        const countryFilter = document.getElementById('countryFilter');
        if (countryFilter) {
            countryFilter.addEventListener('change', (e) => {
                this.state.country = e.target.value;
                this.updateFilterCount();
            });
        }

        const premiumFilter = document.getElementById('premiumFilter');
        if (premiumFilter) {
            premiumFilter.addEventListener('change', (e) => {
                this.state.highlight = e.target.checked;
                this.updateFilterCount();
            });
        }

        const applyButton = document.getElementById('applyFilters');
        if (applyButton) {
            applyButton.addEventListener('click', () => this.applyFilters());
        }

        const clearButton = document.getElementById('clearFilters');
        if (clearButton) {
            clearButton.addEventListener('click', () => this.clearFilters());
        }
    }

    updateFilterCount() {
        let count = 0;
        if (this.state.categories.length > 0) count++;
        if (this.state.orderBy) count++;
        if (this.state.status) count++;
        if (this.state.country) count++;
        if (this.state.highlight) count++;

        const badge = document.getElementById('filterCount');
        const modalTitle = document.querySelector('.modal-title');
        
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else if (modalTitle) {
                modalTitle.insertAdjacentHTML('beforeend', 
                    `<span class="badge bg-light text-dark ms-2" id="filterCount">${count}</span>`);
            }
        } else if (badge) {
            badge.remove();
        }
    }

    applyFilters() {
        const url = new URL(window.location);
        url.search = '';
        
        if (this.state.categories.length > 0) {
            url.searchParams.set('categories', this.state.categories.join(','));
        }
        
        if (this.state.includeSubcategories) {
            url.searchParams.set('include_subcategories', '1');
        }
        
        if (this.state.orderBy) {
            url.searchParams.set('order_by', this.state.orderBy);
        }
        
        if (this.state.status) {
            url.searchParams.set('status', this.state.status);
        }
        
        if (this.state.country) {
            url.searchParams.set('country', this.state.country);
        }
        
        if (this.state.highlight) {
            url.searchParams.set('highlight', '1');
        }
        
        window.location.href = url.toString();
    }

    clearFilters() {
        this.state = {
            categories: [],
            includeSubcategories: false,
            orderBy: '',
            status: '',
            country: '',
            highlight: false
        };
        
        document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
        
        const includeSubcategories = document.getElementById('includeSubcategories');
        if (includeSubcategories) includeSubcategories.checked = false;
        
        const orderByFilter = document.getElementById('orderByFilter');
        if (orderByFilter) orderByFilter.value = '';
        
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) statusFilter.value = '';
        
        const countryFilter = document.getElementById('countryFilter');
        if (countryFilter) countryFilter.value = '';
        
        const premiumFilter = document.getElementById('premiumFilter');
        if (premiumFilter) premiumFilter.checked = false;
        
        this.updateFilterCount();
        
        window.location.href = window.location.pathname;
    }
}

new FilterSystem();
