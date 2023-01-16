<?php

    setlocale(LC_ALL, 'pt_BR');
    date_default_timezone_set('America/Sao_Paulo');
    header('Content-Type: text/html; charset=utf-8');  

    # Including necessary files
    include 'db_con.php';
    include 'func.php';

    # TEST IF COOKIES EXIST . IF SO, RETRIEVE VALUES
    if( isset($_COOKIE['user']) && isset($_COOKIE['mail']) ) {
        $user = $_COOKIE['user'];
        $mail = $_COOKIE['mail'];    
    }else{
        header("Location: logon.php");
    }   

    session_start();

    #------------------------------------------------------------------------------
    #  INHERITING THE VALUES FROM SESSION VARIABLES
    #------------------------------------------------------------------------------
    $date_of_access = $_SESSION['date_of_access'];
    $date_of_access[3] = $_SESSION['date_of_access'][0] . "-" . $_SESSION['date_of_access'][1] . "-" . $_SESSION['date_of_access'][2];
    #$nr_of_days_in_game_month = $_SESSION["nr_of_days_in_game_month"];

    # NEED TO STORE THE FORM FROM CADASTRO IN SESSION VARS AS THIS PAGE NEED TO CALL ITSELF
    if( isset($_POST['jogo'])) {  $jogo = $_POST['jogo']; }    
    if( isset($_POST['date']))  {  $_SESSION['date'] = $_POST['date']; }    
    if( isset($_POST['horario'])){$_SESSION['horario'] = $_POST['horario']; }
    if( isset($_POST['local'])){  $_SESSION['local'] = $_POST['local']; }    
    if( isset($_POST['close_hours'])){ $_SESSION['close_hours'] = $_POST['close_hours']; }
    
    #-------------------------------------------------------
    # THIS INSERT IS ACTIVATED FROM A POST OF THIS VERY PAGE
    #-------------------------------------------------------
    if(isset($_POST['btn_list'])) {

        # ASSINGING THE VALUES THAT CAME FROM CADASTRO.PHP BEFORE
        $jogo = $_POST['name'];
        $jogador1 = $user;        
        $date = $_SESSION['date'];
        $local = $_SESSION['local'];
        $horario = $_SESSION['horario'];        
        
        # ASSIGINING THE VALUES FROM FIELDS ON THIS PAGE (NO NEED TO USE SESSION VARIABLES)
        $slots = $_POST['maxPlayers'];
        $thumb = $_POST['thumb'];
        # Recebo ID pra inserir na tabela JOGO
        $bgg_id = $_POST['bggid'];        
        $minPlayers = 3;
        #$minPlayers = $_POST['minPlayers'];
        $close_hours = $_SESSION['close_hours'];
        if ($close_hours == NULL) {
            $close_hours = 12;
        }

        # TRATAR O DIA RECEBIDO E TRANSFORMAR EM DATA        
        //$currentYear = $_SESSION['date_of_access_yepar'];
        //$currentMonth = $_SESSION['data_var_mes'];
        #$date = "$date_of_access_year-$date_of_access_month-$day_created";

        $sql = "INSERT INTO tb_diadejogo (jogo, jogador1, data, local, hora, slots, minPlayers, close_hours, thumb, id_bgg)
        VALUES ( '$jogo', '$jogador1', '$date', '$local', '$horario', '$slots', '$minPlayers', '$close_hours', '$thumb', '$bgg_id')";        
        
        // ====================================================== 2021

        if ($conn->query($sql) === TRUE) {
            # Fetch the AUTO_INCREMENT value for the latest inserted entry
            $last_id = $conn->insert_id;
            $allUsers = getAllUsers($conn);
            # enviar e-mail
            //f_sendMail($conn, "create", $last_id, $allUsers);
            
            
            # Inserindo na tabela JOGO se jogado pela 1a vez
            f_insereTabelaJogo($conn, $bgg_id, $jogo);

            header("Location: index.php");
        }
        else{
        echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="js/index.js"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/bglist.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
    <title>Lista @GameCorner</title>
</head>

<body class="<?php echo wallpaper();?>">  

<div class="grid_container">    

    <?php

    # listar os jogos retornados da query

    $raw_xml = "https://www.boardgamegeek.com/xmlapi2/search?query=$jogo&type=boardgame";
    $xml = simplexml_load_file($raw_xml) or die("Error: Cannot create object");   
    
    # showing the list of possible games returned by BGG
    $request_limit = 25;

    foreach ($xml->item as $item) {

        if ($request_limit == 0) {
            break;
        }
        
        $request_limit--;
        
        # ID do jogo sendo listado agora pra buscar detalhes sobre ele
        $bgg_id = (string) $item['id'];
        $bgg_year = (string) $item->yearpublished['value'];

        $raw_xml_id = "https://www.boardgamegeek.com/xmlapi2/thing?id=$bgg_id&type=boardgame";
        $xml_id = simplexml_load_file($raw_xml_id) or die("Error: Cannot create object");

        $bgg_name = (string) $xml_id->item->name['value'];
    
        if ($bgg_name == "") {
            continue;
        }

        $maxPlayers = (string) $xml_id->item->maxplayers['value'];        
        $thumb = (string) $xml_id->item->thumbnail;
        if ($thumb == "") {
            $thumb = "img/no-img.jpg";
        }
        
        echo"

        <form method='post'>
            <div class='grid_cell'>
                <div class='bg_thumb'><img src='$thumb'></div>
                <div class='bg_name'>$bgg_name</div>
                <div class='footer_container'>
                    <div class='bg_year'>$bgg_year</div>
                    <button style='width: 100%' class='w3-button w3-blue bg_btn' formaction='bglist.php' id='btn_list' name='btn_list'>OK</button>
                    
                    <!-- hidden fields -->
                    <input type='hidden' name='name' value='$bgg_name'>                                        
                    <input type='hidden' name='year' value='$bgg_year'>
                    <input type='hidden' name='maxPlayers' value='$maxPlayers'>
                    <input type='hidden' name='thumb' value='$thumb'>
                    <input type='hidden' name='bggid' value='$bgg_id'>

                </div>
            </div>
        </form>

        ";
    }
    
    ?>
    
</div>
    
</body>
</html>