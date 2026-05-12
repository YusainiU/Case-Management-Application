document.addEventListener('alpine:init', () => {
    Alpine.data('multiselect', (config) => ({
        open: false,
        search: '',

        options: Array.isArray(config.options)
            ? config.options
            : [],

        selected: Array.isArray(config.selected)
            ? config.selected
            : [],

        get selectedOptions() {
            return this.options.filter(option =>
                this.selected.includes(option.value)
            )
        },

        get filteredOptions() {
            const search = this.search.toLowerCase()

            return this.options.filter(option =>
                option.label.toLowerCase().includes(search)
            )
        },

        toggle(option) {
            if (this.selected.includes(option.value)) {
                this.remove(option.value)
                return
            }

            this.selected = [
                ...this.selected,
                option.value
            ]
        },

        remove(value) {
            this.selected = this.selected.filter(
                item => item !== value
            )
        }
    }))
})