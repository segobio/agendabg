<?php

    setlocale(LC_ALL, 'pt_BR');
    date_default_timezone_set('America/Sao_Paulo');    
    header('Content-Type: text/html; charset=utf-8');

    # Including necessary files
    include 'db_con.php';
    include 'func.php';     
    
    $user = "nobody";
    $mail = "empty";
    $lastGameDay = "";    

    ## what does the switch do?
    $switch = "off";    

    session_start();

    // write log about user action
    writeLog($_COOKIE['user'], $_SERVER['REQUEST_URI']);

    if (isset($_GET['logoff']) && $_GET['logoff'] == 1)
    {
        setcookie("user", "", time()-3600, '/');
        setcookie("mail", "", time()-3600, '/');
        setcookie("_ga", "", time()-3600, '/');
        setcookie("PHPSESSID", "", time()-3600, '/');
        header("Location: logon.php");
    }
    
    # If session cookies exist, retrieve values
    if( isset($_COOKIE['user']) && isset($_COOKIE['mail']) ) {
        $user = $_COOKIE['user'];
        $mail = $_COOKIE['mail'];

    # If not, redirect to logon page
    } else {
        header("Location: logon.php");
    } ## END SESSION HANDLING
    


    ## -------------------------------------------------------------------------------
    ## CONSTANTES PARA HOJE (DIA DO ACESSO DO JOGADOR)
    ## -------------------------------------------------------------------------------
    define("date_of_access_full", date("Y-n-d"));    
    define("date_of_access_day", date("d"));
    define("date_of_access_month", date("n"));
    define("date_of_access_year", date("Y"));

    ## -------------------------------------------------------------------------------
    ## GLOBAL / SESSION VARIABLES
    ## -------------------------------------------------------------------------------
    # Date for the featured game
    $_SESSION["data_jogo"] = null;
    $_SESSION["nr_of_days_in_game_month"] = null;
    # Dates which will be used (and updated) for the monthly loop fetching of games
    $_SESSION["date_of_access_full"] = constant("date_of_access_full");
    $_SESSION["date_of_access_day"] = constant("date_of_access_day");
    $_SESSION["date_of_access_month"] = constant("date_of_access_month");
    $_SESSION["date_of_access_year"] = constant("date_of_access_year");
    
    # Session variables keep the date selected in the html picker
    $_SESSION["date_of_selection_year"] = null;
    $_SESSION["date_of_selection_month"] = null;
    $_SESSION["date_of_selection_day"] = null;
    $_SESSION["date_of_selection_full"] = null;

    $ObjectDate = $_SESSION['date_of_access_year']."-".$_SESSION['date_of_access_month'];

    ## ----------------------------------------------------------------------------------------------------------
    ## SAVE DATE POSTED BY THE HTML DATE PICKER
    ## ----------------------------------------------------------------------------------------------------------
    if( isset($_POST['selected_date'])){
        $ObjectDate = $_POST['selected_date'];
        $arrayDate = explode('-', $ObjectDate);
        $_SESSION["date_of_selection_year"] = $arrayDate[0];
        $_SESSION["date_of_selection_month"] = $arrayDate[1];
        $_SESSION["nr_of_days_in_game_month"] = cal_days_in_month(CAL_GREGORIAN, $arrayDate[1], $arrayDate[0]); // 31
    }
    else{
        //$ObjectDate = date_of_access_year."-".date_of_access_month;
        #    $ObjectDate = "2021-12";#constant("data_hoje_full");
        $_SESSION["nr_of_days_in_game_month"] = cal_days_in_month(CAL_GREGORIAN, $_SESSION["date_of_access_month"], $_SESSION["date_of_access_year"]);
    }


    ## ----------------------------------------------------------------------------------------------------------
    ## SAVING DETAILS ABOUT CURRENT MONTH TO BUILD THE CALENDAR
    ## ----------------------------------------------------------------------------------------------------------    
    $_SESSION["nr_of_days_in_game_month"] = cal_days_in_month(CAL_GREGORIAN, $arrayDate[1], $arrayDate[0]); // 31
    $firstWeekDay = date("l", strtotime("$ObjectDate"));    
    $firstWeekDayNr = date("N", strtotime("$ObjectDate"));    
    $firstMonthDay = date("j", strtotime("$ObjectDate"));    
    $day_of_month_loop = $firstMonthDay;
    $full_gameday_loop = "$ObjectDate-$day_of_month_loop";

    #---------------------------------------------------------------------------
    # HANDLING THE "QUICKJOIN" EVENT
    #---------------------------------------------------------------------------

    if(isset($_POST['join']) && $_POST['join'] == 1) { # Player is quick-joining an event 
        
        $eventID = $_POST['id'];        
        $today = date("Y-m-d");        
        $thisGameDay = date("Y-m-d", strtotime($_POST['date'])); # Converting string -> date in specific format
 
        $freeSlots = getFreeSlots($conn, $eventID);
        $maxlots = getMaxSlots($conn, $eventID);
        $takenSlots = $maxlots - $freeSlots;
        $nextInsertSlot = $takenSlots+1;

        $indexJogador = "jogador$nextInsertSlot"; # Mount the field name to add the new player

        if ( $freeSlots > 0) { # confirmar se tem espaco livre

            if ($thisGameDay >= $today) { # if gameday is yet to come            
               
                if (valSinglePlayer($conn, $eventID, $user)){ # If current player not yet assigned
                    
                    $freeSlots--; # Decrease one as a new user has taken on free seat                    
                    $userList = getUserList($conn, $eventID);

                    $sql= "UPDATE tb_diadejogo SET $indexJogador = '$user' WHERE id_jogo = $eventID";

                    if ($conn->query($sql) === TRUE) {
                        header("Location: index.php");
                    }    
                }
                
            }else {
                ## nada acontece
            }
            
        } # nao tem lugar livre
    }

    #---------------------------------------------------------------------------
    # HANDLING THE CHAT
    #---------------------------------------------------------------------------

    if(isset($_POST['btn'])){

        $eventID = $_POST['id'];
        $ts_date = date("d/m/y");
        $ts_time = date("H:i:s");
        $msg = $_POST['msg'];
        $sql = "INSERT INTO tb_chat (id_jogo, message, ts_date, ts_time, user) VALUES ('$eventID', '$msg', '$ts_date', '$ts_time', '$user')";
        $tmp_month_10 = $_SESSION['currentMonth'];
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php");
        }
        else{
        echo "Error: " . $sql . "<br>" . $conn->error;
        }        
    }    
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">    
    <title>Partidas - GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Righteous|Russo+One&display=swap" rel="stylesheet">
    <!-- END FONTS -->
    <link rel="stylesheet" type="text/css" media="screen" href="css/index.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/flip-card.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/glitch.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/persistent-checkboxes.js"></script>
    <script src="js/index.js"></script>
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">

