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
define('memberships_types', [
    "single" => "Plan por Propiedad",
    "general" => "Plan Mensual",
    "ultimate" => "Plan Anual"   
]);


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


    //si posee una subscripcion ultimate la retornamos y obviamos cualquier subscription
    $ultimate_args = [
        "post_type" => "buyer_membership",
        "numberposts" => 1,
        'orderby'   => 'date', 
        'order'     => 'DESC',
        'meta_query' => [
            [
                'key' => 'buyer_membership_type',
                'value' => "ultimate",
                'compare' => '='
            ]
        ],
        "author" => $buyer_id,
        'date_query' => [            
            'after' => date('Y-m-d', strtotime('-365 days')) 
        ]
    ];

    $ultimate_subscriptions = get_posts($ultimate_args);

    if($ultimate_subscriptions && count($ultimate_subscriptions)){
        $current_ultimate_subscription = $ultimate_subscriptions[0];

        $current_subscription = [
            "ID" => $current_ultimate_subscription->ID            
        ];
        return $current_subscription;
    }

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
        "author" => $buyer_id,
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
        "author" => $buyer_id,
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

require_once __DIR__."/libs/mercadopago/vendor/autoload.php";


function mercadopago_pay ($subscription_value, $token, $installments, $payment_method_id, $issuer_id, $email) {

    $mercadopago_active = get_option("mercadopago_active");
                        
    if(!$mercadopago_active){
        return false;
    }

    $mercadopago_access_token = get_option("mercadopago_access_token", '');

    MercadoPago\SDK::setAccessToken($mercadopago_access_token);
    //...
    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = $subscription_value;
    $payment->token = $token;
    $payment->description = "Compra de plan";
    $payment->installments = $installments;
    $payment->payment_method_id = $payment_method_id;
    $payment->issuer_id = $issuer_id;
    $payment->payer = [
        "email" => $email
    ];
    
    
    // Guarda y postea el pago
    $payment->save();
    //...
    // Imprime el estado del pago
    if($payment->status != 'approved'){
        //wp_safe_redirect( $url);
        return false;
    }

    return true;
}


function ajax_buyer_subscribe_plan() {


    $url = $_POST["url"];

    if(!is_user_logged_in()){
        
        wp_safe_redirect($url);
        exit();
    }

    $buyer = wp_get_current_user();
    $buyer_id = $buyer->ID;
    
    $subscription_type = (isset($_POST["subscription_type"])) ? $_POST["subscription_type"] : false;  

    if(!$subscription_type){
        wp_safe_redirect($url);
        exit();
    }   

    $property_id = (isset($_POST["property_id"])) ? $_POST["property_id"] : false;

    $current_subscription = get_buyer_current_subscription($buyer_id, $property_id);

    if($current_subscription){
        wp_safe_redirect($url);
        exit();
    }

    //$membership_type = memberships_types[$subscription_type];

    $seller_membership = 'seller_membership_'.$subscription_type;
    $seller_membership_price = get_option($seller_membership.'_price' );

    $payment_method_name = "";

    if(isset($_POST["mercadopago"])){

        $payment_method_name = "mercadopago";

        $token = $_POST["token"];
        $payment_method_id = $_POST["payment_method_id"];
        $installments = $_POST["installments"];
        $issuer_id = $_POST["issuer_id"];    

        $res = mercadopago_pay($seller_membership_price, $token, $installments, $payment_method_id, $issuer_id, $buyer->user_email);
      
        if(!$res){
            wp_safe_redirect( $url);
            exit();
        }

    }

   
        
    
    $new_subscription_args = [
        'post_title'    => 'Membresia',
        'post_content'  => 'x', 
        "post_type" => "buyer_membership",
        "post_status" => "publish",
        "meta_input" => [
            "buyer_membership_type" => $subscription_type,
            "buyer_payment_method" =>  $payment_method_name
        ],
        "post_author" => $buyer_id
    ];

    if($property_id && $subscription_type == "single"){
        $new_subscription_args["meta_input"]["buyer_membership_property"] = $property_id;
    }    
   
    wp_safe_redirect( $url);

    wp_insert_post($new_subscription_args);
    


}


