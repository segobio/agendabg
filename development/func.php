<?php

    # Including necessary files
    include 'db_con.php';

    #------------------------------------------------------------------------------
    # INITIALIZE PHP MAILER
    #------------------------------------------------------------------------------

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require("phpmailer/PHPMailer.php");
    require("phpmailer/SMTP.php");        

    #--------------------------------------------------------------------------
    # GLOBALS / CONSTANTS
    #--------------------------------------------------------------------------

    $maxNrPlayers = 7;

    #--------------------------------------------------------------------------
    # Return number of free slots
    #--------------------------------------------------------------------------

    function getFreeSlots($conn, $eventId) {

        $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventId";
        $query = mysqli_query($conn, $sql);
        $numRegPlayers = count(array_filter(mysqli_fetch_row($query)));        

        $sql = "SELECT slots, minPlayers FROM tb_diadejogo WHERE id_jogo = $eventId";
        $query = mysqli_query($conn, $sql);
        $row = array_filter(mysqli_fetch_row($query));
        $freeSlots = $row[0] - $numRegPlayers;
        return $freeSlots;
    }

    #--------------------------------------------------------------------------
    # Return max slots for the game
    #--------------------------------------------------------------------------

    function getMaxSlots($conn, $eventId) {      

        $sql = "SELECT slots FROM tb_diadejogo WHERE id_jogo = $eventId";
        $query = mysqli_query($conn, $sql);
        $row = array_filter(mysqli_fetch_row($query));
        $maxSlots = $row[0];
        return $maxSlots;
    }
    

    #--------------------------------------------------------------------------
    # Test if current player is not already registered
    #--------------------------------------------------------------------------

    # Return array with registered players
    function valSinglePlayer($conn, $eventId, $currPlayer){
        
        $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventId";
        $query = mysqli_query($conn, $sql);
        $row = array_filter(mysqli_fetch_row($query));        

        # Might copy this function later up until this part (return array with players)

        foreach ($row as &$value) {

            if ($currPlayer == $value) {                
                return false;                
            }            
        }        
        return true;
    }


    #--------------------------------------------------------------------------
    # Return slot into to insert player
    #--------------------------------------------------------------------------

    # Return array with registered players
    function getNextSlot($conn, $eventId){
        
        $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventId";        
        $query = mysqli_query($conn, $sql);
        $count = count(array_filter(mysqli_fetch_row($query)));
        $count = $maxNrPlayers - $count;
        $conn->close();
        return $count;
    }

    #--------------------------------------------------------------------------
    # Print STATUS area depending on number of players
    #--------------------------------------------------------------------------
    
    function f_status($gameDay, $nr_players, $minPlayers){

        $today = date("Y-m-d");
        $gameDay = date("Y-m-d", strtotime($gameDay)); # Converting string -> date in specific format
        #$gameDay = strtotime("$gameDay");
        #$gameDay = date("Y-m-d", $gameDay);
        
        ##############################
        # PAST EVENTS
        ##############################

        if ( $gameDay < $today && $nr_players < $minPlayers) { # We ahead the date of game AND min users did not registered (ABORTED)
            echo "<div style='' class='ev_canc'><p>Cancelado</p></div>";                    
        }

        else if ( $gameDay < $today && $nr_players >= $minPlayers ){ # Date of event is in the past and min users was reached (assumed realized)
            echo "<div style='' class='ev_real'><p>Realizado</p></div>";
        }
        
        ##############################
        # FUTURE EVENTS
        ##############################

        # TODO - cancel 12 hours before event (if min not met)
        else if ( $gameDay >= $today && $nr_players < $minPlayers) { # Time has not yet come but not enough players
            echo "<div style='' class='ev_pend'><p>Planejado</p></div>";
        }
                
        else if ( $gameDay >= $today && $nr_players >= $minPlayers ){ # Date of event has not yet come and min users was reached
            echo "<div style='' class='ev_conf'><p>Confirmado</p></div>";
        }
    }
    
    # Print registered players from "$row"    

    function f_printPlayer($arrayPlayer){
        for ($j=1; $j<8; $j++){
            if ($arrayPlayer[$j] != NULL){                
                    echo "<li>$arrayPlayer[$j]</li>";                  
            }else{
                break;
            }
        }
    }
    
    # Change Wallpaper    

    function wallpaper(){
        $imagem = rand(1,9);
        return "img/".$imagem.".jpg";
    }

    #-----------------------------------------------------------------------------#
    #                                                                             #
    # Main function for Sending Mail                                              #
    #                                                                             #
    #-----------------------------------------------------------------------------#

    
    function f_sendMail($conn, $type, $id)
    {

        $mail = new PHPMailer;
        $mail->IsSMTP(); # enable SMTP
        $mail->SMTPDebug = 0; # debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; # authentication enabled
        $mail->SMTPSecure = 'ssl'; # secure transfer enabled REQUIRED for Gmail
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465; # or 587
        $mail->IsHTML(true);
        $mail->Username = "gamecornerbr@gmail.com";
        $mail->setFrom('gamecornerbr@gmail.com', 'BoardGame Corner');
        $mail->Password = "01argonia10";
        $mail->CharSet = 'UTF-8';
        #$mail->SMTPDebug = 2;
        
        # With the event ID, SELECT in the database 

        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id";
        $query = mysqli_query($conn, $sql);
        $row = array_filter(mysqli_fetch_row($query));
        $date = $row[8];
        $user = $_COOKIE['user'];
        $horario = $row[10];
        $local = $row[9];
        $creator = $row[1];

        $formatedDate = date("d/m/Y", strtotime($date));

        # $link is the same for all types...????

        $link = "<a href ='https://agendabg.000webhostapp.com/cadastro.php?new=0&id=$id&date=$date'>$row[0]</a>";

        # ------------------------------------------------------
        # start the tests to differ between type of email
        # ------------------------------------------------------

        if ($type == "join") {
            $mail_title_html = "<h2>$user aceitou o desafio de $creator! Quem sairá vitorioso?</h2>";
            $subject = "Here comes a new challenger!";
            $img_html = "<br><img style='float:left' src='http://www.superpcenginegrafx.net/img/herecomes.gif'>";
        
        }else if($type == "edit"){
            $mail_title_html = "<h2>O jogador $user modificou um evento!</h2>";
            $subject = "Um evento foi modificado!";
            $img_html = "";
        
        }elseif ($type == "cancel") {
            $mail_title_html = "<h2>O jogador $user cancelou um evento!</h2>";
            $subject = "Um Evento foi cancelado!";
            $link = $row[0];
            $img_html = "";

        }elseif ($type == "create") {
            $mail_title_html = "<h2>O jogador $user criou um evento!</h2>";
            $subject = "Um novo evento foi criado!";
            $img_html = "";
        }

        # ------------------------------------------------------
        # Assign the content to the $mail object
        # ------------------------------------------------------

        $mail->Subject = $subject;        
        $mail->Body = "
        <html>
        <head>
        <style>
        body{width:90%}        
        table {width:600px}
        .container{display:grid; grid-template-columns: 30% 30%; grid-gap: 1em; padding: 1em;}        
        td, th { border: 1px solid #dddddd; text-align: left; padding: 15px; }                     
        </style>
        </head>
        <body>              
            $mail_title_html
            <br>
            <table>
            <tr>
                <th>Jogo</th>
                <td>$link</td>
            </tr>
            <tr>
                <th>Data</th>
                <td>$formatedDate</td>
            </tr>
            <tr>
                <th>Hora</th>
                <td>$horario</td>
            </tr>
            <tr>
                <th>Local</th>
                <td>$local</td>
            </tr>
            </table>        
            $img_html                 
            </div>
        </body>
        </html>";

        $sql = "SELECT mail FROM tb_users";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        while (!empty($row)){
            $mail->AddAddress($row[0]);
            $row = mysqli_fetch_row($query);
        }

        # $mail->AddAddress("segobio@outlook.com");
        #$mail->Send();
        #$conn->close();
    }

?>