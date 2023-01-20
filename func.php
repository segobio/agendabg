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

#--------------------------------------------------------------------------------------------------------
# GLOBALS / CONSTANTS
#--------------------------------------------------------------------------------------------------------

$maxNrPlayers = 7;

#--------------------------------------------------------------------------------------------------------
# Function that returns player picture.... ??????????
#--------------------------------------------------------------------------------------------------------
function f_query($conn, $sql)
{
    $inner_sql = $sql;
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);
    return $row;
}

#--------------------------------------------------------------------------------------------------------
# Function that returns the number of free slots for a game
#--------------------------------------------------------------------------------------------------------
function getFreeSlots($conn, $eventId)
{

    $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);
    $numRegPlayers = count(array_filter(mysqli_fetch_row($query)));

    $sql = "SELECT slots, minPlayers FROM tb_diadejogo WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);
    $row = array_filter(mysqli_fetch_row($query));
    $freeSlots = $row[0] - $numRegPlayers;
    return $freeSlots;
}

# Return chat messages for game
function getChat($conn, $eventId)
{

    $sql = "SELECT message, ts_date, ts_time, user FROM tb_chat WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);

    if ($query) {
        $row = mysqli_fetch_row($query);

        for ($i = 0; $i < 100; $i++) {
            if (!empty($row)) {
                echo "<li><span class='chat_header'>$row[3]&nbsp;<span class='timestamp'>($row[1]-$row[2])</span></span><br><span class='chat_msg'>$row[0]</span></li>";
                $row = mysqli_fetch_row($query);
            } else {
                break;
            }
        }
    }
}

# Return max slots for the game
function getMaxSlots($conn, $eventId)
{

    $sql = "SELECT slots FROM tb_diadejogo WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);
    $row = array_filter(mysqli_fetch_row($query));
    $maxSlots = $row[0];
    return $maxSlots;
}

#--------------------------------------------------------------------------------------------------------
# Function that returns player ID
# Input: Connection object, username
# Output: User ID (int) 
#--------------------------------------------------------------------------------------------------------
function getUserId($conn, $userName)
{
    $sql = "SELECT user_id FROM tb_users WHERE user = '$userName'";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);
    $userId = $row[0];
    return $userId;
}

