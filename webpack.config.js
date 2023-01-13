const Encore = require("@symfony/webpack-encore");
const { exit } = require("browser-sync");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  ;
if (process.env.SUBPATH || false) {
  console.log("Compiling under subpath " + process.env.SUBPATH);
  Encore
    // public path used by the web server to access the output path
    .setPublicPath(process.env.SUBPATH + "/build")
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build/');
} else {
  console.log("Compiling to be hosted at the root level.");
  Encore
    // public path used by the web server to access the output path
    .setPublicPath("/build")
}
/*
 * ENTRY CONFIG
 *
 * Each entry will result in one JavaScript file (e.g. app.js)
 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
 */
Encore
  .addEntry("admin", "./assets/js/admin.js")
  // .addEntry('textreplacement', './assets/js/textreplacement.js')
  .addEntry("postcodeajax", './assets/js/postcodeajax.js')
  .addEntry('character-counter', './assets/js/character-counter.js')

  /**
   * Add CKEDITOR library and widget files
   */
  .copyFiles([
    { from: './node_modules/ckeditor4/', to: 'ckeditor4/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false },
    { from: './node_modules/ckeditor4/adapters', to: 'ckeditor4/adapters/[path][name].[ext]' },
    { from: './node_modules/ckeditor4/lang', to: 'ckeditor4/lang/[path][name].[ext]' },
    { from: './node_modules/ckeditor4/plugins', to: 'ckeditor4/plugins/[path][name].[ext]' },
    { from: './node_modules/ckeditor4/skins', to: 'ckeditor4/skins/[path][name].[ext]' },
    { from: './node_modules/ckeditor4/vendor', to: 'ckeditor4/vendor/[path][name].[ext]' }
  ])
  .copyFiles([
    { from: './node_modules/govuk-frontend/govuk', to: 'govuk-frontend/[path][name].[ext]', pattern: /all\.js$/, includeSubdirectories: false },
  ])

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  // .enableStimulusBridge("./assets/controllers.json")

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3;
  })

  // enables Sass/SCSS support
  .enableSassLoader()
  .addStyleEntry("styles", "./assets/scss/main.scss")
  .addStyleEntry("adminstyles", "./assets/scss/admin.scss")
  .addStyleEntry("govuk-styles", "./assets/scss/gds.scss")
  .addPlugin(
    new BrowserSyncPlugin(
      // BrowserSync options
      {
        // browse to http://localhost:3000/ during development
        host: "localhost",
        port: 3000,
        // proxy the Webpack Dev Server endpoint
        // (which should be serving on http://localhost:3100/)
        // through BrowserSync
        proxy: "http://127.0.0.1:8000/",
        open: false,
      },
      // plugin options
      {
        // prevent BrowserSync from reloading the page
        // and let Webpack Dev Server take care of this
        reload: true,
      }
    )
  );

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()

module.exports = Encore.getWebpackConfig();
