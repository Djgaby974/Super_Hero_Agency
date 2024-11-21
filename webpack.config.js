const Encore = require('@symfony/webpack-encore');

Encore
    .enablePostCssLoader()
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js') // Point d'entrée principal
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.presets.push('@babel/preset-env');
    })
    .enablePostCssLoader();

module.exports = Encore.getWebpackConfig();
