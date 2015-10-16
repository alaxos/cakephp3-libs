
if (typeof(jQuery) != "undefined") {
    
    $(document).ready(function(){
        
        $(".flash-message-row .flash-message").click(function(e){
            e.preventDefault();
            $(this).hide(200);
        });
        
    });
    
}
