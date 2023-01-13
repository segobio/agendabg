
function sortTable(var_col) {    
    
    var table, rows, switching, i, x, y, shouldSwitch;        
    table = document.getElementById("players");
    switching = true;
    /*Make a loop that will continue until no switching has been done:*/
    while (switching) {       
        //start by saying: no switching is done:
        switching = false;
        rows = table.rows;
        /*Loop through all table rows (except the first, which contains table headers):*/
        for (i = 2; i < (rows.length - 1); i++) {            
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare, one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[var_col];
            y = rows[i + 1].getElementsByTagName("TD")[var_col];           

            // colunas aqui ordenam do menor pro maior. E.G. "media pos", "derrotas")
            if (var_col == 2 || var_col == 7) {
                //Compara numericamente as os valores da coluna (antes remove tudo que não for numérico)
                if (Number(x.innerHTML.replace(/\D/g,'')) > Number(y.innerHTML.replace(/\D/g,''))) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            } else {
                //Compara numericamente as os valores da coluna (antes remove tudo que não for numérico)
                if (Number(x.innerHTML.replace(/\D/g,'')) < Number(y.innerHTML.replace(/\D/g,''))) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
}

$( document ).ready(function() {

    $(".col_rec").show();
    $(".media_pos").show();   
    
    // Adiciona o sinal % na coluna de "accuracy"
    $(".perc").append("%");

    var linear = "linear-gradient(to right, rgb(15, 32, 39), rgb(32, 58, 67), rgb(44, 83, 100))";
    var linear_2 = "linear-gradient(90deg, #e3ffe7 0%, #d9e7ff 100%)";

    $(".col_title").on("click", function() {
        $(".col_title").css("background", linear);
        $(this).css("background", linear_2);        
    })

    if ( $("h2").text().includes("COOPERATIVO") ){
        $(".col_rec").hide();
        $(".media_pos").hide();
    }
});