add_action( 'admin_post_buyer_subscribe_plan', 'ajax_buyer_subscribe_plan' );
add_action( 'admin_post_nopriv_buyer_subscribe_plan', 'ajax_buyer_subscribe_plan' );



function ajax_seller_subscribe_plan() {


    $url = $_POST["url"];

    if(!is_user_logged_in()){
        
        wp_safe_redirect($url);
        exit();
    }


    $seller = wp_get_current_user();
    $seller_id = $seller->ID;
    $selected_pack            =   intval( $_POST['pack_id'] );
    $subscription_value              =   get_post_meta($selected_pack, 'pack_price', true);

    $token = $_POST["token"];
    $payment_method_id = $_POST["payment_method_id"];
    $installments = $_POST["installments"];
    $issuer_id = $_POST["issuer_id"];

    $res = mercadopago_pay($subscription_value, $token, $installments, $payment_method_id, $issuer_id, $buyer->user_email);
    
    wpestate_upgrade_user_membership($seller_id,$selected_pack,'One Time', '',1);
    
    wp_safe_redirect( $url);
    exit();
    
}


add_action( 'admin_post_seller_subscribe_plan', 'ajax_seller_subscribe_plan' );
add_action( 'admin_post_nopriv_seller_subscribe_plan', 'ajax_seller_subscribe_plan' );

function print_subscription_part($property_id, $callback, $print = true){

    if(!is_user_logged_in()){
        if($print){
            get_template_part('templates/agent_log_needed');
        }        
        return;
    }

    $current_viewer_id = get_current_user_id();

    if($current_viewer_id === 0){
        if($print){
            get_template_part('templates/agent_log_needed');
        }
        return;
    }

    /*  $current_viewer_role = get_user_meta($current_viewer_id, 'user_estate_role',true);
      
    if($current_viewer_role != buyer_role_id){//si es un usuario de tipo comprador
        if($print){
            get_template_part('templates/agent_role_not_allowed');
        }
        return;
    }*/

    $current_viewer_subscription = get_buyer_current_subscription($current_viewer_id, $property_id);
                
    if(!$current_viewer_subscription){
        if($print){
            get_template_part('templates/agent_subscription_needed');
        }
        return;         
    }

    $callback();
    



}


///////////////////////////////////////////////////////
/////////// Opciones de control de pasarelas///////////
///////////////////////////////////////////////////////


//seccion de textos
function mercadopago_section_html (){
    echo "Datos para funcionalidades de mercadopago";
}

function mercadopago_public_key_html(){

    $mercadopago_public_key = get_option( 'mercadopago_public_key', '');

    ?>

    <input type="text" name="mercadopago_public_key" value="<?php echo $mercadopago_public_key;?>"/>

    <?php 
}


function mercadopago_access_token_html(){

    $mercadopago_access_token = get_option( 'mercadopago_access_token', '');

    ?>

    <input type="text" name="mercadopago_access_token" value="<?php echo $mercadopago_access_token;?>"/>

    <?php 
}



function mercadopago_active_html(){

    $mercadopago_active = get_option( 'mercadopago_active', '');

    ?>

    <input type="checkbox" name="mercadopago_active" value="active" <?php checked('active', $mercadopago_active);?> />

    <?php 
}



function memberships_section_html(){
    echo '';
}
function memberships_name_html($args){

    $id = $args["id"];

    $membership_value = get_option( $id, '');

    ?>

    <input type="text" name="<?php echo $id;?>" value="<?php echo $membership_value;?>"/>

    <?php 

}
function memberships_price_html($args){

    $id = $args["id"];

    $membership_value = get_option( $id, '');

    ?>

    <input type="text" name="<?php echo $id;?>" value="<?php echo $membership_value;?>"/>

    <?php 

}
function memberships_description_html($args){

    $id = $args["id"];

    $membership_value = get_option( $id, '');

    wp_editor($membership_value, $id);

}



function buyer_memberships_pop_section_html(){
    echo '';
}
function buyer_memberships_text_pop_html($args){

    $id = $args["id"];

    $text_popup = get_option( $id, '');

    wp_editor($text_popup, $id);

}




