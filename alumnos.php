<?php
$nombre=$_POST['nombre'] ?? null;
$nombre_B=$_POST['nombre_B'] ?? null;
$num_paginas = intval($_POST['pagina'] ?? 1);
$alumno = array(); //array para guardar los datos del alumno

?>
<?php

  $nombre=$_POST['nombre'] ?? null;
  $host='localhost';
  $dbname='gestion_fct';
  $user='root';
  $pass='';
  
  $total_registros = 0;
  $total_paginas = 0;

  try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
  catch(PDOException $err)
  {
   echo "Error: ejecutando consulta SQL.";  
  }
  ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Alumnos</title>
</head>
<body>
<form action="alumnos.php" method="post">
  	<label for="nombre">NOMBRE: </label>
  	<input type="text" name="nombre_B" id="nombre" value="<?php echo $nombre_B?>">
  	<input type="submit" value="Buscar">
  	<input type="reset" value="Reset">
</form>

<?php

    try {
    

    //Total registros
    $sql_registros = "SELECT count(*) FROM alumno";
    $gsent = $pdo->prepare($sql_registros);
    $gsent->execute();
    $total_registros = $gsent->fetch(PDO::FETCH_ASSOC);
    $total_registros_int = intval($total_registros['count(*)']);

    

    //Calculo del número total de páginas
    $registros_pagina = 10;
    $total_paginas = ceil($total_registros_int / $registros_pagina);
    

    //Calculo del registro desde el que comienza la pagina 
    $registros = ($num_paginas-1)*$registros_pagina;

    //Calculo paginacion

    if(isset($_POST["siguiente_pagina"])){
      ++$num_paginas;
    }
    if(isset($_POST["pagina_anterior"])){ 
    if($num_paginas<$total_paginas){
      --$num_paginas;
    }
    }
    if(isset($_POST["ultima_pagina"])){ 
    $num_paginas = $total_paginas;
    }
    if(isset($_POST["primera_pagina"])){ 
    $num_paginas = 1;
  }
  

    $sql = "SELECT nia, nombre, cv_file, telefono,email FROM alumno where true ORDER BY nia limit $registros, 10";
    if(isset($_POST["nombre_B"])){
      $sql = "SELECT nia, nombre, cv_file,telefono ,email FROM alumno where true and nombre like :nombre ORDER BY nia limit $registros, $registros_pagina"; 
    }
    $gsent = $pdo->prepare($sql);
    if(isset($_POST["nombre_B"])){
        
      $nombre_B = '%' . $_POST["nombre_B"] . '%';

      $gsent->bindParam(':nombre', $nombre_B, PDO::PARAM_STR);
    }
    $gsent->execute();


      echo "<table>";
    	echo "<tr><th>NIA</th><th>Nombre</th><th>Telefono</th><th>Email</th><th>CV</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>".$row['nia']."</td><td>".$row['nombre']."</td><td>".$row['telefono']."</td><td>".$row['email']."</td><td>".$row['cv_file']."</td>";

      // Boton "Editar"
      echo "<td>";
      echo "<form method='post' action='alumnos.php'>";
      echo "<input type='hidden' name='nia' value='" . $row['nia'] . "'>";
      echo "<input type='submit' value='Editar'>";
      echo "</form>";

      // Boton "Eliminar"
      echo "<td><form method='post' action='alumnos.php'>
            <input type='hidden' name='nia_d' value='" . $row['nia'] . "'>
            <input type='submit' name='eliminar_alumno' value='Eliminar'></form></td>";

      echo "</tr>";
    }
    echo "</table>";


    //Recogida de datos para la edicion de los alumnos
    if(isset($_POST['nia'])){
      $nia = $_POST['nia'];
      $sql = "SELECT * FROM alumno WHERE nia = :nia"; //aqui se recoge los datos del alumno seleccionado
      $gsent = $pdo->prepare($sql);
      $gsent->bindParam(':nia', $nia, PDO::PARAM_INT);
      $gsent->execute();
      $alumno = $gsent->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    
    <?php
	  }catch(PDOException $err) {
    echo "Error al ejecutar la consulta"; 
	  }
    ?>
    <?php
    
    
  ?>

   <!--Paginación-->
  <form action="alumnos.php" method="post">
    <input type="submit" name="primera_pagina" value="<<" <?php ?>>
    <input type="submit" name="pagina_anterior" value="<" <?php ?>>
    <input type="text" name="pagina" value="<?php echo $num_paginas?>">
    <input type="submit" name="siguiente_pagina" value=">">
    <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
  </form>

  <?php
  if(isset($alumno['nia']) || isset($alumno['nombre']) || isset($alumno['telefono']) || isset($alumno['email'])) {
  ?>

  <!--Formulario editar datos alumno-->
  <h2>Editar alumno</h2>
    <form method="post" action="alumnos.php">
            <input type="hidden" name="nia" value="<?php echo $alumno['nia']; ?>">
            Nombre: <input type="text" name="nombre" value="<?php echo $alumno['nombre']; ?>"><br>
            Telefono: <input type="text" name="telefono" value="<?php echo $alumno['telefono']; ?>"><br>
            Email: <input type="text" name="email" value="<?php echo $alumno['email']; ?>"><br>
            
            <input type="submit" value="Actualizar">
    </form>

  
  <?php
  $nia_M = $_POST['nia'];
  $nombre_M = $_POST['nombre']??null;
  $telefono_M = $_POST['telefono']??null;
  $email_M = $_POST['email']??null;

  
  $sql = "UPDATE alumno SET nombre = :nombre_M, telefono = :telefono_M, email = :email_M WHERE nia = :nia_M";
  $stmt = $pdo->prepare($sql);


  //ejecuta la consulta para actualizar los datos
  $stmt->execute([
    ':nombre_M' => $nombre_M,
    ':telefono_M' => $telefono_M,
    ':email_M' => $email_M,
    ':nia_M' => $nia_M
  ]);
  

  }
  ?>

  <h2>Insertar alumno nuevo:</h2>
  <form action="alumnos.php" method="post">
  <label for="nia_I">NIA*: </label>
  <input type="text" name="nia_I" id="nia_I" pattern="[0-9]{8}">
  <label for="nombre_i">Nombre*: </label>
  <input type="text" name="nombre_i" id="nombre_i">
  <label for="telefono">Telefono: </label>
  <input type="text" name="telefono" id="telefono">
  <label for="email">Email: </label>
  <input type="text" name="email" id="email">

  <input type="submit" value="Insertar">
  <input type="reset" value="Reset">

