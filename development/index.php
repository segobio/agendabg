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
    $switch = "off";

    session_start(); 
    
    # TEST IF COOKIES EXIST . IF SO, RETRIEVE VALUES
    if( isset($_COOKIE['user']) && isset($_COOKIE['mail']) ) {
        $user = $_COOKIE['user'];
        $mail = $_COOKIE['mail'];   

    }else
    {
        header("Location: logon.php");
    }

    # CURRENT DATE FOR THE CALENDER HANDLING
    $ObjectDate = date("Y")."-".date("n");
    $today = date("Y-m-d");

    # If month variable was sent via URL, use it instead of the curren date (user browsing months)
    if( isset($_GET['month'])){
        $month = $_GET['month'];
        $ObjectDate = date("Y")."-".$month;
    }

    $arrayDate = explode('-', $ObjectDate);
    #$currMonth = $arrayDate[1];
    $_SESSION["currentMonth"] = $currMonth = $arrayDate[1];
    $_SESSION["currentYear"] = $currYear = $arrayDate[0];
    #$_SESSION["currentYear"] = $currMonth;
    
    if($_SERVER['QUERY_STRING'] == NULL){
        header("Location: index.php?month=$currMonth");
    }

    # SAVING DETAILS ABOUT CURRENT MONTH TO BUILD THE CALENDAR
    
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currMonth, $currYear); // 31
    $_SESSION["daysInMonth"] = $daysInMonth;

    $firstWeekDay = date("l", strtotime("$ObjectDate"));    
    $firstWeekDayNr = date("N", strtotime("$ObjectDate"));    
    $firstMonthDay = date("j", strtotime("$ObjectDate"));    
    $currMonthDay = $firstMonthDay;

    #--------------------------------
    # HANDLING THE "QUICKJOIN" EVENT
    #--------------------------------

    if(isset($_GET['join']) && $_GET['join'] == 1) { # Player is quick-joining an event 
        
        $eventID = $_GET['id'];        
        $today = date("Y-m-d");        
        $thisGameDay = date("Y-m-d", strtotime($_GET['date'])); # Converting string -> date in specific format
 
        $freeSlots = getFreeSlots($conn, $eventID);
        $maxlots = getMaxSlots($conn, $eventID);
        $takenSlots = $maxlots - $freeSlots;
        $nextInsertSlot = $takenSlots+1;

        $indexJogador = "jogador$nextInsertSlot"; # Mount the field name to add the new player

        if ( $freeSlots > 0) { # confirmar se tem espaco livre

            if ($thisGameDay >= $today) { # if gameday is yet to come            
               
                if (valSinglePlayer($conn, $eventID, $user)){ # If current player not yet assigned
                    
                    $freeSlots--; # Decrease one as a new user has taken on free seat
                    $sql= "UPDATE tb_diadejogo SET $indexJogador = '$user' WHERE id_jogo = $eventID";
                    if ($conn->query($sql) === TRUE) {
                        f_sendMail($conn, "join", $eventID);
                    }    
                }
                
            }else {
                ## nada acontece
            }
            
        } # nao tem lugar livre
    }
    
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">    
    <title>Jogatinas @GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/index.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/flip-card.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/persistent-checkboxes.js"></script>
    <script src="js/index.js"></script>
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
</head>

<body background=<?php echo wallpaper(); ?>>

