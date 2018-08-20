<div class="log-needed">

    <!--Por favor, <b>inicia sesión</b> con tu cuenta de <b>Comprador</b> para poder <b>ver la información</b> de contacto.-->

    <button class="btn pay-contact-btn">VER DATOS DE CONTACTO</button>

</div>


<script>

(function($){
    $(document).ready(function(){
        $("button.pay-contact-btn").click(function(){
            $("#modal_login_wrapper").css("display", "block");

            ajaxcalls_vars.login_redirect = window.location.href;
        })
        
       

    })
})(jQuery)

</script>