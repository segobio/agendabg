$( document ).ready(function() {
    
    $(".box_coop").change(function() {
        if(this.checked) {
            $(".combo_coloc").fadeOut();
            //$(".combo_coloc").prop('disabled', 'disabled');
            $(".combo_coloc").prop('value', '0');

        }
        if(!this.checked) {
            $(".combo_coloc").fadeIn();
        }
    });

});