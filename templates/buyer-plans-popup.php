<style>
    button.mercadopago-button {
        background-image: url(<?php echo get_stylesheet_directory_uri()."/img/mercadopago-logo.png"; ?>);
    }

</style>

<?php 

    (function(){

        GLOBAL $post;

        if(!$post || $post->post_type != 'estate_property'){
            return;
        }

        $buyer_id = get_current_user_id();

        if(!$buyer_id){
            return;
        }


        $current_viewer_role = get_user_meta($buyer_id, 'user_estate_role',true);

        if($current_viewer_role != buyer_role_id){
            return;
        }

        $current_subscription = get_buyer_current_subscription($current_viewer_id, $property_id);

        if($current_subscription){
            return;
        }


        ?>

            <div class="shadow-buyer-plans-pop">

            <div class="buyer-plans-pop">
                <i class="fa fa-times"></i>
                
                <h5>
                    <?php echo get_option('buyer_memberships_text_pop','') ;?>
                </h5>

                <div class='row'>

                <?php 
                
                $mercadopago_active = get_option("mercadopago_active");
                $mercadopago_public_key = get_option("mercadopago_public_key", '');
                                    
                ;?>

                <?php foreach(memberships_types as  $membership_type => $label):?>

                <?php 

                    $seller_membership = 'seller_membership_'.$membership_type;
                    $seller_membership_name = get_option($seller_membership.'_name' );
                    $seller_membership_price = get_option($seller_membership.'_price' );
                    $seller_membership_description = get_option($seller_membership.'_description' );
                
                ?>

                    <div class='col-md-4'>
                            
                        <h5><?php echo $seller_membership_name;?></h5>
                        <div>
                            <?php echo $seller_membership_description; ?>
                        </div>
                        <br>

                        <?php if($mercadopago_active): ?>
                        <form action="<?php echo admin_url('admin-post.php');?>" method="POST">
                            <input type="hidden" name="action" value="buyer_subscribe_plan"/>
                            <input type="hidden" name="url" value="<?php echo get_the_permalink();?>">
                            <input type="hidden" name="property_id" value="<?php echo $post->ID; ?>"/>
                            <input type="hidden" name="subscription_type" value="<?php echo $membership_type;?>"/>
                            <script
                                src="https://www.mercadopago.cl/integrations/v1/web-tokenize-checkout.js"
                                data-public-key="<?php echo $mercadopago_public_key;?>"
                                data-transaction-amount="<?php echo $seller_membership_price;?>" data-button-label="">
                            </script>
                        </form>
                        <?php endif;?>
                        
                    </div>

                <?php endforeach;?>
                </div>

            </div>

            </div>

            <script>

            jQuery(document).ready(function(){

            /*
            var callCompleted = true;

            function checkout(e){
            
            if(!callCompleted){
                return;
            }

            var ajax_url = ajaxcalls_vars.admin_url+"admin-post.php";

            var dataForm = jQuery(this).parents("form").serialize();
            
            jQuery.get(ajax_url, dataForm+"&action=buyer_subscribe_plan").
                done(function(d){

                    if(d.success){
                        location.reload();
                    }
                })
                .always(function(){
                    callCompleted = true;
                })

            }

            */

            //jQuery(".choose-plan-form button").click();




                jQuery(".buyer-plans-pop i").click(function(){
                    jQuery(".shadow-buyer-plans-pop").fadeOut();
                })

                jQuery(".subscription-needed button").click(function(){
                    jQuery(".shadow-buyer-plans-pop").css("display", "flex");
                })

                jQuery(".shadow-buyer-plans-pop").css("display", "flex")
            })

            </script>

            <?php
    })();