</form>
  <?php
  try{
    if(isset($_POST['nia_I']) && !empty($_POST['nia_I'])) {
      
      $nia_I=$_POST['nia_I'];

      //Se comprueba que el nia no está repetido
      $comprobarNia = $pdo->prepare("SELECT COUNT(*) FROM alumno WHERE nia = :nia_I");
      $comprobarNia->bindParam(':nia_I', $nia_I);
      $comprobarNia->execute();
      $comprobar = $comprobarNia->fetchColumn(); //se guarda en una variable las filas que coinciden con el nia introducido

      if($comprobar == 0){//cuando no hay filas que coincidan con el nia introducido entonces se introducen el resto de datos
        
        $nombre_i=$_POST['nombre_i'] ?? null;
        $telefono=$_POST['telefono'] ?? null;
        $email = $_POST['email'] ?? null;

        $sql = "insert into alumno (nia, nombre, telefono, email) values (:nia_I, :nombre, :telefono, :email)";
        $datos = [":nia_I"=>$nia_I, ":nombre"=>$nombre_i, ":telefono"=>$telefono, ":email"=>$email];
        $stmt = $pdo->prepare($sql);
        $row = $stmt->execute($datos);

        echo "<h3>SE HA ENVIADO CORRECTAMENTE<h3>";
      }else{
        echo "<h3>El NIA introducido ya existe</h3>";
      }
    }
  }catch(PDOException $e) {
  echo $e->getMessage();
  }
  ?>

  <?php
  try{
    
  if(isset($_POST["eliminar_alumno"])){
    
    $nia_d = $_POST['nia_d'] ?? null;

    $sql = "DELETE FROM alumno where nia = :nia"; 
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nia',$nia_d);
    $stmt->execute();


    echo "Alumno con NIA $nia_d eliminado correctamente.";
  }
  }catch(PDOException $e){
    echo $e->getMessage();
  }
  ?>

 

</body>
</html>