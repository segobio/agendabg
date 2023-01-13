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

    $currMonth = $_SESSION['currentMonth'];
    $daysInMonth = $_SESSION["daysInMonth"];  

    if( isset($_GET['id'])){        
        $id_jogo = $_GET["id"];   
    }

    if( isset($_GET['new'])){
        $new = $_GET['new'];
    
        if ( $new == 0) {  
            $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
            $query = mysqli_query($conn, $sql);
            $row = mysqli_fetch_row($query);
        }
    }        

    if( isset($_GET['date']) && $new == 0){ # Index -> Looking at an existing entry
        $date = $_GET['date'];
        $arrayDate = explode('-', $date);
        $currMonth = $arrayDate[1];
        $currDay = $arrayDate[2];
    }

    if( isset($_GET['date']) && $new == 1 ){
        $date = $_GET['date'];        
        $arrayDate = explode('/', $date);
        $currMonth = $arrayDate[1];
    }else{
        $date = date('Y-m-d');
    }

    #------------------------------------------------------------------------------
    # I AM DELETING A PRE-EXISTING DATABASE ENTRY
    #------------------------------------------------------------------------------

    if( isset($_GET['delete']) && $_GET['delete'] == true){

        # Have to save data before deleting (for the notification)

        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        $jogo = $row[0];
        $local = $row[9];
        $horario = $row[10];
        $date = $row[8];

        # It is risky sending the e-mail before deleting...
        f_sendMail($conn, "cancel", $id_jogo);
        $sql = "DELETE FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        if (mysqli_query($conn,$sql) === TRUE) {           
            $currMonth = $_SESSION['currentMonth'];
            header("Location: index.php?month=$currMonth");
        }
    }   

    #--------------------------------------------------------------------------------
    # I AM UPDATING A PRE-EXISTING DATABASE ENTRY
    #--------------------------------------------------------------------------------
    
    if(isset($_POST['btn']) && $new == 0) {

        if( isset($_GET['date'])){
            $date = $_GET['date'];
        }

        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        $jogo = $_POST['jogo'];

        # Testing if players are NULL
        if (isset($_POST['jogador1'])){
            $jogador1 = $_POST['jogador1'];            
        }else{
            $jogador1 = NULL;
        }

        if (isset($_POST['jogador2'])) {
            $jogador2 = $_POST['jogador2'];
        }else{
            $jogador2 = NULL;
        }

        if (isset($_POST['jogador3'])) {
            $jogador3 = $_POST['jogador3'];
        }else {
            $jogador3 = NULL;
        }

        if (isset($_POST['jogador4'])) {
            $jogador4 = $_POST['jogador4'];
        }else {
            $jogador4 = NULL;
        }

        if (isset($_POST['jogador5'])) {
            $jogador5 = $_POST['jogador5'];
        }else {
            $jogador5 = NULL;
        }

        if (isset($_POST['jogador6'])) {
            $jogador6 = $_POST['jogador6'];
        }else {
            $jogador6 = NULL;
        }

        if (isset($_POST['jogador7'])) {
            $jogador7 = $_POST['jogador7'];
        }else {
            $jogador7 = NULL;
        }
       
        $dia = $_POST['dia'];        
        $local = $_POST['local'];
        $horario = $_POST['horario'];        
        $minPlayers = $_POST['minPlayers'];

        $currentYear = $_SESSION['currentYear'];
        $currentMonth = $_SESSION['currentMonth'];
        $date = "$currentYear-$currentMonth-$dia";

        $sql= "UPDATE tb_diadejogo SET

        jogo = '$jogo',
        
        jogador1 = '$jogador1',
        jogador2 = '$jogador2',
        jogador3 = '$jogador3',
        jogador4 = '$jogador4',
        jogador5 = '$jogador5',
        jogador6 = '$jogador6',
        jogador7 = '$jogador7',
        data = '$date',
        local = '$local',        
        hora = '$horario'
        WHERE id_jogo = $id_jogo";

        if ($conn->query($sql) === TRUE) {            
            f_sendMail($conn, "edit", $id_jogo);
            $currMonth = $_SESSION['currentMonth'];
            header("Location: index.php?month=$currMonth");
        }
    }

    #--------------------------------------------------------------------------------
    #------------- I AM CREATING A BRAND NEW DATABASE ENTRY -------------------------
    #--------------------------------------------------------------------------------    

    # ???????????????????????????????

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Novo Evento @ GameCorner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/cadastro.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/cadastro.js"></script>   
</head>
<body>

<div class="cadastro_box">

    <?php
    if ($new == 0){ # EXISTING EVENT!
        $currentMonth = $_SESSION['currentMonth'];
    ?>

    <form class="" method="post">
    
        <div class="cell"><input class="w3-input" type="text" name="jogo" placeholder="Nome do Jogo" value="<?php echo $row[0]; ?>" required></div>
        <div class="cell"><input class="w3-input" type="number" name="dia" placeholder="Dia do Evento" min="1" max="<?php echo $daysInMonth ?>" value="<?php echo $currDay; ?>" required></div>

        <?php  # Tailoring the output so only the next empty user field appears
            $j = 1;
            for ($i=$row[11]; $i>0 ; $i--) { # Max player slots tied to the max allowed by the game
                if ($row[$j] != NULL) {
                    echo "<div class='cell'><input class='w3-input' type='text' name='jogador$j' placeholder='Player $j' value='$row[$j]'></div>";
                    $j++;
                }else{
                    echo "<div class='cell'><input class='w3-input' type='text' name='jogador$j' placeholder='Player $j' value=''></div>";
                    break;
                }
            } ?>
        
        <div class="cell"><input class="w3-input" type="text" name="local" placeholder="Local" value="<?php echo $row[9]; ?>"></div>
        <div class="cell"><input class="w3-input" type="text" name="horario" placeholder="Horario" value="<?php echo $row[10]; ?>" required></div>        
        <button class="w3-button w3-block w3-blue" formaction="cadastro.php?new=0&id=<?php echo $id_jogo ?>&date=<?php echo $date; ?>" id="btn" name="btn" class="">Confirmar</button>        
        <a style ="font-size:15px!important"; class="w3-button w3-block w3-green" href="index.php?month=<?php echo $currentMonth ?>">Voltar</a>
        <br>        
        <a class="delete" href="cadastro.php?delete=true&id=<?php echo $id_jogo ?>"><p>Apagar Registro</p></a>

    </form>

    <?php
    }

    if ($new == 1){ # NEW EVENT!

    $currentMonth = $_SESSION['currentMonth'];
    
    ?>

    <form class="" method="post">
        
        <div class="cell"><input class="w3-input" type="text" name="jogo" placeholder="Nome do Jogo" value="" required></div>
        
        <div class="date_container">
            <div class="cell"><input class="w3-input" type="number" name="dia" placeholder="Dia" min="1" max="<?php echo $daysInMonth ?>" value="" required></div>
            <div class="cell"><input class="w3-input" type="text" name="horario" placeholder="Horario" value="" required></div>            
            <div class="cell"><input class="w3-input" type="text" name="local" placeholder="Local" value=""></div>
        </div>       
                
        <div class="btn_container">
            <!-- Buttons -->
            <button class="w3-button w3-block w3-blue" formaction="bglist.php" id="btn" name="btn" class="">Confirmar</button>
            <a style ="font-size:15px!important"; class="w3-button w3-block w3-green" href="index.php?month=<?php echo $currentMonth ?>" >Voltar</a>
        </div>
    
    <?php
    }
    
    $conn->close(); # TERMINATE MYSQL CONNECTION

    ?>

    </form>
    
</div>
    
</body>
</html>