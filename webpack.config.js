const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    .setPublicPath('/build')


    .addEntry('app', './assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // enables and configuration
    .enableSassLoader()
    .enableStimulusBridge('./assets/controllers.json')

    //HMR
    .configureDevServerOptions(options => {
        options.allowedHosts = 'all';
        options.liveReload = true;
        options.static = {
            watch: false
        };
        options.watchFiles = {
            paths: ['templates/**/*'],
        };
        options.server = {
            type: 'https',
            options: {
                pfx: path.join(process.env.HOME, '.symfony5/certs/default.p12'),
            }
        }
    })

;

module.exports = Encore.getWebpackConfig();
