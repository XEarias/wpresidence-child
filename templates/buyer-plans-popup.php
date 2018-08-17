<style>
    button.mercadopago-button {
        background-image: url(<?php echo get_stylesheet_directory_uri()."/img/mercadopago-logo.png"; ?>);
        background-position: center;
        background-repeat: no-repeat;
        background-size: 80% auto;
        width: 150px;
        height: 50px;
        color: transparent;
        background-color: #e6e6e6;
    }

</style>

<div class="shadow-buyer-plans-pop">

    <div class="buyer-plans-pop">
        <i class="fa fa-times"></i>


        <div class='row'>
            <div class='col-md-6'>
                    

                <h5>Plan General</h5>
                <p> Descripcion </p>
                <br>
                <form action="<?php echo admin_url('admin-post.php');?>" method="POST">
                    <input type="hidden" name="action" value="buyer_subscribe_plan"/>
                    <input type="hidden" name="url" value="<?php echo get_the_permalink();?>">
                    <input type="hidden" name="property_id" value="<?php echo $post->ID; ?>"/>
                    <input type="hidden" name="subscription_type" value="general"/>
                    <script
                        src="https://www.mercadopago.cl/integrations/v1/web-tokenize-checkout.js"
                        data-public-key="TEST-4840c8f8-981f-472c-936c-cef9b2cbd44f"
                        data-transaction-amount="100.00" data-button-label="">
                    </script>
                </form>
                   
            </div>
            <div class='col-md-6'>
               
                    <h5>Plan Individual</h5>
                    <p> Descripcion </p>
                    <br>
                    
                    <form action="<?php echo admin_url('admin-post.php');?>" method="POST">
                        <input type="hidden" name="action" value="buyer_subscribe_plan"/>
                        <input type="hidden" name="url" value="<?php echo get_the_permalink();?>">
                        <input type="hidden" name="property_id" value="<?php echo $post->ID; ?>"/>
                        <input type="hidden" name="subscription_type" value="single"/>
                        <script
                            src="https://www.mercadopago.cl/integrations/v1/web-tokenize-checkout.js"
                            data-public-key="TEST-4840c8f8-981f-472c-936c-cef9b2cbd44f"
                            data-transaction-amount="100.00" data-button-label="">
                        </script>
                    </form>

            </div>
        </div>

    </div>

</div>

<script>

jQuery(document).ready(function(){

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




    jQuery(".choose-plan-form button").click();

    jQuery(".buyer-plans-pop i").click(function(){
        jQuery(".shadow-buyer-plans-pop").fadeOut();
    })

    jQuery(".subscription-needed button").click(function(){
        jQuery(".shadow-buyer-plans-pop").css("display", "flex");
    })
})

</script>
