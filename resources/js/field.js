Nova.booting((Vue) => {
    Vue.component('detail-nova-flexible-content', require('./components/DetailField.vue').default)
    Vue.component('detail-nova-flexible-content-group', require('./components/DetailGroup.vue').default)
    Vue.component('form-nova-flexible-content', require('./components/FormField.vue').default)
    Vue.component('form-nova-flexible-content-group', require('./components/FormGroup.vue').default)
    Vue.component('flexible-drop-menu', require('./components/OriginalDropMenu.vue').default)
    Vue.component('flexible-search-menu', require('./components/SearchMenu.vue').default)
    Vue.component('delete-flexible-content-group-modal', require('./components/DeleteGroupModal.vue').default)
})
