<?php
//TODO: Corregir que se quedan varios 'radios' seleccionados.
$num_paginas = intval($_POST['pagina'] ?? 1);
require_once 'validar_sesion.php';

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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/perfil_alumno.css">
  <title>FCT Alumno</title>

</head>

<body>


<?php

$user = $_SESSION['usuario'];

$sql = "SELECT nombre FROM alumno WHERE email='$user'";
$gsent = $pdo->prepare($sql);
$gsent->execute();

$nombreUsu = null;
if ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
  $nombreUsu = $row['nombre'];
}

?>

    <header>
        <p>Bienvenido <?php echo $nombreUsu ?>!</p>
        <form action="inicio_alumnos.php" method="post">
            <input type="submit" id="boton_logout" name="logout" value="Cerrar Sesión">
            <?php
            if (isset($_POST['logout'])) {
              session_destroy();
              header('Location: login.php');
            }
            ?>
        </form>
    </header>
    <img src="IMG/cuadrao.png" alt="cuadrao" class="cuadrao">
  <?php

  //Consulta para comprobar si un alumno tiene una empresa asingada
  $sql_empresa_asignada = "SELECT p.empresa_id FROM practica p WHERE p.alumno_id='$user'";
  $empresa_asignada_query = $pdo->prepare($sql_empresa_asignada);
  $empresa_asignada_query->execute();

  $empresa_asignada = $empresa_asignada_query->fetch();



  //Mostrar datos de la empresa asignada (si existe)
  if ($empresa_asignada = true) {
    //Consulta para obtener los datos de la FCT (adaptar según la estructura de la tabla FCT Alumno)
    $sql_fct = "SELECT a.nombre, a.nia, a.telefono, a.email, e.nombre as nombre_empresa, i.nombre as nombre_instructor, eh.estado_id as estado_FCT
                FROM practica p
                JOIN alumno a ON p.alumno_id=a.email
                JOIN empresa e ON p.empresa_id=e.nombre
                JOIN instructor i ON p.instructor_id=i.nombre
                JOIN estados_historico eh ON eh.practica_id=p.id
                WHERE p.alumno_id='$user'
                GROUP BY p.id";
    $fct_query = $pdo->prepare($sql_fct);
    $fct_query->execute();
    // Mostrar datos de la FCT
    echo "<h2>FCT Alumno</h2>";
    if ($fct_data = $fct_query->fetchAll(PDO::FETCH_ASSOC)) {
      echo "<div class='fct-datos'>";
      foreach ($fct_data as $fct_row) {
        echo "<table>";
        echo "<tr><th>Nombre:</th><td>" . $fct_row['nombre'] . "</td></tr>";
        echo "<tr><th>NIA:</th><td>" . $fct_row['nia'] . "</td></tr>";
        echo "<tr><th>Teléfono:</th><td>" . $fct_row['telefono'] . "</td></tr>";
        echo "<tr><th>Email:</th><td>" . $fct_row['email'] . "</td></tr>";
        echo "<tr><th>Nombre Empresa:</th><td>" . $fct_row['nombre_empresa'] . "</td></tr>";
        echo "<tr><th>Tutor:</th><td>" . $fct_row['nombre_instructor'] . "</td></tr>";
        echo "<tr><th>Estado FCT:</th><td>" . $fct_row['estado_FCT'] . "</td></tr>";
        echo "</table>";
        echo "<br>";
      }
      echo "</div>";
    } else {
      echo "No se encontraron datos de la FCT para este alumno.";
    }
  } else {
    echo "No se ha encontrado empresa asignada para este alumno.";
  }



  //Consulta para obtener los mensajes del alumno (adaptar según la estructura de la tabla Mensajes)
  $sql_mensajes = "SELECT c.fecha, c.comentario, c.hablado_con, c.hablado_por, c.prioridad_id
                  FROM comentario c
                  JOIN prioridades p ON c.prioridad_id=p.id
                  WHERE p.alumno_id='$user'";
  $mensajes_query = $pdo->prepare($sql_mensajes);
  $mensajes_query->execute();

  // Mostrar mensajes del alumno
  echo "<h2>Mensajes</h2>";
  if ($mensajes = $mensajes_query->fetchAll(PDO::FETCH_ASSOC)) {
    echo "<table>";
    echo "<tr><th>Fecha</th><th>Asunto</th><th>Hablado con</th><th>Hablado por</th><th>Prioridad</th></tr>";
    foreach ($mensajes as $mensaje) {
      echo "<tr><td>" . $mensaje['fecha'] . "</td><td>" . $mensaje['comentario'] . "</td><td>" . $mensaje['hablado_con'] . "</td><td>" . $mensaje['hablado_por'] . "</td><td>" . getPrioridadNombre($mensaje['prioridad_id']) . "</td></tr>";
    }
    echo "</table>";
  } else {
    echo "<p>No se encontraron mensajes para este alumno.</p>";
  }

  // Función para obtener el nombre del estado a partir del ID
  function getEstadoNombre($estado_id)
  {
    switch ($estado_id) {
      case 1:
        return "En curso";
      case 2:
        return "Finalizado";
      case 3:
        return "Cancelado";
      default:
        return "Sin asignar";
    }
  }

  // Función para obtener el nombre de la prioridad a partir del ID
  function getPrioridadNombre($prioridad_id)
  {
    switch ($prioridad_id) {
      case 1:
        return "Alta";
      case 2:
        return "Media";
      case 3:
        return "Baja";
      default:
        return "Sin prioridad";
    }
  }

  ?>

</body>

</html>