# RANKING SYSTEM
function listRanking($conn, $flag)
{

    # Pego a lista de usuarios primeiro pra usar depois como argumento
    $sql = "SELECT user_id, user FROM tb_users";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);
    $nr_users = countAllUsers($conn);

    // Uso a função "countAllUsers()" pra saber quantos users existem e assim definir o termino do FOR
    for ($i = 0; $i < $nr_users; $i++) {

        $inner_sql = "SELECT COUNT(tb_score_player.victory) FROM tb_score_player WHERE user_id = $row[0] AND is_coop = $flag";
        $inner_query = mysqli_query($conn, $inner_sql);
        $jogos_nr = mysqli_fetch_row($inner_query);

        # Mostro a linha para o jogador que tem pelo menos um jogo
        if ($jogos_nr[0] > 0) {

            $inner_sql = "SELECT COUNT(tb_score_player.victory)
                              FROM tb_score_player
                              WHERE user_id = $row[0] AND victory = 1 AND is_coop = $flag";

            $inner_query = mysqli_query($conn, $inner_sql);
            $vit_nr = mysqli_fetch_row($inner_query);
            $jogador_name = $row[1];
            $derr = $jogos_nr[0] - $vit_nr[0];
            $relat = $vit_nr[0] / $jogos_nr[0];
            $percent = round((float)$relat * 100);

            #--------------------------------------------------------------------------
            # MEDIA
            #--------------------------------------------------------------------------
            $media_sql = "SELECT coloc FROM tb_score_player WHERE user_id = $row[0] AND is_coop = $flag";
            $media_query = mysqli_query($conn, $media_sql);
            $media_row = mysqli_fetch_row($media_query);
            //$media_row = mysqli_fetch_all($media_query,MYSQLI_ASSOC);
            $media = 0;

            for ($v = 0; $v < $jogos_nr[0]; $v++) {
                $media = $media + $media_row[0];
                $media_row = mysqli_fetch_row($media_query);
            }
            $media = round($media / $jogos_nr[0], 2);

            #--------------------------------------------------------------------------
            # TEMPO
            #--------------------------------------------------------------------------
            $tempo_sql = "SELECT time FROM tb_score_player WHERE user_id = $row[0] AND is_coop = $flag";
            $tempo_query = mysqli_query($conn, $tempo_sql);
            $tempo_row = mysqli_fetch_row($tempo_query);

            $parsed = 0;
            $hour = 00;
            $minute = 00;
            $second = 00;

            for ($y = 0; $y < $jogos_nr[0]; $y++) {

                $parsed = date_parse($tempo_row[0]);
                $hour += $parsed['hour'];
                $minute += $parsed['minute'];
                $tempo_row = mysqli_fetch_row($tempo_query);
            }

            $hour = $hour . 'h';

            if ($hour < 10) {
                $hour = '0' . $hour;
            }

            if ($minute < 10) {
                $minute = '0' . $minute;
            }

            $time = $hour . $minute;

            #--------------------------------------------------------------------------
            # RECORDE
            #--------------------------------------------------------------------------

            // "$row[0]" é o ID do usuário da iteração
            $nome_jogos = "";

            // RECUPERAR O NÚMERO DE VEZES QUE O JOGADOR ATUAL APARECE NA TABELA "TB_JOGO"
            $record_sql = "SELECT COUNT(record_user_id) FROM tb_jogo WHERE record_user_id=$row[0]";

            $record_query = mysqli_query($conn, $record_sql);
            $record_row = mysqli_fetch_row($record_query);

            $rec_index = $record_row[0];

            $record_sql = "SELECT nome_jogo FROM tb_jogo WHERE record_user_id=$row[0]";
            $record_query = mysqli_query($conn, $record_sql);
            $record_row = mysqli_fetch_row($record_query);

            $img = "<img src='img/star.png'>";

            for ($k = 0; $k < $rec_index; $k++) {
                if ($record_row != NULL) {
                    if ($k == 0) {
                        $nome_jogos = "<ul><li class='record_item'>$img$record_row[0]</li>";
                    } else {
                        $nome_jogos = $nome_jogos . "<ul><li class='record_item'>$img$record_row[0]</li>";
                    }
                    $record_row = mysqli_fetch_row($record_query);
                } else {
                    $nome_jogos = $nome_jogos . "</ul>";
                    break;
                }
            }

            echo "<tr>
                        <td class='player'>$jogador_name</td>
                        <td class='stat col_rec'><span class='rec_nr'>$rec_index</span><span class='rec_txt'>$nome_jogos</span></td>
                        <td class='stat media_pos'>$media</td>
                        <td class='stat perc'>$percent</td>
                        <td class='stat'>$jogos_nr[0]</td>
                        <td class='stat'>$vit_nr[0]</td>                        
                        <td class='stat'>$time</td>
                        <td class='stat'>$derr</td>
                        <td class='stat'>0</td>
                        
                      </tr>";
        }
        $row = mysqli_fetch_row($query);
    }
}

# Test if current player is not already registered
# Return array with registered players
function valSinglePlayer($conn, $eventId, $currPlayer)
{

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

# Return slot into to insert player
# Return array with registered players
function getNextSlot($conn, $eventId)
{

    $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);
    $count = count(array_filter(mysqli_fetch_row($query)));
    $count = $maxNrPlayers - $count;
    $conn->close();
    return $count;
}


#--------------------------------------------------------------------------------------------------------
# Function handles the event field "VAGAS" and "LIMITE"
#--------------------------------------------------------------------------------------------------------
function f_status($gameDay, $nr_players, $minPlayers)
{

    $today = date("Y-m-d");
    $gameDay = date("Y-m-d", strtotime($gameDay)); # Converting string -> date in specific format
    #$gameDay = strtotime("$gameDay");
    #$gameDay = date("Y-m-d", $gameDay);

    ##############################
    # PAST EVENTS
    ##############################

    # We ahead the date of game AND min users did not registered (ABORTED)
    if ($gameDay < $today && $nr_players < $minPlayers) {
        echo "<div style='' class='ev_canc'><p>Cancelado</p></div>";
    }

    # Date of event is in the past and min users was reached (assumed realized)
    else if ($gameDay < $today && $nr_players >= $minPlayers) {
        echo "<div style='' class='ev_real'><p>Realizado</p></div>";
    }

    ##############################
    # FUTURE EVENTS
    ##############################

    # Time has not yet come but not enough players
    else if ($gameDay >= $today && $nr_players < $minPlayers) {
        echo "<div style='' class='ev_pend'><p>Planejado</p></div>";
    }

    # Date of event has not yet come and min users was reached
    else if ($gameDay >= $today && $nr_players >= $minPlayers) {
        echo "<div style='' class='ev_conf'><p>Confirmado</p></div>";
        #    echo "<div><img class='ev_conf' src='img/confirmed.png' style='width:125px'></div>";
    }
}

