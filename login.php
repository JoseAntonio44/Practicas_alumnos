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
  if(isset($_POST['usuario']) && isset($_POST['contraseña'])) {
  // Obtener los datos del formulario
  $usuario = $_POST['usuario'];
  $contrasena = $_POST['contraseña'];

  // Seleccionar el usuario de la base de datos
  $sql = "SELECT * FROM alumno WHERE email = '$usuario' AND password = '$contrasena'";
  $result = $pdo->query($sql);

  // Comprobar si el usuario existe
  if ($result->rowCount() > 0) {
    // Iniciar sesión
    session_start();
    $_SESSION['usuario'] = $usuario;

    // Redirigir a la página principal
    header('Location: inicio_alumnos.php');
  } else {
    // Mostrar un mensaje de error
    echo 'Usuario o contraseña incorrectos.';
  }
  }
  ?>

</body>

</html>