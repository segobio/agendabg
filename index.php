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

    if (isset($_POST['logoff']) && $_POST['logoff'] == 1)
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
    define("data_hoje_full", date("Y-n-d"));
    
    define("data_hoje_dia", date("d"));
    define("data_hoje_mes", date("n"));
    define("data_hoje_ano", date("Y"));

    # Date for the featured game
    $_SESSION["data_jogo"] = null;
    $_SESSION["daysInMonth"] = null;

    # Dates which will be used (and updated) for the monthly loop fetching of games
    $_SESSION["data_var_full"] = constant("data_hoje_full");
    $_SESSION["data_var_dia"] = constant("data_hoje_dia");
    $_SESSION["data_var_mes"] = constant("data_hoje_mes");
    $_SESSION["data_var_ano"] = constant("data_hoje_ano");

    $ObjectDate = $_SESSION["data_var_ano"]-$_SESSION["data_var_mes"];

    ##
    ## 1) BLOCO RECEBE O VALOR DA DATA (ANO-MES) ENVIADA PELO HTML DATE PICKER
    ## 2) ATUALIZA AS VARIÁVEIS GLOBAIS DE DATA
    ## ----------------------------------------------------------------------------------------------------------
    if( isset($_POST['selected_date'])){
        $ObjectDate = $_POST['selected_date'];
        $arrayDate = explode('-', $ObjectDate);
        $_SESSION["data_var_ano"] = $arrayDate[0];
        $_SESSION["data_var_mes"] = $arrayDate[1];
    }
    else{
        $ObjectDate = data_hoje_ano."-".data_hoje_mes;
    #    $ObjectDate = "2021-12";#constant("data_hoje_full");
    }


    ## ----------------------------------------------------------------------------------------------------------
    ## SAVING DETAILS ABOUT CURRENT MONTH TO BUILD THE CALENDAR
    ## ----------------------------------------------------------------------------------------------------------    
    $_SESSION["daysInMonth"] = cal_days_in_month(CAL_GREGORIAN, $_SESSION["data_var_mes"], $_SESSION["data_var_ano"]); // 31
    $firstWeekDay = date("l", strtotime("$ObjectDate"));    
    $firstWeekDayNr = date("N", strtotime("$ObjectDate"));    
    $firstMonthDay = date("j", strtotime("$ObjectDate"));    
    $currMonthDay = $firstMonthDay;

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

    $tmp_data_var_mes = $_SESSION["data_var_mes"];
    $tmp_data_var_ano = $_SESSION["data_var_ano"];

    $sql = "SELECT * FROM tb_diadejogo WHERE MONTH(data)=$tmp_data_var_mes AND YEAR(data)=$tmp_data_var_ano ORDER BY data";
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

    $fullCurrentDay = $iterateDay_sec = constant("data_hoje_full");    
    $iterateDay_sec = $currentDay_sec = new DateTime(constant("data_hoje_full"));
    $interval = $iterateDay_sec->diff($currentDay_sec);
    $test_day = $interval->days;

    
    for ( $i = $_SESSION["daysInMonth"]+$gamesThisMonth ; $i > 0 ; $i-- )
    {        
        if ($gameDay == $currMonthDay || $switch == "on")
        {
            # Reseting the switch regardless
            $switch = "off";

            # Formatting the date
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
            
            $weekDay = strftime('%A', strtotime($fullCurrentDay));            
            $slots = $row[11] - $nr_players;            
            $minPlayers = $row[12];
            $thumb = $row[15];
            $today = date("d");
            ?>

            <!-- validar eventos passados / cancelados via PHP???? -->

            <div class="cell swapper-first" id="">
                <div class="day_container">                    
                    <!-- <div class="day_cell"><p><//?php echo utf8_encode("Dia $currMonthDay - $weekDay"); ?><p></div> -->
                    <div class="day_cell"><p><?php echo "$currMonthDay/".$_SESSION["data_var_mes"]."/".$_SESSION["data_var_ano"] . " - $weekDay"; ?></p></div>
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
                <div class="edit"><a id="chat_icon" href="javascript:void(0)" onclick="SwapDivsWithClick_1();"><img class="clock" src="img/love.png" alt=""></a><p>&nbsp;&nbsp;<?php echo getMuralNrMsg($conn, $row[14]) ?></p></div>
                
                <div class="title"><p>Limite</p></div>                
                <div class="count" gamehour="<?php echo calcHour($conn, $row[14], $row[13]); ?>" gamedate="<?php echo $fullCurrentDay; ?>"></div>                
                
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
            $currMonthDay++;
        }else{
            $switch = "on";
        }

        # Pra que serve essa linha? O mesmo valor é recebido??
        /* $fullCurrentDay = "$currYear-$currMonth-$currMonthDay"; */
        
        if ($currMonthDay <=31){
            # 19/SET - Adicionando um objeto date baseado no proximo dia da iteracao pra poder comparar datas
            #$iterateDay_sec = new DateTime("$currYear-$currMonth-$currMonthDay");
            $iterateDay_sec = new DateTime("$fullCurrentDay");
        }
        
        # Não sei o que faz essa entrada
        $weekDay = date("l", strtotime("$fullCurrentDay"));


    } # Close the FOR loop

    # Terminate Connection
    $conn->close();

    ?>   

</div>
    
</body>
</html>