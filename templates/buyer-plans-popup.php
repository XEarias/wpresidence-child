<div class="shadow-buyer-plans-pop">

    <div class="buyer-plans-pop">
        <i class="fa fa-times"></i>

        <div class='row'>
            <div class='col-md-6'>
                <form name="general-plan-form" class="choose-plan-form" action="/" method="POST">
                    <input type="hidden" name="subscription_type" value="general"/>
                    <input type="hidden" name="buyer_id" value="<?php echo get_current_user_id(); ?>"/>
                    <input type="hidden" name="property_id" value="<?php echo $post->ID; ?>"/>

                    <h5>Plan General</h5>
                    <p> Descripcion </p>
                    <br>
                    <button class="btn btn-info small" type="button">Seleccionar</button>
                </form>
            </div>
            <div class='col-md-6'>
                <form name="single-plan-form" class="choose-plan-form" action="/" method="POST">
                    <input type="hidden" name="subscription_type" value="single"/>
                    <input type="hidden" name="buyer_id" value="<?php echo get_current_user_id(); ?>"/>
                    <input type="hidden" name="property_id" value="<?php echo $post->ID; ?>"/>
                    <h5>Plan Individual</h5>
                    <p> Descripcion </p>
                    <br>
                    <button class="btn btn-info small" type="button">Seleccionar</button>
                </form>
            </div>
        </div>

    </div>

</div>

<script>

jQuery(document).ready(function(){

    var callCompleted = true;

    jQuery(".choose-plan-form button").click(function(e){
       
        if(!callCompleted){
            return;
        }

        var ajax_url = ajaxcalls_vars.admin_url+"admin-post.php";

        var dataForm = jQuery(this).parents("form").serialize();
        
        jQuery.get(ajax_url, dataForm+"&action=buyer_subscribe_plan").
            done(function(d){

                console.log(d);
                if(d.success){
                    location.reload();
                }
            })
            .always(function(){
                callCompleted = true;
            })

    });

    jQuery(".buyer-plans-pop i").click(function(){
        jQuery(".shadow-buyer-plans-pop").fadeOut();
    })

    jQuery(".subscription-needed button").click(function(){
        jQuery(".shadow-buyer-plans-pop").css("display", "flex");
    })
})

</script>
