const mix = require('laravel-mix');
const path = require('path');

mix.ts('resources/js/main.tsx', 'public/js/app.js').react()

   .webpackConfig({
     resolve: {
       extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
       alias: {
         '@': path.resolve(__dirname, 'resources/js'),
       },
     },
   })

   .postCss('resources/css/app.css', 'public/css', [
    //  require('postcss-import'),
     require('tailwindcss'),
     require('autoprefixer'),
   ])

   .sass('resources/scss/app.scss', 'public/css')
   .sass('resources/sass/style.scss', 'public/css')
   .sass('resources/sass/app.scss', 'public/css');