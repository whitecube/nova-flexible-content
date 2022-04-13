let mix = require('laravel-mix');
let path = require('path')

mix.setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .alias({
        'laravel-nova': path.join(__dirname, 'vendor/laravel/nova/resources/js/mixins/packages.js'),
    })
    .webpackConfig({
        externals: {
            vue: 'Vue',
        },
        output: {
            uniqueName: 'whitecube/nova-flexible-content',
        },
    });

mix.sass('resources/sass/field.scss', 'css');
