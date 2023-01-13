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

    # receber os dados postados do formulario original
    if( isset($_POST['jogo'])) { $jogo = $_POST['jogo']; }
    
    if( isset($_POST['dia'])){
        $_SESSION['dia'] = $_POST['dia'];
    }
    
    if( isset($_POST['local'])){
        $_SESSION['local'] = $_POST['local'];
    }
    
    if( isset($_POST['horario'])){
        $_SESSION['horario'] = $_POST['horario'];
    }

    # TRATAR O DIA RECEBIDO E TRANSFORMAR EM DATA        
    #$currentYear = $_SESSION['currentYear'];
    #$currentMonth = $_SESSION['currentMonth'];
    #$date = "$currentYear-$currentMonth-$dia";

    #-------------------------------------------------------
    # Recebe definitivamente os dados e realiza o insert
    #-------------------------------------------------------

    if(isset($_POST['btn_list'])) {

        $jogo = $_POST['name'];
        $jogador1 = $user;
        #$dia = $_POST['dia'];
        $dia = $_SESSION['dia'];
        $local = $_SESSION['local'];
        $horario = $_SESSION['horario'];        
        $slots = $_POST['maxPlayers'];
        #$minPlayers = $_POST['minPlayers'];
        $minPlayers = 3;

        # TRATAR O DIA RECEBIDO E TRANSFORMAR EM DATA        
        $currentYear = $_SESSION['currentYear'];
        $currentMonth = $_SESSION['currentMonth'];
        $date = "$currentYear-$currentMonth-$dia";        

        $sql = "INSERT INTO tb_diadejogo (jogo, jogador1, data, local, hora, slots, minPlayers)
        VALUES ( '$jogo', '$jogador1', '$date', '$local', '$horario', '$slots', '$minPlayers')";        
        
        if ($conn->query($sql) === TRUE) {           

            # Fetch the AUTO_INCREMENT value for the latest inserted entry
            $last_id = $conn->insert_id;
            #f_sendMail($conn, "create", $last_id);            
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
<body background=<?php echo wallpaper(); ?>>    

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

                </div>
            </div>
        </form>

        ";
    }
    
    ?>
    
</div>
    
</body>
</html>