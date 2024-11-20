const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableVueLoader(() => {}, { runtimeCompilerBuild: true })
    .addPlugin(new webpack.DefinePlugin({
        '__VUE_OPTIONS_API__': JSON.stringify(true),
        '__VUE_PROD_DEVTOOLS__': JSON.stringify(false),
        '__VUE_PROD_HYDRATION_MISMATCH_DETAILS__': JSON.stringify(false)
    }))
    .enableSassLoader()
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enablePostCssLoader();

const config = Encore.getWebpackConfig();

config.watchOptions = {
    poll: 1000, // Vérifie les changements toutes les 1000 ms (1 seconde)
    ignored: /node_modules/ // Ignore le dossier node_modules
};

// Limiter les informations de log en utilisant infrastructureLogging
config.infrastructureLogging = {
    level: 'warn' // Réduit les messages à 'warn' et 'error' uniquement
};

// Exporte la configuration
module.exports = config;
