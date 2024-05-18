<?php
require_once 'validar_sesion.php';
$nombre_B = $_POST['nombre_B'] ?? null;
$num_paginas = intval($_POST['pagina'] ?? 1);

$host = 'localhost';
$dbname = 'gestion_fct';
$user = 'root';
$pass = '';

$total_registros = 0;
$total_paginas = 0;

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
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
  <link rel="stylesheet" href="css/EmpresaCSS.css">
  <title>Empresas</title>
</head>

<body>
  <header>
    <p>Bienvenido <?php echo $_SESSION['usuario'] ?></p>
    <form action="empresas.php" method="post">
      <input type="submit" name="logout" value="Cerrar Sesión">
      <?php
      if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: login.php');
      }
      ?>
    </form>
  </header>
  <?php
  //Procesamiento de formularios

  // Editar la empresa
  if (isset($_POST['guardar_edicion'])) {
    $cif = $_POST['cif'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $localidad = $_POST['localidad'];
    $provincia = $_POST['provincia'];
    $telefono = $_POST['telefono'];
    $persona_contacto = $_POST['persona_contacto'];

    // Actualiza los datos de la empresa en la base de datos
    $sql = "UPDATE empresa SET nombre = :nombre, email = :email, direccion = :direccion, localidad = :localidad, provincia = :provincia, telefono = :telefono, persona_contacto = :persona_contacto WHERE cif = :cif";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':cif', $cif);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':localidad', $localidad);
    $stmt->bindParam(':provincia', $provincia);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':persona_contacto', $persona_contacto);
    $stmt->execute();
    echo "Empresa actualizada correctamente.";
  }

  // Añadir empresa
  $cif = $_POST['cif'] ?? null;
  $nombre = $_POST['nombre'] ?? null;
  $email = $_POST['email'] ?? null;
  $direccion = $_POST['direccion'] ?? null;
  $localidad = $_POST['localidad'] ?? null;
  $provincia = $_POST['provincia'] ?? null;
  $telefono = $_POST['telefono'] ?? null;
  $persona_contacto = $_POST['persona_contacto'] ?? null;

  if (isset($_POST['insertar'])) {
    // Inserta una nueva empresa en la base de datos
    $sql = "INSERT INTO empresa (cif, nombre, email, direccion, localidad, provincia, telefono, persona_contacto) VALUES (:cif, :nombre, :email, :direccion, :localidad, :provincia, :telefono, :persona_contacto)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':cif', $cif);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':localidad', $localidad);
    $stmt->bindParam(':provincia', $provincia);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':persona_contacto', $persona_contacto);
    $stmt->execute();

    echo "<script>alert('Empresa $nombre añadida correctamente')</script>";
  }

  // Eliminar empresa
  try {
    if (isset($_POST["eliminar_empresa"])) {
      $cif_d = $_POST['cif_d'] ?? null;

      $sql = "DELETE FROM empresa WHERE cif = :cif";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':cif', $cif_d);
      $stmt->execute();

      echo "Empresa con CIF $cif_d eliminada correctamente.";
    }
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
  ?>
  <form action="empresas.php" method="post">
    <label for="nombre">NOMBRE: </label>
    <input type="text" name="nombre_B" id="nombre" value="<?php echo $nombre_B ?>">
    <input type="submit" value="Buscar">
    <input type="reset" value="Reset">
  </form>

  <?php
  // Cálculos Paginación
  try {
    // Total registros
    $total_registros_query = $pdo->query("SELECT count(*) FROM empresa");
    $total_registros = $total_registros_query->fetchColumn(); // Para obtener el resultado de la consulta y poder calcular con él

    $registros_pagina = 10;
    // Cálculo del número total de páginas
    $total_paginas = ceil($total_registros / $registros_pagina);

    // Pasar de página y retroceder
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

    // Última y primera página
    if (isset($_POST["ultima_pagina"])) {
      $num_paginas = $total_paginas;
    }
    if (isset($_POST["primera_pagina"])) {
      $num_paginas = 1;
    }

    // Cálculo del registro desde el que comienza la página 
    $registros = ($num_paginas - 1) * $registros_pagina;

    // Ejecución de la consulta para mostrar la tabla
    $sql = "SELECT cif, nombre, email, direccion, localidad, provincia, telefono, persona_contacto FROM empresa WHERE true ORDER BY cif LIMIT $registros, 10";

    // Procesamiento de formulario de búsqueda
    if (isset($_POST["nombre_B"])) {
      $sql = "SELECT cif, nombre, email, direccion, localidad, provincia, telefono, persona_contacto FROM empresa WHERE true AND nombre LIKE :nombre ORDER BY cif LIMIT $registros, 10";
    }
    $gsent = $pdo->prepare($sql);
    if (isset($_POST["nombre_B"])) {
      $nombre_B = '%' . $_POST["nombre_B"] . '%';
      $gsent->bindParam(':nombre', $nombre_B, PDO::PARAM_STR);
    }
    $gsent->execute();

    echo "<table>";
    echo "<tr><th>CIF</th><th>Nombre</th><th>Email</th><th>Dirección</th><th>Localidad</th><th>Provincia</th><th>Teléfono</th><th>Persona de Contacto</th>
    <th colspan='2'>
    <form method='post' action='empresas.php'>
    <input type='submit' name='insertar_empresa' value='Insertar Empresa'>
    </form>
    </th>
    </tr>";
    while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>" . $row['cif'] . "</td><td>"
        . $row['nombre'] . "</td><td>"
        . $row['email'] . "</td><td>"
        . $row['direccion'] . "</td><td>"
        . $row['localidad'] . "</td><td>"
        . $row['provincia'] . "</td><td>"
        . $row['telefono'] . "</td><td>"
        . $row['persona_contacto'] . "</td>";

            // Botón "Editar"
            echo "<td><form method='post' action='empresas.php'>
            <input type='hidden' name='cif' value='" . $row['cif'] . "'>
            <input type='hidden' name='nombre' value='" . $row['nombre'] . "'>
            <input type='hidden' name='email' value='" . $row['email'] . "'>
            <input type='hidden' name='direccion' value='" . $row['direccion'] . "'>
            <input type='hidden' name='localidad' value='" . $row['localidad'] . "'>
            <input type='hidden' name='provincia' value='" . $row['provincia'] . "'>
            <input type='hidden' name='telefono' value='" . $row['telefono'] . "'>
            <input type='hidden' name='persona_contacto' value='" . $row['persona_contacto'] . "'>
            <input type='submit' name='editar' value='Editar'>
            </form></td>";
      
            // Botón "Eliminar"
            echo "<td><form method='post' action='empresas.php'>
                  <input type='hidden' name='cif_d' value='" . $row['cif'] . "'>
                  <input type='submit' name='eliminar_empresa' value='Eliminar'></form></td>";
            echo "</tr>";
          }
          echo "</table>";
        } catch (PDOException $err) {
          echo "Error al ejecutar la consulta";
        }
      
        ?>
        <!--Paginación-->
        <form action="empresas.php" method="post" id="paginacion">
          <input type="submit" name="primera_pagina" value="<<" <?php ?>>
          <input type="submit" name="pagina_anterior" value="<" <?php ?>>
          <input type="text" name="pagina" value="<?php echo $num_paginas ?>">
          <input type="submit" name="siguiente_pagina" value=">">
          <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
        </form>
        <?php
        if (isset($_POST['editar']) && !isset($_POST['cancelar'])) {
          $cif = $_POST['cif'];
          $nombre = $_POST['nombre'];
          $email = $_POST['email'];
          $direccion = $_POST['direccion'];
          $localidad = $_POST['localidad'];
          $provincia = $_POST['provincia'];
          $telefono = $_POST['telefono'];
          $persona_contacto = $_POST['persona_contacto'];
        ?>
      
          <!-- Formulario de edición -->
          <form method='post' action='empresas.php' id='insercion_edicion'>
            <input type='hidden' name='cif' value='<?php echo $cif; ?>'>
            <label for='nombre'>Nombre: </label>
            <input type='text' name='nombre' value='<?php echo $nombre; ?>'><br>
            <label for='email'>Email: </label>
            <input type='text' name='email' value='<?php echo $email; ?>'><br>
            <label for='direccion'>Dirección: </label>
            <input type='text' name='direccion' value='<?php echo $direccion; ?>'><br>
            <label for='localidad'>Localidad: </label>
            <input type='text' name='localidad' value='<?php echo $localidad; ?>'><br>
            <label for='provincia'>Provincia: </label>
            <input type='text' name='provincia' value='<?php echo $provincia; ?>'><br>
            <label for='telefono'>Teléfono: </label>
            <input type='text' name='telefono' value='<?php echo $telefono; ?>'><br>
            <label for='persona_contacto'>Persona de Contacto: </label>
            <input type='text' name='persona_contacto' value='<?php echo $persona_contacto; ?>'><br>
            <input type='submit' name='guardar_edicion' value='Guardar'>
            <input type='submit' name='cancelar' value='Cancelar'>
          </form>
      
        <?php
        }
      
        if (isset($_POST['insertar_empresa']) && !isset($_POST['cancelar'])) {
        ?>
      
          <!-- Formulario de inserción -->
          <form method='post' action='empresas.php' id='insercion_edicion'>
            <label for='cif'>CIF: </label>
            <input type='text' name='cif'><br>
            <label for='nombre'>Nombre: </label>
            <input type='text' name='nombre'><br>
            <label for='email'>Email: </label>
            <input type='text' name='email'><br>
            <label for='direccion'>Dirección: </label>
            <input type='text' name='direccion'><br>
            <label for='localidad'>Localidad: </label>
            <input type='text' name='localidad'><br>
            <label for='provincia'>Provincia: </label>
            <input type='text' name='provincia'><br>
            <label for='telefono'>Teléfono: </label>
            <input type='text' name='telefono'><br>
            <label for='persona_contacto'>Persona de Contacto: </label>
            <input type='text' name='persona_contacto'><br>
            <input type='submit' name='insertar' value='Insertar'>
            <input type='submit' name='cancelar' value='Cancelar'>
          </form>
        <?php
        }
        ?>
      
      </body>
      
      </html>
      