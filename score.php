<?php

    #------------------------------------------------------------------------------
    # LOCALICATION AND INCLUDES
    #------------------------------------------------------------------------------

    setlocale(LC_ALL, 'pt_BR');
    date_default_timezone_set('America/Sao_Paulo');
    header('Content-Type: text/html; charset=utf-8');  

    # Including necessary files
    include 'db_con.php';
    include 'func.php';

    #------------------------------------------------------------------------------
    # TEST IF COOKIES EXIST . IF SO, RETRIEVE . IF NOT, FORCE TO LOGIN SCREEN
    #------------------------------------------------------------------------------
    
    if( isset($_COOKIE['user']) && isset($_COOKIE['mail']) ) {
        $user = $_COOKIE['user'];
        $mail = $_COOKIE['mail'];
        #$user_id = $_COOKIE['id'];
    }else{
        header("Location: logon.php");
    }   

    session_start();

    #------------------------------------------------------------------------------
    # CAPTURA O ID DA PARTIDA ESCOLHIDA
    #------------------------------------------------------------------------------
    if( isset($_GET['id'])){
        $_SESSION['eventID'] = $_GET['id'];        
        $users = getUserList($conn, $_SESSION['eventID']);
        $_SESSION['usercount'] = count($users);
    }

    $eventID = $_SESSION['eventID'];

    #---------------------------------------------------------------------------
    # ESCREVENDO O SCORE NO BANCO DE DADOS
    #---------------------------------------------------------------------------
    if(isset($_POST['btn'])){

        # Initializa as varíaveis que guardarão o maior score até o final das interações
        $tmp_record_score = 0;
        $tmp_record_user_id = 0;

        $userCount = $_SESSION['usercount'];
        $currentMonth = $_SESSION['currentMonth'];

        $tmp_index = $userCount + 1;

        for ($i=1; $i < $tmp_index ; $i++) {

            $tmp_user_id = $_SESSION['id_user_'.$i];
            $tmpVitoria = "vitoria_$i";
            $tmpPontos = "pontos_$i";
            $tmpObs = "obs_$i";
            $tmp_coloc = "coloc_$i";
            
            #Inicializando vitoria
            $vitoria = 0;

            #PLAYERS
            if( isset($_POST[$tmpVitoria])) {
                $vitoria = $_POST[$tmpVitoria];
            }    
            
            if( isset($_POST[$tmpPontos])) {
                $pontos = $_POST[$tmpPontos];

            }

            $obs = "";            
            
            if( isset($_POST[$tmp_coloc])) {
                $coloc = $_POST[$tmp_coloc];
            }else{
                $coloc = 0;
            }

            # DURAÇÃO DA PARTIDA
            if( isset($_POST['tempo'])) {
                $tempo = $_POST['tempo'];
            }
            
            if( isset($_POST['compet'])) {
                $compet = $_POST['compet'];                
            }else{
                $compet = 0;
            }

            if ($pontos > $tmp_record_score) {
                $tmp_record_score = $pontos;
                $tmp_record_user_id = $tmp_user_id;
            }
             

            $sql = "INSERT INTO tb_score_player (score_id, user_id, victory, score, observ, is_coop, coloc, time)
                VALUES ('$eventID', '$tmp_user_id', '$vitoria', '$pontos', '$obs', '$compet', '$coloc', '$tempo')";
                        
            if ($conn->query($sql) === TRUE) {               
                # TUDO CERTO COM A OPERAÇÃO NO BANCO    
            } else {
            # TUDO CERTO COM A OPERAÇÃO NO BANCO
            echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        ### FUNCAO QUE ATUALIZA O RECORDE DO JOGO
        f_updateRecord($conn, $eventID, $tmp_record_score, $tmp_record_user_id);
        header("Location: index.php?month=$currentMonth");     
    }
?>

<!DOCTYPE html>
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Score - GameCorner</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>    
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Righteous|Russo+One&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">	
	<link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/score.css">
    <script src="js/score.js"></script> 

</head>

<body class="<?php echo wallpaper();?>">

    <div class="container-contact100">
        <div class="wrap-contact100"> 

        <span class="contact100-form-title">Pontuação</span>

            <form class="contact100-form validate-form" _lpchecked="1" method="post">                
           
                <?php
                
                $field_index = 1;

                # INICIO DO LOOP "FOREACH"
                foreach ($users as &$value) {

                    $tmp_vitoria = "vitoria_".$field_index;
                    $tmp_pontos = "pontos_".$field_index;
                    $tmp_obs = "obs_".$field_index;
                    $tmp_coloc = "coloc_".$field_index;                    

                ?>               

                <div class="wrap-fourth fm-righteous title contact100-subtitle wrap-input200 flex-c-m">
                    <span><?php echo $value ?></span>
                    <?php
                    # Capturar o ID (em var de sessão dinamica) dos usuarios que jogaram pra poder inserir na tabela depois
                    $_SESSION['id_user_'.$field_index] = getUserId($conn, $value);
                    ?>

                </div>

                <div class="wrap-fourth box-rad-shad wrap-input200">
					<span class="label-input100">VENCEU?</span>
					<input class="input100" type="checkbox" name=<?php echo $tmp_vitoria; ?> placeholder="" value="1" pattern="" title="">
                </div>

                <div class="wrap-fourth box-rad-shad wrap-input200 bg1">
					<span class="label-input100">PONTOS</span>
					<input class="input100" type="number" name=<?php echo $tmp_pontos; ?> value="0" placeholder="" pattern="" title="" required>
                </div>

                <div class="wrap-fourth wrap-input200 box-rad-shad">
                    <span class="label-input100">COLOCAÇÃO</span>
                    <select name="<?php echo $tmp_coloc ?>" class="input100 combo_coloc">
                        <option value="" disabled selected></option>;
                        <?php                            
                            for ($i=1; $i <= count($users) ; $i++) {
                                
                                echo "<option value='$i' placeholder=''>$i</option>";
                            }

                        ?>
                    </select>
                </div>
                
                <?php
                    $field_index++;
                    # Fechando o "FOREACH"
                    }
                ?>
                
                <div class="wrap-third box-rad-shad wrap-input200 bg1 m-t-20-i">
					<span class="label-input100">DURAÇÃO</span>
					<input class="input100" type="time" name="tempo" placeholder="" pattern="" title="" required>
                </div>

                <div class="wrap-third box-rad-shad wrap-input200 bg1 m-t-20-i">
					<span class="label-input100">EXPANSÃO</span>
					<input class="input100" type="text" name="" placeholder="" pattern="" title="">
                </div>

                <div class="wrap-third box-rad-shad wrap-input200 m-t-20-i">
					<span class="label-input100">COOPERATIVO?</span>
					<input class="input100 box_coop" type="checkbox" name="compet" placeholder="" value="1" pattern="" title="">
                </div>

				<div class="container-contact100-form-btn m-t-20-i">
					<button class="contact100-form-btn" formaction="score.php" id="btn" name="btn" class="" onclick="location.href='score.php?id=<?php echo $_SESSION['eventID']; ?>'">
						<span>
                            Confirmar                            
						    <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
						</span>
					</button>
                </div>
            </form>

            <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" onclick="history.go(-1);">
					    <span>
                            Voltar
					        <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
	    				</span>
    				</button>				    
                </div>

        </div>
    </div>

    <?php $conn->close(); # TERMINATE MYSQL CONNECTION ?>    
    </body>
</html>