</head>

<body class="<?php echo wallpaper();?>">

<div class="container"> <!--- MAIN CCS GRID CONTAINER --->

    <!--------------------------------------------------------------------
    BLOCK WITH [ MONTH / YEAR ] DETAILS
    --------------------------------------------------------------------->
    
    <div class="header_container border__shadow">
        <div class="header_cell" id="upper_calen"> <a href="cadastro.php?new=1"><img class="icon-small" src="img/calend.png" alt=""></a> </div>
        <div class="header_cell" id="upper_togl">
                <!-- Rounded switch -->
                <label class="switch">
                <input type="checkbox" id="toggle_events">
                <span class="slider round"></span>
            </label>
        </div>

        <div class="header_cell" id="upper_ym">
            <form name="input_date" method="POST">
                <input type="month" id="start" min="" name="selected_date" value="<?php data_hoje_ano."-".data_hoje_mes?>">
                <input type="submit" value="Enviar"/>
            </form>
        </div>

        <div class="header_cell" id="upper_rank"><a href="ranking.php?coop=0"><img class="icon-small" src="img/podium.png" alt=""></a></div>
        <div class="header_cell" id="upper_set"><a href="#"><img class="icon-small" src="img/settings.png" alt=""></a></div>
        <div class="header_cell" id="upper_log"><a href="index.php?logoff=1"><img class="icon-small" src="img/logout.png" alt=""></a></div>

    </div>

    <?php

    ##---------------------------------------------------------------------------
    ## SELECIONANDO DO BANCO DE DADOS TODOS OS EVENTOS DO MES ATUAL
    ##---------------------------------------------------------------------------

    $tmp_date_of_access_month = $_SESSION["date_of_access_month"];
    $tmp_date_of_access_year = $_SESSION["date_of_access_year"];

    $sql = "SELECT * FROM tb_diadejogo WHERE MONTH(data)=$tmp_date_of_access_month AND YEAR(data)=$tmp_date_of_access_year ORDER BY data";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);    
    
    # Keep the number of events and ADD to the number of days in month in the FOR loop
    $gamesThisMonth = mysqli_num_rows($query);

    $gameDay = "noMoreGamesThisMonth";
    $today = date("d");

    # Prevents errors if trying to refference to NULL array position
    if ($row !== NULL){ 
        $gameDay = date("j", strtotime("$row[8]"));
        $gameMonth = date("n", strtotime("$row[8]"));
    }

    ##---------------------------------------------------------------------------
    ## $iterateDay_sec RECEBE O DIA COMPLETO D-M-y PRA ITERAR NO MES
    ##---------------------------------------------------------------------------

    $fullCurrentDay = $iterateDay_sec = constant("date_of_access_full");    
    $iterateDay_sec = $currentDay_sec = new DateTime(constant("date_of_access_full"));
    $interval = $iterateDay_sec->diff($currentDay_sec);
    $test_day = $interval->days;

    
    for ( $i = $_SESSION["nr_of_days_in_game_month"]+$gamesThisMonth ; $i > 0 ; $i-- )
    {        
        if ($gameDay == $day_of_month_loop || $switch == "on")
        {
            $full_gameday_loop = "$ObjectDate-$day_of_month_loop";
            # Reseting the switch regardless
            $switch = "off";

            # Inserts the current day in the loop as a gameday into the DB array
            $row[8] = date("d", strtotime($row[8]));            
            
            # Evento já aconteceu, mostrar a pountuação e medalhas
            if ($iterateDay_sec <= $currentDay_sec && getRankedList($conn, $row[14]) != NULL) {                
                $row_score = getRankedList($conn, $row[14]);

                if (!is_array($row_score[0])) {
                    $row_players = $row_score;
                }

                $row_players = NULL;
                $nr_players = (count(array_filter($row_score)));
                # Evento futuro, listar apenas os jogadores
            } else {                
                $row_players = getUserList($conn, $row[14]);
                $row_score = NULL;
                $nr_players = (count(array_filter($row_players)));
            }
            
            //$weekDay = strftime('%A', strtotime($fullCurrentDay));            
            $slots = $row[11] - $nr_players;            
            $minPlayers = $row[12];
            $thumb = $row[15];
            $today = date("d");
            ?>

            <!-- validar eventos passados / cancelados via PHP???? -->

            <div class="cell swapper-first" id="">
                <div class="day_container">                    
                    <!-- <div class="day_cell"><p><//?php echo utf8_encode("Dia $day_of_month_loop - $weekDay"); ?><p></div> -->
                    <div class="day_cell"><p><?php echo "$day_of_month_loop/".$_SESSION["date_of_access_month"]."/".$_SESSION["date_of_access_year"] . " - $weekDay"; ?></p></div>
                    <div class="join">
                        <a href="index.php?join=1&id=<?php echo $row[14]; ?>&date=<?php echo $fullCurrentDay; ?>">
                        <img src="img/add.png" title="Clique aqui pra uma inscrição rápida!">
                        </a>
                    </div>
                </div>
                
                <!--- Jogo spams 2 colunms --->
                <div class="title"><p>Jogo</p></div>
                <div class="jogo"><p><?php echo $row[0]; ?></p></div>
                <div class="title"><p>Jogam</p></div>

                <!-- Function f_printPlayer() prints both regular list and ranked list -->
                <div class="players"><?php f_printPlayer($row_players, $row_score); ?></div>

                <div class="icon">
                    <img src="<?php echo $thumb ?>" alt="">
                </div>

                <div class="title"><p>Onde</p></div>

                <div class="place_hour_container">
                    <div class="place"><p><?php echo $row[9]; ?></p></div>
                    <div class="hora"><?php echo $row[10]; ?></span></div>
                </div>

                <?php f_status($fullCurrentDay, $nr_players, $minPlayers); ?>
                <div class="title"><p>Vagas</p></div>                
                <div class="slot" gamedate=""><p><?php echo "$slots / $row[11]"; ?></p></div>
                <div class="edit">
                    <a id="chat_icon" href="javascript:void(0)" onclick="SwapDivsWithClick_1();">
                    <img class="clock" src="img/love.png" alt=""></a>
                    <p><?php echo getMuralNrMsg($conn, $row[14]) ?></p>
                </div>
                
                <div class="title"><p>Limite</p></div>                
                <div class="count" gamehour="<?php echo calcHour($conn, $row[14], $row[13]); ?>" gamedate="<?php echo $full_gameday_loop; ?>"></div>

                <div class="edit">
                    <a id="pod_icon" href="score.php?id=<?php echo $row[14]; ?>"><img src="img/podium.png" alt=""></a>
                    <a id="edit_icon" href="cadastro.php?new=0&id=<?php echo $row[14]; ?>&date=<?php echo $fullCurrentDay; ?>"><img class="edit_img" src="img/edit.png" alt=""></a>
                </div>
            </div>

            <!--------------------------------- DIV DO CHAT ------------------------------->

            <div class="chat_cell swapper-other" id="" style="display:none">
                <div class="feed">
                    <ul>
                        <a class="chat_ativo" id="chat_icon_2" href="javascript:void(0)" onclick="traz_div_original();"><p>voltar</p></a>
                        <?php getChat($conn, $row[14]); ?>
                    </ul>
                </div>
                <form action="index.php" method="post">
                    <div class="chat_menu"><input class="" type="text" name="msg">                    
                        <button class="chat_btn" formaction="<?php echo $url ?>" id="btn" name="btn" >Enviar</button>
                    </div>                    
                </form>
            </div>

            <?php
            #$row = mysqli_fetch_row($query);

        //}

        $lastGameDay = $gameDay;
        $row = mysqli_fetch_row($query);


        } // CLOSE ELSE

        #---------------------------------------------------------------------------
        # ESSE BLOCO SEMPRE EXECUTA
        #---------------------------------------------------------------------------
        
        if ($row[8] != NULL) {
            $gameDay = date("j", strtotime("$row[8]"));
        }else{
            $gameDay = "noMoreGamesThisMonth";
        }


        if ($lastGameDay != $gameDay) {
            $day_of_month_loop++;
        }else{
            $switch = "on";
        }

        # Pra que serve essa linha? O mesmo valor é recebido??
        /* $fullCurrentDay = "$currYear-$currMonth-$day_of_month_loop"; */
        
        if ($day_of_month_loop <=31){
            # 19/SET - Adicionando um objeto date baseado no proximo dia da iteracao pra poder comparar datas
            #$iterateDay_sec = new DateTime("$currYear-$currMonth-$day_of_month_loop");
            $iterateDay_sec = new DateTime("$fullCurrentDay");
        }
        
        # Não sei o que faz essa entrada
            # Parece atualizar o dia da semana para o dia atual do loop
        //$weekDay = date("l", strtotime("$fullCurrentDay"));
        $weekDay = date("l", strtotime("2023-1-$day_of_month_loop"));


    } # Close the FOR loop

    # Terminate Connection
    $conn->close();

    ?>   

</div>
    
</body>
</html>