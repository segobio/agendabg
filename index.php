<?php

session_start();

setlocale(LC_ALL, 'pt_BR');
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/html; charset=utf-8');

# Including necessary files
include 'db_con.php';
include 'func.php';

$user_logged = "nobody";
#$mail = "empty";

// write log about user action
# writeLog($_COOKIE['user'], $_SERVER['REQUEST_URI']);

#if (isset($_POST['logoff']) && $_POST['logoff'] == 1) {
if (isset($_GET['logoff']) && $_GET['logoff'] == 1) {
    setcookie("user", "", time() - 3600, '/');
    setcookie("mail", "", time() - 3600, '/');
    setcookie("_ga", "", time() - 3600, '/');
    setcookie("PHPSESSID", "", time() - 3600, '/');
    header("Location: logon.php");
}

# If session cookies exist, retrieve values
#if (isset($_COOKIE['user']) && isset($_COOKIE['mail'])) {
if (isset($_COOKIE['user'])) {
    $user_logged = $_COOKIE['user'];
    #$mail = $_COOKIE['mail'];

    # If not, redirect to logon page
} else {
    header("Location: logon.php");
} ## END SESSION HANDLING 

## -------------------------------------------------------------------------------
## NEW VARIABLES
## -------------------------------------------------------------------------------

$_SESSION["date_of_access"] = explode('-', date("Y-n-d"));
$date_of_access = explode('-', date("Y-n-d")); #ARRAY with the date when the user accesses the platform
$date_of_access[3] = date("Y-n-d");

##---------------------------------------------------------------------------
## BUSCANDO TODOS OS EVENTOS DO BANCO DE DADOS
##---------------------------------------------------------------------------
$sql = "SELECT * FROM tb_diadejogo ORDER BY data";
$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_row($query);
$nr_of_games_total = mysqli_num_rows($query);

##---------------------------------------------------------------------------
## PROCESSANDO DADOS INICIAIS PARA O PRIMEIRO JOGO DA LISTA
##---------------------------------------------------------------------------
$date_of_game_row = explode('-', $row[8]);
$date_of_game_row[3] = $row[8];

# Prevents errors if trying to refference to NULL array position
# ????????????????????????????
if ($row !== NULL) {
    $gameDay = date("j", strtotime("$row[8]"));
    $gameMonth = date("n", strtotime("$row[8]"));
}

#---------------------------------------------------------------------------
# HANDLING THE "QUICKJOIN" EVENT
#---------------------------------------------------------------------------

