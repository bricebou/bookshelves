const purgecss = require('@fullhuman/postcss-purgecss');

module.exports = {
  plugins: [
    require('autoprefixer'),
    purgecss({
      content: [
        './templates/**/*.html.twig',
        './assets/**/*.js'
      ]
    })
  ]
}