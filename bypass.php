<?php
/*
Plugin Name:   bypass
Plugin URI:    http://github.com/ryanve/bypass
Description:   Enables you to write entry markup in files rather than in the WP editor.
Version:       0.6.0-4
License:       MIT
Author:        Ryan Van Etten
Author URI:    http://github.com/ryanve
*/

add_action('init', function() {
    $bypass = array();
    $bypass['name'] = basename(__FILE__, '.php');
    $bypass['root'] = dirname(get_theme_root()) . '/entries';
    $bypass['priority'] = 20;
    $bypass['deploy'] = function($text, $insert = null) {
        return trim(trim($text) . "\n\n" . trim(do_shortcode($insert)));
    };
    $bypass['filter'] = function() use (&$bypass) {
        $a = apply_filters($bypass['name'] . ':' . current_filter(), $bypass);
        if ($a) foreach ($a as $k => $v) $bypass[$k] = $v;
        return $bypass;
    };
    
    array_reduce(array('content', 'excerpt'), function(&$bypass, $type) {
        add_filter("the_$type", function($text) use (&$bypass, $type) {
        
            $bypass['base'] = basename(get_permalink());
            $bypass['file'] = "$type.html";
            $bypass['path'] = null;
            $bypass['filter']();
            
            return is_file($bypass['path'] = $bypass['path'] ?: array_reduce(array(
                $bypass['root']
              , $bypass['base']
              , $bypass['file']
            ), 'path_join')) ? (string) $bypass['deploy']($text, file_get_contents($bypass['path'])) : $text;
        }, $bypass['priority']);

        return $bypass;
    }, $bypass['filter']());
});

#end