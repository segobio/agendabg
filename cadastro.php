<?php

setlocale(LC_ALL, 'pt_BR');
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/html; charset=utf-8');

# Including necessary files
include 'db_con.php';
include 'func.php';

# TEST IF COOKIES EXIST . IF SO, RETRIEVE VALUES
if (isset($_COOKIE['user']) && isset($_COOKIE['mail'])) {
    $user = $_COOKIE['user'];
    $mail = $_COOKIE['mail'];
} else {
    header("Location: logon.php");
}

session_start();

#------------------------------------------------------------------------------
#  INHERITING THE VALUES FROM SESSION VARIABLES
#------------------------------------------------------------------------------
$date_of_access = $_SESSION['date_of_access'];
$date_of_access[3] = $_SESSION['date_of_access'][0] . "-" . $_SESSION['date_of_access'][1] . "-" . $_SESSION['date_of_access'][2];
#$date_of_access_month = $_SESSION['date_of_access_month'];
#$date_of_access_year = $_SESSION['date_of_access_year'];
#$date_of_access_full = $_SESSION['date_of_access_full'];
#$nr_of_days_in_game_month = $_SESSION["nr_of_days_in_game_month"];

#------------------------------------------------------------------------------
#  FIELD NAMES AND PLACEHOLDERS
#------------------------------------------------------------------------------
$month_name = date("F", mktime(0, 0, 0, $date_of_access[1], 1));
$game_main = "NOME DO JOGO";
$game_ph = "Diga o que quer jogar";
$date_main = "Informe a data da partida";
$date_ph = "";
$date_value = "$date_of_access[0]/$date_of_access[1]";
#$day_main = "DIA";
#$day_ph = "Dia de $month_name";
$hour_main = "HORÁRIO";
$hour_ph = "Início da partida";
$local_main = "LOCAL";
$local_ph = "Local da partida";
$closing_main = "FECHAMENTO DAS INSCRIÇÕES";
$closing_ph = "Padrão 12h";

#------------------------------------------------------------------------------
#  WHAT IS HAPPENING HERE???????
#------------------------------------------------------------------------------

if (isset($_GET['id'])) {
    $id_jogo = $_GET["id"];
}

if (isset($_GET['new'])) {
    $new = $_GET['new'];

    # Se estou editando um evento existente, pego os dados do banco pra popular os campos
    if ($new == 0) {
        $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($query);
    }
}

if (isset($_GET['date']) && $new == 0) { # Index -> Looking at an existing entry
    $date = $_GET['date'];
    $arrayDate = explode('-', $date);
    $date_of_access_month = $arrayDate[1];
    $currDay = $arrayDate[2];
}

if (isset($_GET['date']) && $new == 1) {
    $date = $_GET['date'];
    $arrayDate = explode('/', $date);
    $date_of_access_month = $arrayDate[1];
} else {
    $date = date('Y-m-d');
}

#------------------------------------------------------------------------------
# SELECT FIELDS FROM THE DATABASE REGARDLESS OF THE ACTION
#------------------------------------------------------------------------------

# ????

#------------------------------------------------------------------------------
# I AM DELETING A PRE-EXISTING DATABASE ENTRY
#------------------------------------------------------------------------------

if (isset($_GET['delete']) && $_GET['delete'] == true) {

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
    if (mysqli_query($conn, $sql) === TRUE) {
        //$date_of_access_month = $_SESSION['currentMonth'];
        header("Location: index.php?month=$date_of_access_month");
    }
}

#--------------------------------------------------------------------------------
# I AM UPDATING A PRE-EXISTING DATABASE ENTRY
#--------------------------------------------------------------------------------

