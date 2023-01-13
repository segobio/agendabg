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

    $currMonth = $_SESSION['data_var_mes'];
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
    # SELECT FIELDS FROM THE DATABASE REGARDLESS OF THE ACTION
    #------------------------------------------------------------------------------

    # ????

    #------------------------------------------------------------------------------
    # I AM DELETING A PRE-EXISTING DATABASE ENTRY
    #------------------------------------------------------------------------------

    if( isset($_GET['delete']) && $_GET['delete'] == true){

        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        # Have to save data before deleting (for the notification)

        $jogo = $row[0];
        $local = $row[9];
        $horario = $row[10];
        $date = $row[8];

        # It is risky sending the e-mail before deleting...
        
        #$userList = getUserList($row);
        $userList = getUserList($conn, $id_jogo);       
        f_sendMail($conn, "cancel", $id_jogo, $userList);
        
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

        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);

        if( isset($_GET['date'])){
            $date = $_GET['date'];
        }
        
        $jogo = $_POST['jogo'];

        # Testing if players are NULL
        if (isset($_POST['jogador1'])){ $jogador1 = $_POST['jogador1']; }else{ $jogador1 = NULL; }
        if (isset($_POST['jogador2'])) { $jogador2 = $_POST['jogador2']; }else{ $jogador2 = NULL; }

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
        $slots = $_POST['slots'];
        #$minPlayers = $_POST['minPlayers'];
        $close_hours = $_POST['close_hours'];        

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
        hora = '$horario',
        slots = '$slots',
        close_hours = '$close_hours'
        WHERE id_jogo = $id_jogo";

        if ($conn->query($sql) === TRUE) {
        #--------------------------------------------------------------------------------
        #   # Stopped sending CHANGE notifications - 17/07/2019
        #--------------------------------------------------------------------------------
        #   f_sendMail($conn, "edit", $id_jogo);        
            $currMonth = $_SESSION['currentMonth'];
            header("Location: index.php?month=$currMonth");
        }
    }
?>

<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>+Partida</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/cadastro.js"></script>	
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">	
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">

