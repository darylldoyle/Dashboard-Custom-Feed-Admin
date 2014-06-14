<?php
/**
 * Plugin Name: Dashboard Custom Feed Admin
 * Plugin URI: http://enshrined.co.uk
 * Description: Creates a custom post type to power a custom feed
 * Version: 1.0
 * Author: Daryll Doyle
 * Author URI: http://enshrined.co.uk
 * License: GPL2
 */

class DCFADMIN {

    function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();
    }

    function init() {
        $labels = array(
            'name'                => _x( 'DCF Updates', 'Post Type General Name', 'text_domain' ),
            'singular_name'       => _x( 'DCF Update', 'Post Type Singular Name', 'text_domain' ),
            'menu_name'           => __( 'DCF Updates', 'text_domain' ),
            'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
            'all_items'           => __( 'All Updates', 'text_domain' ),
            'view_item'           => __( 'View Item', 'text_domain' ),
            'add_new_item'        => __( 'Add New Update', 'text_domain' ),
            'add_new'             => __( 'Add New', 'text_domain' ),
            'edit_item'           => __( 'Edit Update', 'text_domain' ),
            'update_item'         => __( 'Update Update', 'text_domain' ),
            'search_items'        => __( 'Search Update', 'text_domain' ),
            'not_found'           => __( 'Not found', 'text_domain' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
        );
        $args = array(
            'label'               => __( 'dcf_update', 'text_domain' ),
            'description'         => __( 'DCF Updates', 'text_domain' ),
            'labels'              => $labels,
            'supports'            => array( 'editor', 'title'),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-list-view',
            'can_export'          => false,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'page',
        );
        register_post_type( 'dcf_update', $args );
    }

    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('dcf/(.+)' => 'index.php?dcf='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }

    function add_query_vars($qvars) {
        $qvars[] = 'dcf';
        return $qvars;
    }

    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    function template_redirect_intercept() {
        global $wp_query;
        if ($wp_query->get('dcf')) {
            $this->getoutput();
            exit;
        }
    }

    function getoutput() {
        $dcfa_options = get_option( 'dcfa-option' );

        $message = array();
        $message['title'] = $dcfa_options['feed_name'];

        $queryObject = new WP_Query( 'post_type=dcf_update&posts_per_page='.$dcfa_options['total_items'] );
        // The Loop!
        if ($queryObject->have_posts()) {

            while ($queryObject->have_posts()) {
                $queryObject->the_post();

                $tmpMessage = array();
                $tmpMessage['title'] = get_the_title();
                $tmpMessage['content'] = get_the_content();

                $message['items'][] = $tmpMessage;
                unset($tmpMessage);

            }
            $this->output($message);
        }
    }

    function output( $output ) {
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

        // Commented to display in browser.
        header( 'Content-type: application/json' );

        echo json_encode( $output );
    }
}

include('Dashboard-Custom-Feed-Admin-Options.php');

$DCFADMINCode = new DCFADMIN();
register_activation_hook( __file__, array($DCFADMINCode, 'activate') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules
add_filter('rewrite_rules_array', array($DCFADMINCode, 'create_rewrite_rules'));
add_filter('query_vars',array($DCFADMINCode, 'add_query_vars'));


// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
add_filter('admin_init', array($DCFADMINCode, 'flush_rewrite_rules'));
add_action( 'template_redirect', array($DCFADMINCode, 'template_redirect_intercept') );

// Initialze Custom Post Type
add_action( 'init', array($DCFADMINCode, 'init'));