# Change wallpaper
function wallpaper()
{
    $num = rand(1, 6);
    $temp = "body_bg_$num";
    return $temp;
}

# Function to define the new time to close inscription period
function calcHour($conn, $eventId, $orig_hour)
{
    $sql = "SELECT hora, close_hours FROM tb_diadejogo WHERE id_jogo = $eventId";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);
    $temp_hour = explode('h', $row[0]);
    $temp_hour[0] = $temp_hour[0] - $orig_hour;
    $test_hour = "$temp_hour[0]h$temp_hour[1]";
    return $test_hour;
}


#--------------------------------------------------------------------------------------------------------
# RETURN ARRAY OF REGISTERED USERS TO A GIVEN EVENT
#--------------------------------------------------------------------------------------------------------
function getUserList($conn, $eventID)
{

    $sql = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7 FROM tb_diadejogo WHERE id_jogo = $eventID";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    for ($i = 0; $i < 8; $i++) {
        if (!empty($row[$i])) {
            $regUsers[] = $row[$i];
        } else {
            break;
        }
    }
    return $regUsers;
}

# RETURN ARRAY OF PLAYERS FROM EVENT ORDERED BY SCORE
function getRankedList($conn, $eventID)
{

    $sql = "SELECT tb_users.user, tb_score_player.score, tb_score_player.coloc, tb_score_player.victory FROM tb_users, tb_score_player WHERE tb_score_player.score_id = $eventID AND tb_score_player.user_id = tb_users.user_id ORDER BY coloc ASC, victory DESC";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    # Agora eu tenho que receber toda a linha (todo o array) e não apenas uma posição do array
    for ($i = 0; $i < 8; $i++) {
        if (!empty($row)) {
            $regUsers[] = $row;
            $row = mysqli_fetch_row($query);
        } else {
            break;
        }
    }
    if (isset($regUsers)) {
        return $regUsers;
    }
}

/*
function setUserPic($conn, $user)
{
    $sql = "UPDATE tb_users set user_pic "
    $user_pic = f_query($conn, "SELECT user_pic FROM tb_users WHERE user = $user");

    
    
    
    $sql = "SELECT tb_users.user, tb_score_player.score, tb_score_player.coloc, tb_score_player.victory FROM tb_users, tb_score_player WHERE tb_score_player.score_id = $eventID AND tb_score_player.user_id = tb_users.user_id ORDER BY coloc ASC, victory DESC";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    # Agora eu tenho que receber toda a linha (todo o array) e não apenas uma posição do array
    for ($i = 0; $i < 8; $i++) {
        if (!empty($row)) {
            $regUsers[] = $row;
            $row = mysqli_fetch_row($query);
        } else {
            break;
        }
    }
    if (isset($regUsers)) {
        return $regUsers;
    }
}
*/

function f_getPlayersClass($nr_slots_taken){
    
    $class = "players-2";

    if ($nr_slots_taken > 6) {
        $class = "players-8";
    }elseif ($nr_slots_taken > 4) {
        $class = "players-6";
    }elseif ($nr_slots_taken > 2) {
        $class = "players-4";
    }
    
    return $class;
}

