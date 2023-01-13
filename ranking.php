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

    if($_SERVER['QUERY_STRING'] == NULL){
        header("Location: ranking.php?coop=0");
    }
    
    # If session cookies exist, retrieve values
    if( isset($_COOKIE['user']) && isset($_COOKIE['mail']) ) {
        $user = $_COOKIE['user'];
        $mail = $_COOKIE['mail'];

    # If not, redirect to logon page
    } else {
        header("Location: logon.php");
    }

    if( isset($_GET['coop'])) {
        # Pode ser 0 (comp) ou 1 (coop)
        $coop = $_GET['coop'];
        if ($coop == 0) {
            $h1 = "RANKING COMPETITIVO";
            $flag = 0;
            $goBack = "index.php";
            $goForth = "ranking.php?coop=1";
        }
        else{            
            $h1 = "RANKING COOPERATIVO";
            $flag = 1;
            $goBack = "ranking.php?coop=0";
            $goForth = "index.php";
        }
    }

    ?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>   
    <meta http-equiv="X-UA-Compatible" content="IE=edge">    
    <title>Ranking - GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <link rel="stylesheet" type="text/css" media="screen" href="css/index.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/ranking.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/persistent-checkboxes.js"></script>
    <script src="js/ranking.js"></script> 
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">    
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Righteous|Russo+One&display=swap" rel="stylesheet">
    <!-- END FONTS -->

</head>

<body class="<?php echo wallpaper();?>">

    <div class="ranking_wrapper">

        <table id="players">

        <tr>            
            <th colspan="9">                
                <h2><a href="<?php echo $goBack ?>"><<<</a>&nbsp;&nbsp;&nbsp;<?php echo $h1 ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo $goForth ?>">>>></a></h2>
            </th>
        </tr>

        <tr>

            <th class="col_title col_jog"><img src="img/friend.png" alt=""><p>JOGADORES</p></th>
            <th class="col_title col_rec" onclick="sortTable(1)"><img src="img/crown.png" alt=""><p>RECORDES</p></th>
            <th class="col_title media_pos" onclick="sortTable(2)"><img src="img/average.png" alt=""><p>MÉDIA POSIÇÕES</p></th>
            <th class="col_title" onclick="sortTable(3)"><img src="img/target.png" alt=""><p>EFICIÊNCIA</p></th>
            <th class="col_title" onclick="sortTable(4)"><img src="img/dice.png" alt=""><p>PARTIDAS</p></th>        
            <th class="col_title" onclick="sortTable(5)"><img src="img/t_pix2.png" alt=""><p>VITÓRIAS</p></th>            
            <th class="col_title horas_jog" onclick="sortTable(6)"><img src="img/time.png" alt=""><p>HORAS JOGADAS</p></th>
            <th class="col_title" onclick="sortTable(7)"><img src="img/danger.png" alt=""><p>DERROTAS</p></th>
            <th class="col_title" onclick="sortTable(8)"><img src="img/run.png" alt=""><p>DESISTÊNCIAS</p></th>
            
        </tr>
            
            <?php listRanking($conn, $flag); ?>
        
        </table>

    </div>

    <?php
        # Terminate Connection
        $conn->close();
    ?>    
    
</body>
</html>