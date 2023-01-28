<?php

include 'db_con.php';
include 'func.php';

if (isset($_POST['submit'])) {
  $user = $_POST['username'];
  $mail = $_POST['email'];
  $pass = $_POST['password'];
  $pass_confirm = $_POST['password_confirm'];

  // Check if a file has been uploaded
  if (isset($_FILES['user_pic'])) {

    // Get the file information
    $file = $_FILES['user_pic'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Get the file extension
    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    // Set allowed file extensions
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    // Check if the file extension is allowed
    if (in_array($file_ext, $allowed_ext)) {
      // Check for any errors
      if ($file_error === 0) {
        // Check if the file size is less than 2MB
        if ($file_size <= 3000000) {
          // Create a new file name
          $file_name_new = strtolower($user . '.' . $file_ext);
          // Set the destination path
          $file_destination = 'img/' . $file_name_new;
          // Move the file to the destination path
          move_uploaded_file($file_tmp, $file_destination);
        }
      }
    }
  }

  # Resume the creation of the user
  $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
  $sql = "INSERT INTO tb_users (user, mail, pass, mobile, user_pic) VALUES ( '$user', '$mail', '$hashed_password','', '$file_destination')";
  if ($conn->query($sql) === TRUE) {
    header("Location: logon.php");
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}
?>

<head>
  <title>Novo Usuário</title>
  <!--<link rel="stylesheet" type="text/css" media="screen" href="css/register.css">-->
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="icon" type="image/png" href="https://www.boardgamefinder.net/assets/images/favicon.ico" sizes="32x32">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="<?php echo wallpaper(); ?>">
  <div class="container-contact100">
    <div class="wrap-contact100">
      <form class="contact100-form validate-form" method="post" action="register.php" enctype="multipart/form-data">

        <span class="contact100-form-title">Novo Jogador</span>

        <div class="wrap-input100 validate-input bg1">
          <span class="label-input100">Nome de Usuário</span>
          <input class="input100" type="text" name="username" id="username" placeholder="" value="" required>
        </div>

        <div class="wrap-input100 validate-input bg1">
          <span class="label-input100">E-mail</span>
          <input class="input100" type="email" name="email" id="email" value="" required>
        </div>

        <div class="wrap-input100 validate-input bg1">
          <span class="label-input100">Senha</span>
          <input class="input100" type="password" name="password" id="password" value="" required>
        </div>

        <div class="wrap-input100 validate-input bg1">
          <span class="label-input100">Repita a senha</span>
          <input class="input100" type="password" name="password_confirm" id="password_confirm" value="" required>
        </div>

        <br><br>

        <label for="user_pic"><strong>Forneça uma foto para o perfil (<3mb):</strong></label>
        <input type="file" name="user_pic" id="user_pic" accept="image/*" required>

        <br><br>

        <div class="container-contact100-form-btn">
          <button class="contact100-form-btn" formaction="" id="btn" name="submit">
            <span>Criar<i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
            </span>
          </button>
        </div>
      </form>

      <div class="container-contact100-form-btn">
        <a href="logon.php">
          <button class="contact100-form-btn" formaction="" id="btn" name="btn">
            <span>Voltar</span>
            <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
            </span>
          </button>
        </a>
      </div>

    </div>
  </div>

  <script>
    var password = document.getElementById("password"),
      confirm_password = document.getElementById("password_confirm");

    function validatePassword() {
      if (password.value != confirm_password.value) {
        confirm_password.setCustomValidity("Passwords Don't Match");
      } else {
        confirm_password.setCustomValidity('');
      }
    }
    password.onchange = validatePassword;
    confirm_password.onkeyup = validatePassword;
  </script>

</body>