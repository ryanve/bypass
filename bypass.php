<?php
/*
Plugin Name:   bypass
Plugin URI:    http://github.com/ryanve/bypass
Description:   Enables you to write entry markup in files rather than in the WP editor.
Version:       0.6.0-2
License:       MIT
Author:        Ryan Van Etten
Author URI:    http://github.com/ryanve
*/

array_reduce(array('content', 'excerpt'), function($one, $mode) {
    $data = array('mode' => $mode, 'hook' => "the_$mode");
    $one($data['hook'], function($text) use (&$data) {

        if (strlen(trim((string) $text)))
            # abort if post has normal content
            return $text;

        $data['path'] = array_reduce(array(
            apply_filters('@bypass_root', dirname(get_theme_root()) . '/entries')
          , apply_filters('@bypass_path', basename(get_permalink()))
          , apply_filters('@bypass_file', $data['mode'] . '.html', $data)
        ), function($path, $part) {
            return rtrim($path, '/\\') . '/' . ltrim($part, '/\\');
        }, '');
        
        if (is_file($data['path'])) {
            # Remove most wp-includes/default-filters.php
            # Keep do_shortcode for wp-includes/shortcodes.php
            remove_filter($data['hook'], 'wptexturize');
            remove_filter($data['hook'], 'convert_smilies');
            remove_filter($data['hook'], 'convert_chars');
            remove_filter($data['hook'], 'wpautop');
            remove_filter($data['hook'], 'shortcode_unautop');
            $text = apply_filters('@bypass_html', file_get_contents($data['path']), $data) ?: $text;
        }

        return $text;
    }, 0);

    return $one;
}, function() {
    $args = func_get_args();
    $func = $args[1];
    $args[1] = function() use ($func, $args) {
        # Wrap the original func.
        call_user_func_array('remove_filter', $args);
        return call_user_func_array($func, func_get_args());
    };
    return call_user_func_array('add_filter', $args);
});

#end