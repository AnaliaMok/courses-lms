const mix = require('laravel-mix');

const purgecss = require('@fullhuman/postcss-purgecss')({
  // Specify the paths to all of the template files in your project
  content: ['./resources/views/**/*.blade.php'],

  // Include any special characters you're using in this regular expression
  defaultExtractor: content => content.match(/[A-Za-z0-9-_:/]+/g) || [],
});

mix
  .js('resources/js/app.js', 'public/js')
  //   .sass('resources/sass/app.scss', 'public/css')
  .postCss('resources/css/main.css', 'public/css', [
    require('tailwindcss')('./resources/css/tailwind.config.js'),
    require('autoprefixer'),
    ...(process.env.NODE_ENV === 'production' ? [purgecss] : []),
  ]);