##-------------------------------------------------------------------------------------------------------
## LIST THE PLAYERS WHO PLEDGED TO PLAY OR, IF IN THE PAST, THEIR SCORING
##-------------------------------------------------------------------------------------------------------
##
## - This function is called at every loop of the main FOR (1 for every event)
##
##-------------------------------------------------------------------------------------------------------
function f_printPlayer($conn, $row_players, $row_score)
{
    $p = 1;    
    # Se o $row_players não for nulo, o evento é futuro e será listado normalmente                
    if ($row_players != NULL) {
        #$index = count($row_players);

        #for ($j = 0; $j < $index; $j++) {
        foreach($row_players as $value) {            

            if ($value != NULL) { #As long as there is an user left to list
                $loop_pic = f_query($conn, "SELECT user_pic FROM tb_users WHERE user = '$value'");
                echo "<div class='image-container'>
                        <img src='$loop_pic[0]'/>
                        <p class='image-caption'>$value</p>
                      </div>";
                $p++;

            } else {
                break;
            }
        }

    # Se o $row_players for nulo, o evento ja aconteceu e os resultados são listados    
    }
    else
    {

        $index = count($row_score);
        $matrix_index = 0;

        # Se a posição for 0 (zero), o jogo é cooperativo, não tem ranking (APENAS WINNERS E LOSERS)        
        if ($row_score[$matrix_index][1] == 0 & $row_score[$matrix_index][2] == 0) {
            
            #----------------------------------
            # COOPERATIVO
            #----------------------------------
            for ($i = 0; $i < $index; $i++) {

                $user = $row_score[$i][0];
                #$score = $row_score[$i][1];

                if ($row_score[$matrix_index][3] == 1) {
                    ### Primeiro da lista é o vendedor e recebe a classe "winner"
                    echo "<li class='listplayer winner'><img class='h-18' src='img/win.png'><span class='player'>$user</li>";
                } else {
                    ### Jogadores seguintes são listados normalmente
                    echo "<li class='listplayer'><img class='h-18' src='img/p4.png'><span class='player'>$user</li>";
                }
                $matrix_index++;
            }
        } else {

            #----------------------------------
            # COMPETITIVO
            #----------------------------------            
            #$index = count($row_score);
            for ($i = 0; $i < $index; $i++) {

                #echo '<script>document.getElementById("Players").className = "'.$newClass.'";</script>';
                #echo '<script> $("#Players").removeClass().addClass("'.$newClass.'"); </script>';
                echo '<script> $("#Players").removeClass().addClass("players-ranking"); </script>';

                $user = $row_score[$i][0];
                $score = $row_score[$i][1];

                $points = "p";

                if ($i == 0) {
                    ### Primeiro da lista é o vendedor e recebe a classe "winner"
                    echo "<img class='h-18' src='img/p$p.png'><span class='winner player'>$user</span><span>$score$points</span>";
                } else {
                    ### Jogadores seguintes são listados normalmente
                    echo "<img class='h-18' src='img/p$p.png'><span class='player'>$user</span><span>$score$points</span>";
                }
                $p++;
            }
        }
    }
}

# RETURN ARRAY OF ALL USERS EXISTING AT DATABASE    
function getAllUsers($conn)
{
    $sql = "SELECT user FROM tb_users";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    while (!empty($row)) {
        $arrayUsers[] = $row[0];
        $row = mysqli_fetch_row($query);
    }
    return $arrayUsers;
}

# RETURN NUMBER OF USERS EXISTING AT DATABASE    
function countAllUsers($conn)
{
    $sql = "SELECT COUNT(user) FROM tb_users";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);
    return $row[0];
}

