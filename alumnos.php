<?php
$nombre=$_POST['nombre'] ?? null;
$num_paginas = intval($_POST['pagina'] ?? 1);
?>
<?php

  $nombre=$_POST['nombre'] ?? null;
  $num_paginas = intval($_POST['pagina'] ?? 1);
  $host='localhost';
  $dbname='practicas_alumnos';
  $user='root';
  $pass='';
  
  $total_registros = 0;
  $total_paginas = 0;

  try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    $pdo->query("SELECT 1");
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
  	<input type="text" name="nombre" id="nombre" value="<?php echo $nombre?>">
  	<input type="submit" value="Buscar">
  	<input type="reset" value="Reset">
  </form>

<?php

    try {
    //Total registros
    $total_registros_query = $pdo->query("SELECT count(*) FROM alumno");
    $total_registros = $total_registros_query->fetchColumn();

    //Calculo del número total de páginas
    $registros_pagina = 10;
    $total_paginas = ceil($total_registros / $registros_pagina);
    

    //Calculo del registro desde el que comienza la pagina 
    $registros = ($num_paginas-1)*$registros_pagina;

    
    $sql = "SELECT nia, nombre, apellido1, apellido2, mail FROM alumno where true limit $registros, $registros_pagina";
    if(isset($_POST["nombre"])){
      $sql = "SELECT nia, nombre, apellido1, apellido2, mail FROM alumno where true and nombre like :nombre limit $registros, $registros_pagina"; 
    }
    $gsent = $pdo->prepare($sql);
    if(isset($_POST["nombre"])){
        
      $nombre = '%' . $_POST["nombre"] . '%';

      $gsent->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    }
    $gsent->execute();


      echo "<table>";
    	echo "<tr><th>Nia</th><th>Nombre</th><th>Apellidos</th><th>Dirección</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>".$row['nia']."</td><td>".$row['nombre']."</td><td>".$row['apellido1']." ".$row['apellido2']."</td><td>".$row['mail']."</td></tr>";	
    }
    echo "</table>";
	  }catch(PDOException $err) {
    echo "Error al ejecutar la consulta"; 
	  }
  ?>
  <?php
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
  ?>
  <form action="alumnos.php" method="post">
    <input type="submit" name="primera_pagina" value="<<" <?php ?>>
    <input type="submit" name="pagina_anterior" value="<" <?php ?>>
    <input type="text" name="pagina" value="<?php echo $num_paginas?>">
    <input type="submit" name="siguiente_pagina" value=">">
    <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
  </form>
    
</body>
</html>