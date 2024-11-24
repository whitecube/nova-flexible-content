Nova.booting((Vue, router, store) => {
    // Vue.component('index-nova-flexible-content', require('./components/IndexField').default)
    Vue.component('detail-nova-flexible-content', require('./components/DetailField.vue').default)
    Vue.component('detail-nova-flexible-content-group', require('./components/DetailGroup.vue').default)
    Vue.component('form-nova-flexible-content', require('./components/FormField.vue').default)
    Vue.component('form-nova-flexible-content-group', require('./components/FormGroup.vue').default)
    Vue.component('flexible-drop-menu', require('./components/OriginalDropMenu.vue').default)
    Vue.component('flexible-search-menu', require('./components/SearchMenu.vue').default)
    Vue.component('flexible-select-menu', require('./components/SelectMenu.vue').default)
    Vue.component('delete-flexible-content-group-modal', require('./components/DeleteGroupModal.vue').default)
    Vue.component('icon-arrow-down', require('./components/icons/ArrowDown.vue').default)
    Vue.component('icon-arrow-up', require('./components/icons/ArrowUp.vue').default)
    Vue.component('icon-plus-square', require('./components/icons/PlusSquare.vue').default)
    Vue.component('icon-minus-square', require('./components/icons/MinusSquare.vue').default)
})
