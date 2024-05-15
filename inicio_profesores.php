<?php
$nombre_B = $_POST['nombre_B'] ?? null;
$num_paginas = intval($_POST['pagina'] ?? 1);

?>
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
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/inicio_profesoresCSS.css">
  <title>Alumnos</title>
</head>

<body>
  <form action="inicio_profesores.php" method="post">
    <label for="nombre">NOMBRE: </label>
    <input type="text" name="nombre_B" id="nombre" value="<?php echo $nombre_B ?>">
    <input type="submit" value="Buscar">
    <input type="reset" value="Reset">
  </form>

  <?php
  // Calculos Paginacion
  try {
    //Total registros
    $total_registros_query = $pdo->query("SELECT count(*) FROM alumno");
    $total_registros = $total_registros_query->fetchColumn(); //Para obtener el resultado de la consulta y poder calcular con él

    $registros_pagina = 10;
    //Calculo del número total de páginas

    $total_paginas = ceil($total_registros / $registros_pagina);

    //pasar de pagina y retroceder
    if ($num_paginas < $total_paginas) {
      if (isset($_POST["siguiente_pagina"])) {

        $num_paginas++;
      }
    }
    if ($num_paginas > 1) {
      if (isset($_POST["pagina_anterior"])) {
        $num_paginas--;
      }
    }
    //Ultima y primera pagina
    if (isset($_POST["ultima_pagina"])) {
      $num_paginas = $total_paginas;
    }

    if (isset($_POST["primera_pagina"])) {
      $num_paginas = 1;
    }


    //Calculo del registro desde el que comienza la pagina 
    $registros = ($num_paginas - 1) * $registros_pagina;

    ?>


    <?php
    //Ejecucion de la consulta 
    $sql = "SELECT nia, nombre, cv_file, telefono,email FROM alumno where true ORDER BY nia limit $registros, 10";
    if (isset($_POST["nombre_B"])) {
      $sql = "SELECT nia, nombre, cv_file,telefono ,email FROM alumno where true and nombre like :nombre ORDER BY nia";
    }
    $gsent = $pdo->prepare($sql);
    if (isset($_POST["nombre_B"])) {

      $nombre_B = '%' . $_POST["nombre_B"] . '%';

      $gsent->bindParam(':nombre', $nombre_B, PDO::PARAM_STR);
    }

    $gsent->execute();

    echo "<table>";
    echo "<tr><th>NIA</th><th>Nombre</th><th>Telefono</th><th>Email</th><th>CV</th></tr>";
    while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>" . $row['nia'] . "</td><td>" . $row['nombre'] . "</td><td>" . $row['telefono'] . "</td><td>" . $row['email'] . "</td><td>" . $row['cv_file'] . "</td>";

      // Boton "Editar"

      echo "<td><form method='post' action=''>
      <input type='hidden' name='nia' value='" . $row['nia'] . "'>
      <input type='hidden' name='nombre' value='" . $row['nombre'] . "'>
      <input type='hidden' name='telefono' value='" . $row['telefono'] . "'>
      <input type='hidden' name='email' value='" . $row['email'] . "'>
      <input type='submit' name='editar' value='Editar'>
      </form></td>";





      // Boton "Eliminar"
      echo "<td><form method='post' action='inicio_profesores.php'>
            <input type='hidden' name='nia_d' value='" . $row['nia'] . "'>
            <input type='submit' name='eliminar_alumno' value='Eliminar'></form></td>";
      echo "</tr>";
    }
    echo "</table>";
  } catch (PDOException $err) {
    echo "Error al ejecutar la consulta";
  }

  if (isset($_POST['editar'])) {
    $nia = $_POST['nia'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
  ?>

    <form method='post' action='inicio_profesores.php'>
      <input type='hidden' name='nia' value='<?php echo $nia; ?>'>

      <label for='nombre'>Nombre: </label>
      <input type='text' name='nombre' value='<?php echo $nombre; ?>'><br>

      <label for='telefono_edit'>Teléfono: </label>
      <input type='text' name='telefono' value='<?php echo $telefono; ?>'><br>

      <label for='email'>Email: </label>
      <input type='text' name='email' value='<?php echo $email; ?>'><br>

      <input type='submit' name='guardar_edicion' value='Guardar'>
    </form>

  <?php
    if (isset($_POST['guardar_edicion'])) {
      // Obtener los datos actualizados del formulario
      $nia = $_POST['nia'];
      $nombre = $_POST['nombre'];
      $telefono = $_POST['telefono'];
      $email = $_POST['email'];

      // Actualizar los datos del alumno en la base de datos
      $sql = "UPDATE alumno SET nombre = :nombre, telefono = :telefono, email = :email WHERE nia = :nia";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':nia', $nia);
      $stmt->bindParam(':nombre', $nombre);
      $stmt->bindParam(':telefono', $telefono);
      $stmt->bindParam(':email', $email);
      try {
        $stmt->execute();
        echo "Alumno actualizado correctamente.";
      } catch (PDOException $e) {
        echo "Error al actualizar el alumno: " . $e->getMessage();
      }
    }
  }


  // Insertar un nuevo alumno en la base de datos
  /*$sql = "INSERT INTO alumno (nia, nombre, telefono, email) VALUES (:nia, :nombre, :telefono, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nia', $nia);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->execute();*/





  ?>















  <?php
  //Eliminar alumno
  try {

    if (isset($_POST["eliminar_alumno"])) {

      $nia_d = $_POST['nia_d'] ?? null;

      $sql = "DELETE FROM alumno where nia = :nia";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':nia', $nia_d);
      $stmt->execute();


      echo "Alumno con NIA $nia_d eliminado correctamente.";
    }
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
  ?>

  <!--Paginación-->
  <form action="inicio_profesores.php" method="post">
    <input type="submit" name="primera_pagina" value="<<" <?php ?>>
    <input type="submit" name="pagina_anterior" value="<" <?php ?>>
    <input type="text" name="pagina" value="<?php echo $num_paginas ?>">
    <input type="submit" name="siguiente_pagina" value=">">
    <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
  </form>


</body>

</html>