if (isset($_GET['join']) && $_GET['join'] == 1) { # Player is quick-joining an event 

    $eventID = $_GET['id'];
    $QJ_today = date("Y-m-d");
    $QJ_game_day = date("Y-m-d", strtotime($_GET['date'])); # Converting string -> date in specific format

    $freeSlots = getFreeSlots($conn, $eventID);
    $maxlots = getMaxSlots($conn, $eventID);
    $takenSlots = $maxlots - $freeSlots;
    $nextInsertSlot = $takenSlots + 1;

    $indexJogador = "jogador$nextInsertSlot"; # Mount the field name to add the new player

    if ($freeSlots > 0) { # confirmar se tem espaco livre

        if ($QJ_game_day >= $QJ_today) { # if gameday is yet to come            

            if (valSinglePlayer($conn, $eventID, $user_logged)) { # If current player not yet assigned

                $freeSlots--; # Decrease one as a new user has taken on free seat                    
                $user_loggedList = getUserList($conn, $eventID);

                $sql = "UPDATE tb_diadejogo SET $indexJogador = '$user_logged' WHERE id_jogo = $eventID";

                if ($conn->query($sql) === TRUE) {
                    header("Location: index.php");
                }
            }
        } else {
            ## nada acontece
        }
    } # nao tem lugar livre
}
#---------------------------------------------------------------------------
# HANDLING THE CHAT
#---------------------------------------------------------------------------
if (isset($_POST['btn'])) {

    $eventID = $_POST['id'];
    $ts_date = date("d/m/y");
    $ts_time = date("H:i:s");
    $msg = $_POST['msg'];
    $sql = "INSERT INTO tb_chat (id_jogo, message, ts_date, ts_time, user) VALUES ('$eventID', '$msg', '$ts_date', '$ts_time', '$user_logged')";
    $tmp_month_10 = $_SESSION['currentMonth'];
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Partidas - GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Righteous|Russo+One&display=swap" rel="stylesheet">
    <!-- END FONTS -->
    <link rel="stylesheet" type="text/css" media="screen" href="css/index.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/flip-card.css">
    <!--<link rel="stylesheet" type="text/css" media="screen" href="css/glitch.css">-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/persistent-checkboxes.js"></script>
    <script src="js/index.js"></script>
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">

</head>

<body class="<?php echo wallpaper(); ?>">

    <div class="header_container border__shadow">
        <!--<div class="header_cell upper_user"><div><img src="img/ianzito.jpg"/></div><div id="upper_user"><p>Ianzito</p></div></div> -->
        <div class="header_cell upper_user"><img src="img/ianzito.jpg"/></div>
        <div class="header_cell" id="upper_calen"><a href="cadastro.php?new=1"><img class="icon-small" src="img/calend.png" alt=""></a></div>
        <div class="header_cell" id="upper_togl">
            <!-- Rounded switch -->
            <label class="switch"><input type="checkbox" id="toggle_events"><span class="slider round"></span></label>
        </div>

        <div class="header_cell" id="upper_rank"><a href="ranking.php?coop=0"><img class="icon-small" src="img/podium.png" alt=""></a></div>
        <div class="header_cell" id="upper_set"><a href="#"><img class="icon-small" src="img/settings.png" alt=""></a></div>
        <div class="header_cell" id="upper_log"><a href="index.php?logoff=1"><img class="icon-small" src="img/logout.png" alt=""></a></div>

    </div>

    <div class="container"> <!--- MAIN CCS GRID CONTAINER --->



        <?php

        ##---------------------------------------------------------------------------
        # MAIN LOOP - ITERATE OVER ALL DATABASE GAMES
        ##---------------------------------------------------------------------------

        for ($i = $nr_of_games_total; $i > 0; $i--) {
            ##---------------------------------------------------------------------------
            ## COLLECT INTEL ON THE CURRENT GAME
            ##---------------------------------------------------------------------------

            $id_of_game_row = $row[14];
            $name_of_game_row = $row[0];
            $date_of_game_row = explode('-', $row[8]);
            $date_of_game_row[3] = $row[8];
            $date_of_game_row[4] = (new DateTime($date_of_game_row[3]))->format('d/m/Y'); # Adding weekday name + Brazil date format to the array "$date_of_game_row"
            $date_of_game_row[5] = (new DateTime($date_of_game_row[3]))->format('l'); # Adding weekday name + Brazil date format to the array "$date_of_game_row"
            $place_of_game_row = $row[9]; #Place of the game
            $time_of_game_row = $row[10]; #Time of the game
            $list_players_row = getUserList($conn, $row[14]); #ARRAY with the list of players for current game
            $nr_players_row = (count(array_filter($list_players_row)));
            $nr_slots_max_row = (int)$row[11];
            $nr_slots_row = (int)$row[11] - (int)$nr_players_row;
            $nr_min_players_row = $row[12];
            $img_thumb_row = $row[15];
            $array_scores_row = NULL;
            $hours_limit_row = $row[13];

            ##---------------------------------------------------------------------------
            ## OLD GAME - !!!!!!!! NOT SURE ABOUT EVERYTHING THAT IT DOES
            ##---------------------------------------------------------------------------

            # If the game is older than today AND there are scores logged for it
            if ($date_of_game_row[3] <= $date_of_access[3] && getRankedList($conn, $id_of_game_row) != NULL) {

                $array_scores_row = getRankedList($conn, $row[14]);

                #What I did here?????
                if (!is_array($array_scores_row[0])) {
                    $list_players_row = $array_scores_row;
                }
                $list_players_row = NULL;
                $nr_players_row = (count(array_filter($array_scores_row)));
            }           

        ?>

            <!--------------------------------- EVENT HEADER: GAME DATE, QUICKJOIN ------------------------------->
            <div class="cell swapper-first" id="">
                <div class="day_container">
                    <div class="day_cell">
                        <?php echo "$date_of_game_row[4] - $date_of_game_row[5]"; ?></div>
                    <div class="join">
                        <a href="index.php?join=1&id=<?php echo $id_of_game_row; ?>&date=<?php echo $date_of_access[3]; ?>"><img src="img/add.png" title="Inscrição rápida!"></a>
                    </div>
                </div>

                <!--------------------------------- EVENT HEADER: GAME NAME ------------------------------->
                <div class="title">
                    <p>Jogo</p>
                </div>
                <div class="jogo">
                    <p><?php echo $name_of_game_row; ?></p>
                </div>
                <!--------------------------------- EVENT HEADER: ENROLLED PLAYERS ------------------------------->
                <div class="title">
                    <p>Jogam</p>
                </div>
                <!-- Function f_printPlayer() prints both regular list and ranked list -->
                <div id="Players" class="<?php echo f_getPlayersClass($nr_players_row); ?>"><?php f_printPlayer($conn, $list_players_row, $array_scores_row); ?></div>
                <div class="game_thumb "><img src="<?php echo $img_thumb_row ?>" alt=""></div>
                <!--------------------------------- EVENT HEADER: LOCATION, TIME ------------------------------->
                <div class="title">
                    <p>Onde</p>
                </div>
                <div class="place_hour_container">
                    <div class="place">
                        <p><?php echo $place_of_game_row; ?></p>
                    </div>
                    <div class="hora"><?php echo $time_of_game_row; ?></span></div>
                </div>
                <!--------------------------------- EVENT HEADER: GAME STATUS ------------------------------->
                <?php f_status($date_of_game_row[3], $nr_players_row, $nr_min_players_row); ?>
                <!--------------------------------- EVENT HEADER: SLOTS, CHAT ------------------------------->
                <div class="title">
                    <p>Vagas</p>
                </div>
                <div class="slot" gamedate="">
                    <p><?php echo "$nr_slots_row / $row[11]"; ?></p>
                </div>
                <div class="edit">
                    <a id="chat_icon" href="javascript:void(0)" onclick="SwapDivsWithClick_1();"><img class="clock" src="img/love.png" alt=""></a>
                    <p><?php echo getMuralNrMsg($conn, $row[14]) ?></p>
                </div>
                <!--------------------------------- EVENT HEADER: COUNTDOWN, SCORE, CONFIG ------------------------------->
                <div class="title">
                    <p>Limite</p>
                </div>
                <div class="count" gamehour="<?php echo calcHour($conn, $id_of_game_row, $hours_limit_row); ?>" gamedate="<?php echo $date_of_game_row[3]; ?>"></div>

                <div class="edit">
                    <a id="pod_icon" href="score.php?id=<?php echo $row[14]; ?>"><img src="img/score.png" alt=""></a>
                    <a id="edit_icon" href="cadastro.php?new=0&id=<?php echo $row[14]; ?>&date=<?php echo $date_of_game_row[3]; ?>"><img class="edit_img" src="img/edit.png" alt=""></a>
                </div>
            </div>

            <!--------------------------------- DIV DO CHAT ------------------------------->
            <div class="chat_cell swapper-other" id="" style="display:none">
                <div class="feed">
                    <ul>
                        <a class="chat_ativo" id="chat_icon_2" href="javascript:void(0)" onclick="traz_div_original();">
                            <p>voltar</p>
                        </a>
                        <?php getChat($conn, $row[14]); ?>
                    </ul>
                </div>
                <form action="index.php" method="post">
                    <div class="chat_menu"><input class="" type="text" name="msg">
                        <button class="chat_btn" formaction="<?php echo $url ?>" id="btn" name="btn">Enviar</button>
                    </div>
                </form>
            </div>

        <?php

            $row = mysqli_fetch_row($query); # Points to the next database row (next game event)

        } # Close the main FOR loop

        $conn->close(); # Terminate Connection

        ?>

    </div>
</body>

</html>