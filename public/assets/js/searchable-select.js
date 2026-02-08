class SearchableSelect {
    constructor(container) {
        this.container = container;
        this.input = container.querySelector('.searchable-select-input');
        this.hiddenInput = container.querySelector('input[type="hidden"]');
        this.dropdown = container.querySelector('.searchable-select-dropdown');
        this.options = Array.from(container.querySelectorAll('.searchable-select-option'));
        this.highlightedIndex = -1;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        this.setInitialValue();
        this.bindEvents();
        this.updateOptionsVisibility();
    }

    setInitialValue() {
        const selectedValue = this.hiddenInput.value;
        const selectedOption = this.options.find(opt => opt.dataset.value === selectedValue);
        
        if (selectedOption) {
            this.input.value = selectedOption.textContent.trim();
            selectedOption.classList.add('selected');
        }
    }

    bindEvents() {
        this.input.addEventListener('focus', () => this.open());
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        this.dropdown.addEventListener('click', (e) => {
            const option = e.target.closest('.searchable-select-option');
            if (option && !option.classList.contains('hidden')) {
                this.selectOption(option);
            }
        });

        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });
    }

    handleInput(e) {
        const query = e.target.value.toLowerCase();
        this.filterOptions(query);
        this.highlightedIndex = -1;
        this.updateHighlight();
        
        if (!this.isOpen) {
            this.open();
        }
    }

    handleKeydown(e) {
        if (!this.isOpen) {
            if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                this.open();
            }
            return;
        }

        const visibleOptions = this.options.filter(opt => !opt.classList.contains('hidden'));

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.highlightedIndex = Math.min(this.highlightedIndex + 1, visibleOptions.length - 1);
                this.updateHighlight();
                this.scrollToHighlighted();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                this.updateHighlight();
                this.scrollToHighlighted();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.highlightedIndex >= 0 && visibleOptions[this.highlightedIndex]) {
                    this.selectOption(visibleOptions[this.highlightedIndex]);
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                this.close();
                break;
        }
    }

    filterOptions(query) {
        this.options.forEach(option => {
            const text = option.textContent.toLowerCase();
            const matches = text.includes(query);
            option.classList.toggle('hidden', !matches);
        });
    }

    updateHighlight() {
        this.options.forEach(opt => opt.classList.remove('highlighted'));
        
        const visibleOptions = this.options.filter(opt => !opt.classList.contains('hidden'));
        if (this.highlightedIndex >= 0 && visibleOptions[this.highlightedIndex]) {
            visibleOptions[this.highlightedIndex].classList.add('highlighted');
        }
    }

    scrollToHighlighted() {
        const visibleOptions = this.options.filter(opt => !opt.classList.contains('hidden'));
        const highlighted = visibleOptions[this.highlightedIndex];
        
        if (highlighted) {
            highlighted.scrollIntoView({
                block: 'nearest',
                behavior: 'smooth'
            });
        }
    }

    selectOption(option) {
        this.options.forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        
        this.input.value = option.textContent.trim();
        this.hiddenInput.value = option.dataset.value;
        
        this.close();
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.updateOptionsVisibility();
        this.highlightedIndex = -1;
        this.updateHighlight();
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.input.blur();
    }

    updateOptionsVisibility() {
        const query = this.input.value.toLowerCase();
        this.filterOptions(query);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.searchable-select-container');
    containers.forEach(container => {
        new SearchableSelect(container);
    });
});