if (isset($_POST['btn']) && $new == 0) {

    $sql = "SELECT * FROM tb_diadejogo WHERE id_jogo = $id_jogo";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($query);

    if (isset($_GET['date'])) {
        $date = $_GET['date'];
    }

    $jogo = $_POST['jogo'];

    # Testing if players are NULL
    if (isset($_POST['jogador1'])) {
        $jogador1 = $_POST['jogador1'];
    } else {
        $jogador1 = NULL;
    }
    if (isset($_POST['jogador2'])) {
        $jogador2 = $_POST['jogador2'];
    } else {
        $jogador2 = NULL;
    }
    if (isset($_POST['jogador3'])) {
        $jogador3 = $_POST['jogador3'];
    } else {
        $jogador3 = NULL;
    }
    if (isset($_POST['jogador4'])) {
        $jogador4 = $_POST['jogador4'];
    } else {
        $jogador4 = NULL;
    }
    if (isset($_POST['jogador5'])) {
        $jogador5 = $_POST['jogador5'];
    } else {
        $jogador5 = NULL;
    }
    if (isset($_POST['jogador6'])) {
        $jogador6 = $_POST['jogador6'];
    } else {
        $jogador6 = NULL;
    }
    if (isset($_POST['jogador7'])) {
        $jogador7 = $_POST['jogador7'];
    } else {
        $jogador7 = NULL;
    }

    $day_edited = $_POST['dia'];
    $local = $_POST['local'];
    $horario = $_POST['horario'];
    $slots = $_POST['slots'];
    #$minPlayers = $_POST['minPlayers'];
    $close_hours = $_POST['close_hours'];
    #$date = "$date_of_access_year-$date_of_access_month-$day_edited";

    $sql = "UPDATE tb_diadejogo SET

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
        //$date_of_access_month = $_SESSION['currentMonth'];
        header("Location: index.php?month=$date_of_access_month");
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Criar Partida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/cadastro.js"></script>
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">

</head>

<body class="<?php echo wallpaper(); ?>">

    <div class="container-contact100">
        <div class="wrap-contact100">

            <?php
            ################################################################################
            #  DISPLAYING / CHANGING - EXISTING EVENT
            ################################################################################
            if ($new == 0) {
                #$currentMonth = $_SESSION['currentMonth']; 
            ?>

                <form class="contact100-form validate-form" _lpchecked="1" method="post">

                    <span class="contact100-form-title">Editar Jogo</span>

                    <div class="wrap-input100 validate-input bg1">
                        <span class="label-input100"><?php echo $game_main ?></span>
                        <input class="input100" type="text" name="jogo" placeholder="<?php echo $game_ph ?>" value="<?php echo $row[0]; ?>" required>
                    </div>

                    <div class="wrap-input100 validate-input bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $date_main ?></span>
                        <input class="input100" type="date" name="date" placeholder="" value="<?php echo $row[8]; ?>" required>
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $hour_main ?></span>
                        <input class="input100" type="text" name="horario" placeholder="<?php echo $hour_ph ?>" value="<?php echo $row[10]; ?>" pattern="[0-9]{2}h[0-9]{2}" title="Utilize o formato ex: 13h00" required>
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $local_main ?></span>
                        <input class="input100" type="text" name="local" placeholder="<?php echo $local_ph ?>" value="<?php echo $row[9]; ?>">
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100">JOGADORES</span>
                        <input class="input100" type="text" name="slots" placeholder="Quantos jogam?" value="<?php echo $row[11]; ?>" required>
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $closing_main ?></span>
                        <input class="input100" type="number" min="0" max="72" name="close_hours" placeholder="<?php echo $closing_ph ?>" value="<?php echo $row[13]; ?>">
                    </div>

                    <?php

                    # Tailoring the output so only the next empty user field appears
                    $j = 1;

                    # Max player slots tied to the max allowed by the game
                    for ($i = $row[11]; $i > 0; $i--) {
                        if ($row[$j] != NULL) {
                            echo "<div class='wrap-input100 bg1'>
                                     <span class='label-input100'>JOGADOR $j</span>
                                     <input class='input100' type='text' name='jogador$j' placeholder='Jogador $j' value='$row[$j]'>
                                  </div>";
                            $j++;
                        } else {
                            echo "<div class='wrap-input100 bg1'>
                                    <span class='label-input100'>JOGADOR $j</span>
                                    <input class='input100' type='text' name='jogador$j' placeholder='Jogador $j' value=''>
                                  </div>";
                            break;
                        }
                    }
                    ?>

                    <div class="container-contact100-form-btn">
                        <button class="contact100-form-btn" formaction="cadastro.php?new=0&id=<?php echo $id_jogo ?>&date=<?php echo $row[8]; ?>" id="btn" name="btn" class="">
                            <span>
                                Confirmar
                                <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
                            </span>
                        </button>
                    </div>

                </form>

                <div class="container-contact100-form-btn">
                    <button class="contact100-form-btn" onclick="history.back()">
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

            if ($new == 1) {
                # O que essa linha faz??
                #$currentMonth = $_SESSION['currentMonth'];                
            ?>

                <form class="contact100-form validate-form" _lpchecked="1" method="post">

                    <span class="contact100-form-title">Novo Jogo</span>

                    <div class="wrap-input100 validate-input bg1">
                        <span class="label-input100"><?php echo $game_main ?></span>
                        <input class="input100" type="text" name="jogo" placeholder="<?php echo $game_ph ?>" value="" required>
                    </div>

                    <div class="wrap-input100 validate-input bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $date_main ?></span>
                        <input class="input100" type="date" name="date" placeholder="" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $hour_main ?></span>
                        <input class="input100" type="text" name="horario" placeholder="<?php echo $hour_ph ?>" value="" pattern="[0-9]{2}h[0-9]{2}" title="Utilize o formato ex: 13h00" required>
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $local_main ?></span>
                        <input class="input100" type="text" name="local" placeholder="<?php echo $local_ph ?>" value="">
                    </div>

                    <div class="wrap-input100 bg1 rs1-wrap-input100">
                        <span class="label-input100"><?php echo $closing_main ?></span>
                        <input class="input100" type="number" min="0" max="72" name="close_hours" placeholder="<?php echo $closing_ph ?>" value="">
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
                    <button class="contact100-form-btn" onclick="location.href='index.php?month=<?php echo $date_of_access_month ?>'">
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