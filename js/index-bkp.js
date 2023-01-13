
// PURE JAVASCRIPT

function SwapDivsWithClick_1() {
    $(".edit").click(function() {
        console.log("DESAPARECE PRINCIPAL");
        
        $(".swapper-first").css('display','grid');
        $(".swapper-other").css('display','none');

        $(this).parent().css('display','none');
        console.log("mostra o proximo pai (reserva)");
        $(this).parent().next().css('display','grid');
    });
}

function traz_div_original() {
    $(".edit").click(function() {
        console.log("DESAPARECE RESERVA");        
        $(this).parent().css('display','none');
        console.log("mostra o pai anterior (principal)");
        $(this).parent().prev().css('display','grid');
    });
}

// JQUERY

$( document ).ready(function() {

    function getTimerGame(){    

       $(".count, .js_event_expiring, .js_ev_closed").each(function(index, value){
           
       var today = new Date();
       today = today.getTime();
       
       var gameDate = new Date($(this).attr('gamedate'));            
       var gameHour = $(this).attr('gamehour');
       var hourParts = gameHour.split('h'); // Parse time before and after the "h"       
       var neGamewDate = gameDate.setHours(hourParts[0], hourParts[1], 00, 000); // Add hours to original date
       var countDownDate = new Date(neGamewDate).getTime();
       //var countDownDate = new Date(date).getTime();
       var now = new Date().getTime();
       var distance = countDownDate - now;            
       
       // Time calculations for days, hours, minutes and seconds
       var days = Math.floor(distance / (1000 * 60 * 60 * 24));
       var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
       var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
       var seconds = Math.floor((distance % (1000 * 60)) / 1000);
       
       //var finished = false;
       
       /*--------------------------------------------
       // CONTROLLING WHAT UNITS TO PRINT
       // v1- decided to hide only depleted units (decreased the font)
       ---------------------------------------------*/
       if(distance > 0 )                
        $(this).text(days + "d " + hours + "h " + minutes + "m " + seconds + "s");

       //if (days > 0 ) $(this).text(days + "d " + hours + "h " + minutes + "m ");       
       //if (days <= 0 && hours > 0) $(this).text(hours + "h " + minutes + "m " + seconds + "s");      
       //if(days <= 0 && hours <= 0 && minutes >= 0 ) $(this).text(minutes + "m " + seconds + "s");       
       //if(distance > 0 && days <= 0 && hours <= 0 && minutes <= 0 ) $(this).text(seconds + "s");           
        //if(distance > 0 )                
        //    $(this).text(days + "d " + hours + "h " + minutes + "m " + seconds + "s");

       /*--------------------------------------------
       // LAST 12 HOURS TO REGISTER - YELLOW
       ---------------------------------------------*/
       
       if ( (days > 0 || hours > 0 || minutes > 0 || seconds > 0) && days < 1 && hours < 12) {

           // Last 12 hours for registration
           $(this).addClass("js_event_expiring");
           $(this).removeClass("count");
       }            
           
       /*---------------------------------------------------
       // "INSCRIÇÕES ENCERRADAS"
       ---------------------------------------------------*/
       
       if ( distance < 0 && distance >= -39600592) {
           // registrations finished
           
           $(this).addClass("js_ev_closed");                
           $(this).removeClass("js_event_expiring");
           $(this).html("<p>Encerrado</p>");                

           console.log($(this).siblings(" .ev_conf ").length);
           // Upper padlock
           if ( $(this).siblings(" .ev_conf ").text() != "Confirmado" || $(this).siblings(" .slot ").text().charAt(0) == 0 ) {
              var beforeLock = $(this).siblings().first().children().last().children().first()[0];
              $(beforeLock).replaceWith("<img style='height: 40px' src='img/pad.png' />");
           }           
           
           // Lower padlock
           $(this).siblings().last().html("<img style='height: 40px' src='img/pad.png' />");
       }  
       
       /*--------------------------------------------
       // AFTER X HOURS FALL BACK TO DEFAULT STYLE
       ---------------------------------------------*/
           
        if (distance < -39600592) {
           
           //console.log("Distance -> " + distance + "| FALLBACK -> " + days + "d " + hours + "h " + minutes + "m " + seconds + "s");
           
           $(this).html("<p>Finalizado</p>");
           $(this).removeClass("js_event_expiring");
           $(this).removeClass("js_ev_closed");
           $(this).addClass("count");

           // Upper padlock
           var beforeLock = $(this).siblings().first().children().last().children().first()[0];
           $(beforeLock).replaceWith("<img style='height: 40px' src='img/pad.png' />");
           // Lower padlock
           // 19/09/19 -> O cadeano não aparece mais embaixo. Em vez disso, apenas removo o botão de edit...
           //$(this).siblings().last().html("<img style='height: 40px' src='img/pad.png' />");
           //$(this).siblings().last().html("<img style='height: 40px' src='img/pad.png' />");
           $(this).siblings().last().children("#edit_icon").remove();
           $(this).siblings().last().children("#pod_icon").show();
           //console.log($(this));
        }                                
        });
    }

    function handleSlots(){

        $(".slot").each(function(index, value){

        var text = $(this).text();
        var slots = text.charAt(0);
        var status = $(this).siblings(".count").text();
        //console.log(status);

        if (slots == 1 && status != "Finalizado"){
               $(this).css({
                   'color' : 'white',
                   'text-shadow' : '1px 1px 1px black',
                   'background' : 'khaki'
               });
           }

           if (slots <= 0 && status != "Finalizado") {
               $(this).css({
                   'color' : 'white',
                   'text-shadow' : '1px 1px 1px black',  
                   'background-color' : 'salmon'    
               });
           }       
        });
    }
    
    /*--------------------------------------------
    // DECIDE TO SHOW IF EVENT IS IN THE PAST
    ---------------------------------------------*/

    $("#toggle_events").change(function() {

        var d = new Date();        
        var curr_day = d.getDate();        

        $(".cell").each(function(index, value){
            cell_day = $(this).children(".day_container").children().first().text();            
            cell_day = cell_day.split(' '); // Parse time before and after the "h"           
            if (cell_day[1] < curr_day) {                
                $(this).toggleClass("hide");               
            }
        });
    });

    if ( $("#toggle_events").is(':checked') )
    {

        var d = new Date();        
        var curr_day = d.getDate();       

        $(".cell").each(function(index, value){
            cell_day = $(this).children(".day_container").children().first().text();            
            cell_day = cell_day.split(' '); // Parse time before and after the "h"             
            if (cell_day[1] < curr_day) {                
                $(this).addClass("hide");
            }
        });
    }

    function handleRank(){ // Change icon and BG color depending on the number of players
         
        $(".slot").each(function(index, value){

            var array_slots = $(this).text().split(' ');
            console.log(array_slots);
            var current_slots = array_slots[0];
            var max_slots = array_slots[2];
            console.log(current_slots + " " + max_slots);
            var result = max_slots - current_slots;
            
            if (result == max_slots) { // LEGENDARY
                
                //$(this).siblings(".table_container").css("background-color", "gold");                
                $(this).siblings(".table_container").children(".game_icon").html('<img src="img/thor.png" alt="Mjölnir">');
                $(this).siblings(".table_container").children(".game_rank").text("Lendário!"); 
                //$(this).siblings(".table_container").children(".game_icon").text("testando");
            }

            if (result == (max_slots-1)) { // EPIC
                
               // $(this).siblings(".table_container").css("background-color", "purple");
                $(this).siblings(".table_container").children(".game_icon").html('<img src="img/helmet.png" alt="Helmet">');
                $(this).siblings(".table_container").children(".game_rank").text("Épico!"); 
                //$(this).siblings(".table_container").children(".game_icon").text("testando");
            }

            if (result == (max_slots-2)) { // RARE
                
                //$(this).siblings(".table_container").css("background-color", "slateblue");
                $(this).siblings(".table_container").children(".game_icon").html('<img src="img/axes.png" alt="Axes">');
                $(this).siblings(".table_container").children(".game_rank").text("Raro!"); 
                //$(this).siblings(".table_container").children(".game_icon").text("testando");
            }

            if (result <= (max_slots-3)) { // COMMON
                
                //$(this).siblings(".table_container").css("background-color", "silver");
                $(this).siblings(".table_container").children(".game_icon").html('<img src="img/ham.png" alt="Axes">');
                $(this).siblings(".table_container").children(".game_rank").text("Comum!"); 
                //$(this).siblings(".table_container").children(".game_icon").text("testando");
            }

            //$(this).siblings(".table_container").addClass("table_container_new");
            //$(this).siblings(".table_container").removeClass("table_container");            

        });
    }

    /*--------------------------------------------
    // CALLING THE FUNCTIONS
    ---------------------------------------------*/
    
    // handleRank();

    getTimerGame(); // Call the 1st time to prevent 1 second delay    
    handleSlots();

    setInterval(function(){ // Call function inside "set interval"...
    getTimerGame();
    }, 1000);
   
});