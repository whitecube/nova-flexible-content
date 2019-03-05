Nova.booting((Vue, router, store) => {
    // Vue.component('index-nova-flexible-content', require('./components/IndexField'))
    Vue.component('detail-nova-flexible-content', require('./components/DetailField'))
    Vue.component('detail-nova-flexible-content-group', require('./components/DetailGroup'))
    Vue.component('form-nova-flexible-content', require('./components/FormField'))
    Vue.component('form-nova-flexible-content-group', require('./components/FormGroup'))
    Vue.component('icon-arrow-down', require('./components/icons/ArrowDown'))
    Vue.component('icon-arrow-up', require('./components/icons/ArrowUp'))
})
