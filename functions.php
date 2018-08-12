<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:
        
if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css' ); 
    }
endif;

load_child_theme_textdomain('wpestate', get_stylesheet_directory().'/languages');
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css' );

// END ENQUEUE PARENT ACTION





////////////////// BUYERS ///////////////////////

define('buyer_role_id', 99);
define('buyer_role_name', 'Comprador');


function add_buyer_membership(){

	$args = [
        'labels'             => [
            "name" => "Membresias compradores"
        ],
		'public'             => true,
		'supports'           => ['title', 'editor', 'author', 'custom-fields' ]
    ];

	register_post_type( 'buyer_membership', $args );
    
}

add_action( 'init', 'add_buyer_membership');


function get_buyer_current_subscription($buyer_id, $property_id = false){

    //si posee una subscripcion general la retornamos y obviamos cualquier subscription individual
    $general_args = [
        "post_type" => "buyer_membership",
        "numberposts" => 1,
        'orderby'   => 'date', 
        'order'     => 'DESC',
        'meta_query' => [
            [
                'key' => 'buyer_membership_type',
                'value' => "general",
                'compare' => '='
            ]
        ],
        "post_author" => $buyer_id,
        'date_query' => [            
            'after' => date('Y-m-d', strtotime('-30 days')) 
        ]
    ];

    $general_subscriptions = get_posts($general_args);

    if($general_subscriptions && count($general_subscriptions)){
        $current_general_subscription = $general_subscriptions[0];

        $current_subscription = [
            "ID" => $current_general_subscription->ID            
        ];
        return $current_subscription;
    }

    if(!$property_id) {
        return false;
    }

    $single_args = [
        "post_type" => "buyer_membership",
        "numberposts" => 1,
        "post_author" => $buyer_id,
        'orderby'   => 'date', 
        'order'     => 'DESC',
        'meta_query' => [
            'relation' => "AND",
            [
                'key' => 'buyer_membership_type',
                'value' => 'single',
                'compare' => '='
            ],
            [
                'key' => 'buyer_membership_property',
                'value' => $property_id,
                'compare' => '='
            ]
        ]
    ];

    $single_subscriptions = get_posts($single_args);

    if($single_subscriptions && count($single_subscriptions)){

        $current_single_subscription = $single_subscriptions[0];
        $current_subscription = [
            "ID" => $current_single_subscription->ID            
        ];
        return $current_subscription;
    }

    return false;

}
/*
function can_buyer_get_info($buyer_id, $property_id){

    $subscription = get_buyer_current_subscription($buyer_id, $property_id);

    if($subscription !== false){
        return $allowed;
    }

   
}*/



function ajax_buyer_subscribe_plan() {
    
    $buyer_id = (isset($_GET["buyer_id"])) ? $_GET["buyer_id"] : false;
    $subscription_type = (isset($_GET["subscription_type"])) ? $_GET["subscription_type"] : false;  

    if(!$buyer_id || !$subscription_type){
        wp_send_json_error("Falta ID del comprador o tipo de suscripcion");
        return;
    }   

    $property_id = (isset($_GET["property_id"])) ? $_GET["property_id"] : false;

    $current_subscription = get_buyer_current_subscription($buyer_id, $property_id);

    if($current_subscription){
        wp_send_json_error("ya posee una suscripcion");
        return;
    }

    

    $new_subscription_args = [
        'post_title'    => 'Membresia',
        'post_content'  => 'x', 
        "post_type" => "buyer_membership",
        "post_status" => "publish",
        "meta_input" => [
            "buyer_membership_type" => $subscription_type
        ],
        "post_author" => $buyer_id
    ];

    if($property_id && $subscription_type == "single"){
        $new_subscription_args["meta_input"]["buyer_membership_property"] = $property_id;
    }    
    header('Content-Type: application/json');
    echo json_encode(["success" => true]);
    wp_insert_post($new_subscription_args);
    


}


add_action( 'admin_post_buyer_subscribe_plan', 'ajax_buyer_subscribe_plan' );
add_action( 'admin_post_nopriv_buyer_subscribe_plan', 'ajax_buyer_subscribe_plan' );


function print_subscription_part($property_id, $callback, $print = true){

    if(is_user_logged_in()){
        
        $current_viewer_id = get_current_user_id();

        if($current_viewer_id !== 0){
            
            $current_viewer_role = get_user_meta($current_viewer_id, 'user_estate_role',true);
                
            if($current_viewer_role == buyer_role_id){//si es un usuario de tipo comprador

                $current_viewer_subscription = get_buyer_current_subscription($current_viewer_id, $property_id);
                
                if($current_viewer_subscription){
                    $callback();
                } else if($print) {
                    get_template_part('templates/agent_subscription_needed');
                }
                
            } else if($print) {
                get_template_part('templates/agent_role_not_allowed');
            }
        } else if($print) {//si no se ha logeado
            get_template_part('templates/agent_log_needed');
        }
    } else if($print) {//si no se ha logeado
        get_template_part('templates/agent_log_needed');
    }

}