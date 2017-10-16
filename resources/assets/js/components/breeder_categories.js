App.Components.BreederCategories = App.Components.Exploitable.Section.extend({
    template: "#breeder-category-template",
    data: function() {
        return {
            categories: [],
            hasArchive: false,
            loading: 1,
            deleter: api.deleteBreederCategory,
            subDropdownOpend: false
        };
    },
    computed: {
        models: function() {
            return this.categories;
        }
    },
    components: {
        'breeder-category-form': App.Components.BreederCategoryForm
    },
    methods: {
        addModel: function() {
          $('#breeder-category-form').modal('show');
        },
        hoverSubdropdown: function (event) {
            var elem = event.target;
            if (elem.classList.contains('hoverClass'))
                this.subDropdownOpend = true;
            else
                this.subDropdownOpend = false;
        },
        updateList: function () {
            api.getBreederCategories().then(data => {
                this.loading = 0;
                this.categories = data.categories;
            });
        },
        clickCategory: function (id) {
            localStorage.setItem('category', id);
            this.$route.router.go('/breeders');
        },
        handleChange: function handleChange() {
            if (this.$route.query.newModal) {
                this.addNew();
            }
        }
    },
    ready: function () {
        this.updateList();
        if (this.$route.query.newModal) {
            this.addModel();
        }
        this.$watch('$route.query.newModal', this.handleChange, { deep: true });
    }
});
