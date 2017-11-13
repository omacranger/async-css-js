# Async CSS & JS for WordPress
This plugin was developed to enable Async CSS, and deferred JS for for any site using WordPress. On simple sites, it will work out of the box without any additional configuration. Any additional plugins that utilizes inlined JS techniques improperly (such as inlining jQuery options instead of using something like `wp_localize_script`) might experience errors. 

> Notice: It is **highly** recommended to use something like [Grunt Critical](https://github.com/bezoerb/grunt-critical) on a site when utilizing this plugin. Otherwise, there can be a flash of unstyled text / content while the rest of the assets asynchronously load.

It utilizes the [loadCSS](https://github.com/filamentgroup/loadCSS) library as a fallback for browsers that don't support the [preload](https://caniuse.com/#search=preload) attribute.

### Plugins where issues might arise

Any plugin relying on inlined JS can be affected by installing this plugin. Some plugins with known issues I'll list out below, and try to offer some resources (or snippets) on how to fix.

* Gravity Forms - [Pretty good fix here](https://gist.github.com/eriteric/5d6ca5969a662339c4b3)