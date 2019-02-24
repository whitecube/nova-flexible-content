Nova.booting((Vue, router, store) => {
    // Vue.component('index-nova-flexible-content', require('./components/IndexField'))
    Vue.component('detail-nova-flexible-content', require('./components/DetailField'))
    Vue.component('form-nova-flexible-content', require('./components/FormField'))
    Vue.component('form-nova-flexible-content-group', require('./components/FormGroup'))
})
