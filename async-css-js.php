<?php

/*
Plugin Name: Async CSS & JS
Plugin URI: https://github.com/omacranger/async-css-js
Description: Adds required tags for Async CSS & deferred JS using the loadCSS library as a fallback for browsers that don't support the preload attribute.
Version: 1.0.0
Author: Logan Graham
Author URI: https://twitter.com/LoganPGraham
*/

class Async_CSS_JS {

	protected static $_instance = null;

	function __construct() {
		// CSS
		add_filter( 'style_loader_tag', array( $this, 'filter_style_tag' ), 10, 4 );
		add_action( 'wp_head', array( $this, 'print_loadcss_inline' ), 150 );

		// JS
		add_filter( 'script_loader_tag', array( $this, 'add_defer_parameter' ), 10, 3 );
	}

	/**
	 * Provides filtered style tag for loadCSS integration
	 *
	 * @param $link_html
	 * @param $handle
	 * @param $href
	 * @param $media
	 *
	 * @return string
	 */
	function filter_style_tag( $link_html, $handle, $href, $media ) {
		if ( is_admin() || $GLOBALS['pagenow'] === 'wp-login.php' ) {
			return $link_html;
		}

		// Array to exclude specific styles from being deferred
		$excluded = apply_filters( 'async_css_js_excludedcss', array() );

		if ( ! in_array( $handle, $excluded ) ) {
			return "<link rel='preload' id='$handle-css' href='$href' type='text/css' media='$media' as='style'  onload=\"this.rel='stylesheet'\" />\n<noscript>$link_html</noscript>";
		}

		return $link_html;
	}

	/**
	 * Adds inlined version of loadCSS library as late in wp_head as possible.
	 */
	function print_loadcss_inline() {
		if ( ! is_admin() ) {
			printf( '<script id="loadcss" type="text/javascript">%s</script>', file_get_contents( __DIR__ . '/assets/loadcss.min.js' ) );
		}
	}

	/**
	 * Adds defer parameter to JS scripts, where available.
	 *
	 * @param $tag
	 * @param $handle
	 * @param $src
	 *
	 * @return string
	 */
	function add_defer_parameter( $tag, $handle, $src ) {
		global $wp_scripts;

		// Don't defer if the current url is AMP enabled, or if the user is currently inside the dashboard
		if ( preg_match( '`\/amp\/?`', $_SERVER['REQUEST_URI'] ) || is_admin() ) {
			return $tag;
		}

		// Don't return defer attribute on <= IE9
		if ( $this->is_IE9orlower() ) {
			return $tag;
		}

		// Array to exclude specific scripts from being deferred
		$excluded = apply_filters( 'async_css_js_excludedjs', array() );

		if ( ! in_array( $handle, $excluded ) ) {
			$before_handle = $wp_scripts->print_inline_script( $handle, 'before', false );
			$after_handle = $wp_scripts->print_inline_script( $handle, 'after', false );
			$obj           = $wp_scripts->registered[ $handle ];

			// Reuse functionality from WP_Scripts for conditionals
			$cond_before          = $cond_after = '';
			$conditional          = isset( $obj->extra['conditional'] ) ? $obj->extra['conditional'] : '';

			if ( $conditional ) {
				$cond_before = "<!--[if {$conditional}]>\n";
				$cond_after  = "<![endif]-->\n";
			}

			if ( $before_handle ) {
				$before_handle = sprintf( "<script type='text/javascript'>\n%s\n</script>\n", $before_handle );
			}

			if ( $after_handle ) {
				$after_handle = sprintf( "<script type='text/javascript'>\n%s\n</script>\n", $after_handle );
			}

			return "{$cond_before}{$before_handle}<script type='text/javascript' src='$src' defer></script>{$after_handle}{$cond_after}";
		}

		return $tag;
	}

	/**
	 * Conditional User-Agent check to see if current browser is IE9 or Lower.
	 *
	 * @return bool
	 */
	function is_IE9orlower() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			if ( preg_match( '/MSIE [0-9]\.0/', $useragent ) ) {
				return true;
			}
		}

		return false;
	}


	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

Async_CSS_JS::instance();