#--------------------------------------------------------------------------------------------------------
# VERY FIRST MATCH OF EACH GAME - CREATES AN ENTRY AT THE TABLE "JOGO"
#--------------------------------------------------------------------------------------------------------
function f_insereTabelaJogo($conn, $bgg_id, $jogo)
{

    //Checar se o ID do jogo atual ja existe na tabela
    $sql = "SELECT COUNT(id_jogo) FROM tb_jogo WHERE id_jogo=$bgg_id";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    // Se ainda não existe...
    if ($row != NULL) {
        // ...Insiro no banco
        $sql = "INSERT INTO tb_jogo (id_jogo, nome_jogo) VALUES ( '$bgg_id', '$jogo')";

        if ($conn->query($sql) === TRUE) {
            //inserção funcionou
        } else {
            // Inserção falhou
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

function f_updateRecord($conn, $eventID, $tmp_record_score, $tmp_record_user_id)
{

    # Descobrir qual o ID da BGG baseado no ID do evento
    $sql = "SELECT id_bgg FROM tb_diadejogo WHERE id_jogo=$eventID";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    # Var "$id_bgg" recebe o valor
    $id_bgg = $row[0];

    $sql = "SELECT record_score FROM tb_jogo WHERE id_jogo=$id_bgg";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    #$row = mysqli_fetch_row(mysqli_query($conn, "SELECT record_score FROM tb_jogo WHERE id_jogo=$id_bgg"));        
    if ($tmp_record_score > $row[0]) {
        #recorde foi batido, novo usuario assume

        $sql = "UPDATE tb_jogo
                   SET record_score = '$tmp_record_score', record_user_id = '$tmp_record_user_id'
                   WHERE id_jogo = $id_bgg";

        if ($conn->query($sql) === TRUE) {
            ### DB OPERATION OK!
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

#--------------------------------------------------------------------------------------------------------
# EMAIL NOTIFICATION
#--------------------------------------------------------------------------------------------------------
function f_sendMail($conn, $type, $id, $userList)
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


    # Isolate the month to create link to game overview, not details
    $month = date("n", strtotime($date));

    # ------------------------------------------------------
    # Link's info:
    # - $row[0] is the name of the game
    # ...
    # ------------------------------------------------------        
    $link = "<a href ='https://agendabg.000webhostapp.com/index.php?month=$month'>$row[0]</a>";

    # ------------------------------------------------------
    # start the tests to differ between type of email
    # ------------------------------------------------------

    if ($type == "join") {
        $mail_title_html = "<h2>$user aceitou o desafio de $creator! Quem sairá vitorioso?</h2>";
        $subject = "Here comes a new challenger!";
        $img_html = "<br><img style='float:left' src='http://www.superpcenginegrafx.net/img/herecomes.gif'>";
    } else if ($type == "edit") {
        $mail_title_html = "<h2>O jogador $user modificou um evento!</h2>";
        $subject = "Um evento foi modificado!";
        $img_html = "";
    } elseif ($type == "cancel") {
        $mail_title_html = "<h2>O jogador $user cancelou um evento!</h2>";
        $subject = "Um Evento foi cancelado!";
        $link = $row[0];
        $img_html = "";
    } elseif ($type == "create") {
        $mail_title_html = "<h2>O jogador $user criou um evento!</h2>";
        $subject = "Um novo evento foi criado!";

        # Join button that is a link/image            
        #$img_html = "<br><a href='https://agendabg.000webhostapp.com/index.php?join=1&id=$id&date=$date'><img style='float:left' height='140' width='600' src='https://agendabg.000webhostapp.com/img/join.png'>";
    }

    # Assign the content to the $mail object
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
            <tr>
                <th>Registar</th>
                <td><a href='https://agendabg.000webhostapp.com/index.php?join=1&id=$id&date=$date'>Clique aqui</a></td>
            </tr>
            </table>                           
            </div>
        </body>
        </html>";

    $sql = "SELECT mail FROM tb_users WHERE user IN ('" . implode("','", $userList) . "')";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    while (!empty($row)) {
        $mail->AddAddress($row[0]);
        $row = mysqli_fetch_row($query);
    }
    $mail->Send();
}

function getMuralNrMsg($conn, $eventID)
{

    $sql = "SELECT COUNT(message) FROM tb_chat WHERE id_jogo = $eventID";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    if ($row[0] != NULL) {
        return "($row[0])";
    } else {
        return "(0)";
    }
}

function writeLog($user, $script)
{
    /*
        $hora = date("d/m/y - H:i:s");
        $dia = date("d/m/y");
        $script = explode("/", $script);
        $script = end($script);
        $myfile = fopen("log.txt", "a") or die("Unable to open file!");
        $txt = "[$hora] $user accessed $script\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    */
}

/*
function prev_Month()
{

    if ($_SESSION["currentMonth"] >= 2) {
        $_SESSION["currentMonth"]--;
    } else {
        $_SESSION["currentMonth"] = 12;
        $_SESSION["currentYear"]--;
    }
    $temp_url = "index.php?year=" . $_SESSION["currentYear"] . "&month=" . $_SESSION["currentMonth"] . "&nav=0";
    header("Location: $temp_url");
}

function next_Month()
{

    if ($_SESSION["currentMonth"] <= 11) {
        $_SESSION["currentMonth"]++;
    } else {
        $_SESSION["currentMonth"] = 1;
        $_SESSION["currentYear"]++;
    }
    $temp_url = "index.php?year=" . $_SESSION["currentYear"] . "&month=" . $_SESSION["currentMonth"] . "&nav=0";
    //return $temp_url;
    header("Location: $temp_url");
}
*/
