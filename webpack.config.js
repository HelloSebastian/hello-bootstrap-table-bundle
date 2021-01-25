var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('./src/Resources/public/')
    .setPublicPath('/')
    .setManifestKeyPrefix('bundles/hellobootstraptable')
    .addExternals({
        jquery: 'jQuery'
    })
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(false)
    .enableVersioning(false)
    .disableSingleRuntimeChunk()

    .addEntry('bootstrap-table', './assets/app.js')
;

const config = Encore.getWebpackConfig();
config.node = {
    fs: "empty"
};

module.exports = config;