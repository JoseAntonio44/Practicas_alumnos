<?php
//hacer un formulario que sirva para modificar y añadir alumnos

$nombre = $_POST['nombre'] ?? null;
$host = 'localhost';
$dbname = 'gestion_fct';
$user = 'root';
$pass = '';

$total_registros = 0;
$total_paginas = 0;

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo $e->getMessage();
} catch (PDOException $err) {
  echo "Error: ejecutando consulta SQL.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú de Inicio</title>
  <link rel="stylesheet" href="css/LoginCSS.css">
</head>

<body>

  <div class="container">
    <form action="login.php" method="post">
      <img src="IMG/logo_46c6c06f9916c1ded6b2fc2ff2471eb4_1x.png" alt="Logo">
      <input type="text" name="usuario" placeholder="Usuario">
      <input type="password" name="contraseña" placeholder="Contraseña">
      <input type="submit" value="Iniciar Sesión">
    </form>
  </div>


  <?php
  if (isset($_POST['usuario']) && isset($_POST['contraseña'])) {
    // Obtiene los datos del formulario
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contraseña'];

    $sqlTutor = "SELECT * FROM tutor WHERE email = '$usuario' AND password = '$contrasena'";
    $resultTutor = $pdo->query($sqlTutor);

    // Verifica si el usuario es un alumno o es tutor
    if ($resultTutor->rowCount() > 0) {
      // Es un tutor
      session_start();
      $_SESSION['usuario'] = $usuario;

      // Redirige a la página principal de los profesores
      header('Location: inicio_profesores.php');
      exit();
    } else {
      $sqlAlumno = "SELECT * FROM alumno WHERE email = '$usuario' AND password = '$contrasena'";
      $resultAlumno = $pdo->query($sqlAlumno);

      if ($resultAlumno->rowCount() > 0) {
        // Es un alumno
        session_start();
        $_SESSION['usuario'] = $usuario;

        // Redirige a la página principal de los alumnos
        header('Location: inicio_alumnos.php');
        exit();
      } else {

        echo 'Usuario o contraseña incorrectos.';
      }
    }
  }

  ?>

</body>

</html>