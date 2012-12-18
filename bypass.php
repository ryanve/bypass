<?php
/*
Plugin Name:   bypass
Plugin URI:    http://github.com/ryanve/bypass
Description:   Enables you to write entry markup in files rather than in the WP editor.
Version:       0.5.0
License:       MIT
Author:        Ryan Van Etten
Author URI:    http://github.com/ryanve
*/

namespace bypass;

function root ( $path = null ) {
    static $root; # php.net/manual/en/language.variables.scope.php
    if ( ! $root ) {
        $root = \dirname( \get_theme_root() ) . '/entries/';     #wp
        $root = \apply_filters( '@bypass_root', $root );         #wp
        $root = \is_dir($root) ? \trailingslashit($root) : null; #wp 
    }
    return null === $path ? $root : \path_join( $root, $path );  #wp
}

\add_filter('the_content', function ($text = null) {

    if ( '' === \trim( (string) $text ) ) {

        $path = \apply_filters( '@bypass_path', \basename( \get_permalink() ) );
        $file = \apply_filters( '@bypass_file', 'content.php', 'the_content' );
        $path = root( \trailingslashit( $path ) . \ltrim( $file, '/' ) );
        
        if ( \is_readable($path) && ! \is_dir($path) ) {
        
            # remove processing (wp-includes/default-filters.php)
            # keep do_shortcode (wp-includes/shortcodes.php)
            \remove_filter( 'the_content', 'wptexturize'        );
            \remove_filter( 'the_content', 'convert_smilies'    );
            \remove_filter( 'the_content', 'convert_chars'      );
            \remove_filter( 'the_content', 'wpautop'            );
            \remove_filter( 'the_content', 'shortcode_unautop'  );

            # read the file
            $html = \file_get_contents($path);
            $html and $text = $html;
        }
    }

    return $text;

}, 0); # use early priority to run before do_shortcode and other filters

\add_filter('the_excerpt', function ($text = null) {

    if ( '' === \trim( (string) $text ) ) {

        $path = \apply_filters( '@bypass_path', \basename( \get_permalink() ) );
        $file = \apply_filters( '@bypass_file', 'excerpt.php', 'the_excerpt' );
        $path = root( \trailingslashit( $path ) . \ltrim( $file, '/' ) );
        
        if ( \is_readable($path) && ! \is_dir($path) ) {
        
            # remove processing (wp-includes/default-filters.php)
            # keep do_shortcode (wp-includes/shortcodes.php)
            \remove_filter( 'the_excerpt', 'wptexturize'        );
            \remove_filter( 'the_excerpt', 'convert_smilies'    );
            \remove_filter( 'the_excerpt', 'convert_chars'      );
            \remove_filter( 'the_excerpt', 'wpautop'            );
            \remove_filter( 'the_excerpt', 'shortcode_unautop'  );

            # read the file
            $html = \file_get_contents($path);
            $html and $text = $html;
        }
    }

    return $text;

}, 0); # use early priority to run before do_shortcode and other filters

#end