function add_settings_extra_payments(){

    //**** MERCADOPAGO ****/

    //seccion de textos
    add_settings_section(
        'mercadopago_section',
        'Configuración de Mercadopago',
        'mercadopago_section_html',
        'extra_payments_methods'
    );

    /*
    add_settings_field(
        'mercadopago_mode',
        'Modo:',
        'mercadopago_mode_html',
        'extra_payments_methods',
        'mercadopago_section'
    );

    */

    add_settings_field(
        'mercadopago_public_key',
        'Clave Pública:',
        'mercadopago_public_key_html',
        'extra_payments_methods',
        'mercadopago_section'
    );

    add_settings_field(
        'mercadopago_access_token',
        'Token de Acceso:',
        'mercadopago_access_token_html',
        'extra_payments_methods',
        'mercadopago_section'
    );

    add_settings_field(
        'mercadopago_active',
        'Activar:',
        'mercadopago_active_html',
        'extra_payments_methods',
        'mercadopago_section'
    );
    


    //register_setting( 'extra_payments_methods', 'mercadopago_mode' );
    register_setting( 'extra_payments_methods', 'mercadopago_public_key' );
    register_setting( 'extra_payments_methods', 'mercadopago_access_token' );
    register_setting( 'extra_payments_methods', 'mercadopago_active' );


    /***** TEXTO POP UP PLANES *****/
    
    //seccion de textos
    add_settings_section(
        'buyer_memberships_pop_section',
        'Popup de Membresías de Compradores',
        'buyer_memberships_pop_section_html',
        'extra_payments_methods'
    );

    add_settings_field(
        'buyer_memberships_text_pop',
        'Titulo del Popup:',
        'buyer_memberships_text_pop_html',
        'extra_payments_methods',
        'buyer_memberships_pop_section',
        [
            "id" => 'buyer_memberships_text_pop'
        ]
    );

    register_setting( 'extra_payments_methods', 'buyer_memberships_text_pop' );
    



    /**** PLANES DE COMPRADORES ****/

    foreach(memberships_types as  $membership_type => $label){

        $seller_membership = 'seller_membership_'.$membership_type;



        add_settings_section(
            $seller_membership.'_section',
            'Configuración de '.$label,
            'memberships_section_html',
            'extra_payments_methods'
        );

        add_settings_field(
            $seller_membership.'_name',
            'Nombre del Plan:',
            'memberships_name_html',
            'extra_payments_methods',
            $seller_membership.'_section',
            [
                "id" => $seller_membership.'_name'
            ]
        );

        add_settings_field(
            $seller_membership.'_price',
            'Precio del Plan:',
            'memberships_price_html',
            'extra_payments_methods',
            $seller_membership.'_section',
            [
                "id" => $seller_membership.'_price'
            ]
        );

        add_settings_field(
            $seller_membership.'_description',
            'Descripción del Plan:',
            'memberships_description_html',
            'extra_payments_methods',
            $seller_membership.'_section',
            [
                "id" => $seller_membership.'_description'
            ]
        );
        
        register_setting( 'extra_payments_methods',  $seller_membership.'_name' );
        register_setting( 'extra_payments_methods',  $seller_membership.'_price' );
        register_setting( 'extra_payments_methods',  $seller_membership.'_description' );
    }


}

add_action ('admin_init', 'add_settings_extra_payments');



function extra_payments_methods_html(){

    ?>
    <form method="POST" action="options.php">
    <?php 
    
        settings_fields( 'extra_payments_methods' );
        do_settings_sections( 'extra_payments_methods' ); 
        submit_button();
    ?>
    </form>
    <?php 
    
}



function add_theme_admin_pages(){
    add_menu_page("Metodos de Pago Extra",  "Metodos de Pago Extra", "manage_options", "extra_payments_methods", "extra_payments_methods_html" );
    }
    
    
    add_action( 'admin_menu', 'add_theme_admin_pages' );
    



    function days_left($date_start, $type){

        if($type == "ultimate"){
            $duration = 365;
        } else if($type == "general"){
            $duration = 30;
        }
    
        $earlier = new DateTime($date_start);
        $today = new DateTime();
    
        $diff = $today->diff($earlier)->d;
    
        $left = (($duration - $diff) >= 0 ) ? $duration - $diff : 0; 
        $days = [
            "used" => $diff,
            "left" => $left
        ];
        return $days;
    
    
    
    }
?>