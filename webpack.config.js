const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enablePostCssLoader() // Si nécessaire
    .configureBabelPresetEnv((options) => {
        options.useBuiltIns = 'usage'; // Active polyfills automatiques
        options.corejs = 3; // Version de CoreJS
    });

module.exports = Encore.getWebpackConfig();