</head>

    <body class="<?php echo wallpaper();?>">

    <div class="container-contact100">
        <div class="wrap-contact100">

            <?php 
            ################################################################################
            #  DISPLAYING / CHANGING - EXISTING EVENT
            ################################################################################
            if ($new == 0){
                $currentMonth = $_SESSION['currentMonth']; ?>	    

            <form class="contact100-form validate-form" _lpchecked="1" method="post">
			
				<span class="contact100-form-title">Editar Jogo</span>

				<div class="wrap-input100 validate-input bg1">
					<span class="label-input100">NOME DO JOGO</span>
					<input class="input100" type="text" name="jogo" placeholder="Vai de que jogo?" value="<?php echo $row[0]; ?>" required>
				</div>

				<div class="wrap-input100 validate-input bg1 rs1-wrap-input100">
					<span class="label-input100">DIA</span>
					<input class="input100" type="number" name="dia" placeholder="Quando?" min="1" max="<?php echo $daysInMonth ?>" value="<?php echo $currDay; ?>" required>
				</div>

                <div class="wrap-input100 bg1 rs1-wrap-input100">
					<span class="label-input100">HORÁRIO</span>
					<input class="input100" type="text" name="horario" placeholder="Que horas?" value="<?php echo $row[10]; ?>" pattern="[0-9]{2}h[0-9]{2}" title="Utilize o formato ex: 13h00" required>
				</div>

				<div class="wrap-input100 bg1 rs1-wrap-input100">
					<span class="label-input100">LOCAL</span>
					<input class="input100" type="text" name="local" placeholder="Onde?" value="<?php echo $row[9]; ?>">
				</div>

                <div class="wrap-input100 bg1 rs1-wrap-input100">
					<span class="label-input100">JOGADORES</span>
					<input class="input100" type="text" name="slots" placeholder="Quantos jogam?" value="<?php echo $row[11]; ?>" required>
				</div>

                <div class="wrap-input100 bg1 rs1-wrap-input100">
    				<span class="label-input100">LIMITE P/ INSCRIÇÕES</span>
				    <input class="input100" type="number" min="0" max="72" name="close_hours" placeholder="[ 12h padrão ]" value="<?php echo $row[13]; ?>">
				</div>

                <?php

                    # Tailoring the output so only the next empty user field appears
                    $j = 1;

                    # Max player slots tied to the max allowed by the game
                    for ($i=$row[11]; $i>0 ; $i--) {
                        if ($row[$j] != NULL) {
                            echo "<div class='wrap-input100 bg1'>
                                     <span class='label-input100'>JOGADOR $j</span>
                                     <input class='input100' type='text' name='jogador$j' placeholder='Jogador $j' value='$row[$j]'>
                                  </div>";                            
                            $j++;
                        }else{
                            echo "<div class='wrap-input100 bg1'>
                                    <span class='label-input100'>JOGADOR $j</span>
                                    <input class='input100' type='text' name='jogador$j' placeholder='Jogador $j' value=''>
                                  </div>";
                            break;
                        }
                    }                
                ?>				

				<div class="container-contact100-form-btn">
					<button class="contact100-form-btn" formaction="cadastro.php?new=0&id=<?php echo $id_jogo ?>&date=<?php echo $date; ?>" id="btn" name="btn" class="">
						<span>
                            Confirmar                            
						    <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
						</span>
					</button>
                </div>

            </form>

            <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" onclick="location.href='<?php echo 'index.php?year=$currYear&month=$currMonth&nav=0';?>"">
					    <span>
                            Voltar
					        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    				</span>
    				</button>				    
                </div>

                <!-- Opção de cancelar partida aparece somente pro jogagor no slot 1 (provavelmente o criador do evento) -->
                <?php if ($user == $row[1]) { ?>

                <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn btn-delete" onclick="location.href='cadastro.php?delete=true&id=<?php echo $id_jogo ?>'">                    
					    <span>
                            Cancelar Partida
					        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    				</span>
    				</button>				    
                </div>

                <?php } ?>


            <?php
            } # Closing the "new == 0" block

            ################################################################################
            #  CREATING NEW EVENT
            ################################################################################

            if ($new == 1){
                
                # O que essa linha faz??
                #$currentMonth = $_SESSION['currentMonth']; ?>

                <form class="contact100-form validate-form" _lpchecked="1" method="post">

                    <span class="contact100-form-title">Novo Jogo</span>

				    <div class="wrap-input100 validate-input bg1">
    					<span class="label-input100">NOME DO JOGO</span>
					    <input class="input100" type="text" name="jogo" placeholder="Vai de que jogo?" value="" required>
                    </div>

                    <div class="wrap-input100 validate-input bg1 rs1-wrap-input100">
    					<span class="label-input100">DIA</span>
					    <input class="input100" type="number" name="dia" placeholder="Quando?" min="1" max="<?php echo $daysInMonth ?>" value="" required>
				    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
    					<span class="label-input100">HORÁRIO</span>
					    <input class="input100" type="text" name="horario" placeholder="Que horas?" value="" pattern="[0-9]{2}h[0-9]{2}" title="Utilize o formato ex: 13h00" required>
				    </div>

				    <div class="wrap-input100 bg1 rs1-wrap-input100">
    					<span class="label-input100">LOCAL</span>
					    <input class="input100" type="text" name="local" placeholder="Onde?" value="">
				    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
    					<span class="label-input100">LIMITE P/ INSCRIÇÕES</span>
					    <input class="input100" type="number" min="0" max="72" name="close_hours" placeholder="[ 12h padrão ]" value="">
				    </div>
                
                    <div class="container-contact100-form-btn">
                        <button class="contact100-form-btn" formaction="bglist.php" id="btn" name="btn">
						    <span>
                                Confirmar                            
						        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    					</span>
    					</button>
				    </div>

                </form>                
                
                <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" onclick="location.href='index.php?month=<?php echo $currentMonth ?>'">
					    <span>
                            Voltar
					        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    				</span>
    				</button>				    
                </div>
    
            <?php
                } # Closing the "new == 1" block
                $conn->close(); # TERMINATE MYSQL CONNECTION
            ?>

        </div>
    </div>

    </body>
</html>