<div class="container"> <!--- MAIN CCS GRID CONTAINER --->

    <div class="toggle_container">
        <div class="toggle_cell"><input type="checkbox" id="toggle_events">Apenas eventos futuros</div>
    </div>

    <div class="header_container"> <!--- BLOCK WITH MONTH DETAILS ---->
        <div class="header_cell"><a href="index.php?month=<?php echo $currMonth-1 ?>"><img src="img/left.png" alt="" srcset=""></a></div>        
        <div class="header_cell">
            <h2><?php echo utf8_encode(strftime('%B', strtotime($ObjectDate))).' / '.$currYear; ?></h2>
        </div>       
        <div class="header_cell"><a href="index.php?month=<?php echo $currMonth+1 ?>"><img src="img/right.png" alt="" srcset=""></a></div>
    </div>

    <?php
    $sql = "SELECT * FROM tb_diadejogo WHERE MONTH(data)=$currMonth ORDER BY data";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);    
    
    $gamesThisMonth = mysqli_num_rows($query); # Keep the number of events and ADD to the number of days in month in the FOR loop
    $gameDay = "noMoreGamesThisMonth";
    $today = date("d");

    
    if ($row !== NULL){ # Prevents errors if trying to refference to NULL array position
        $gameDay = date("j", strtotime("$row[8]"));
        $gameMonth = date("n", strtotime("$row[8]"));
    }

    $fullCurrentDay = "$currYear-$currMonth-$currMonthDay";   
    $weekDay = date("l", strtotime("$fullCurrentDay")); # Initializing $weekDay for the first time before the loop    
    
    for ( $i = $daysInMonth+$gamesThisMonth ; $i > 0 ; $i-- )
    {        
        if ($gameDay == $currMonthDay || $switch == "on")
        {
            $switch = "off"; # Reseting the switch regardless
            $row[8] = date("d", strtotime($row[8])); # Formatting the date            

            $sql_players = "SELECT jogador1, jogador2, jogador3, jogador4, jogador5, jogador6, jogador7, minPlayers FROM tb_diadejogo WHERE id_jogo = $row[13]";
            $query_players = mysqli_query($conn, $sql_players);
            $row_players = mysqli_fetch_row($query_players);            
            
            $nr_players = (count(array_filter($row_players))-1); # Removing one as I am also fectching minPlayers from the table
            $slots = $row[11] - $nr_players;
            $minPlayers = $row[12];
            $today = date("d");
            $weekDay = strftime('%A', strtotime($fullCurrentDay));            

            ?>

            <div class="cell">
                <div class="day_container">                    
                    <div class="day_cell"><p><?php echo utf8_encode("Dia $currMonthDay - $weekDay"); ?><p></div>                    
                    <div class="join" style="background-color:white">
                        <a href="index.php?join=1&id=<?php echo $row[13]; ?>&date=<?php echo $fullCurrentDay; ?>">
                        <img src="img/add.png" alt="">
                        </a>
                    </div>
                </div>
                
                <!--- Jogo spams 2 colunms --->
                <div class="title"><p>Jogo</p></div>
                <div class="jogo"><p><?php echo $row[0]; ?></p></div>
                <div class="title"><p>Jogam</p></div>
                <div class="players"><ul><?php f_printPlayer($row); ?></ul></div>
                <div class="table_container">
                    <div class="table_cell game_icon"></div>
                    <div class="table_cell game_rank"></div>
                </div>
                <div class="title"><p>Onde</p></div>
                <div class="place"><p><?php echo $row[9]; ?></p></div>                
                <div class="hora"><p><?php echo $row[10]; ?></p></div>
                <div class="title"><p>Vagas</p></div>
                <div class="slot" gamedate="<?php echo $fullCurrentDay; ?>"><p><?php echo "$slots / $row[11] ( min $minPlayers )"; ?></p></div>                    
                <?php f_status($fullCurrentDay, $nr_players, $minPlayers); ?>
                <div class="title"><p>Limite</p></div>                
                <div class="count" gamehour="<?php echo $row[10]; ?>" gamedate="<?php echo $fullCurrentDay; ?>"></div>     
                      
                <div class="edit"><a href="cadastro.php?new=0&id=<?php echo $row[13]; ?>&date=<?php echo $fullCurrentDay; ?>"><p>+Info</p></a></div>                              
            </div>

            <?php
            #$row = mysqli_fetch_row($query);

        //}

        $lastGameDay = $gameDay;
        $row = mysqli_fetch_row($query);


        } //------------- CLOSE ELSE ------------------------------------------------------------

        # This block ALWAYS execute
        
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
        $fullCurrentDay = "$currYear-$currMonth-$currMonthDay";
        $weekDay = date("l", strtotime("$fullCurrentDay"));


    } # Close the FOR loop

    # Terminate Connection
    $conn->close();

    ?>

            <div class="newevent">
            <a href="cadastro.php?new=1"><img class="newevent" src="img/new.png" alt="" srcset=""></a>
            </div>      

</div>
    
</body>
</html>