<?php
$nombre=$_POST['nombre'] ?? null;
$num_paginas = intval($_POST['pagina'] ?? 1);

?>
<?php

  $nombre=$_POST['nombre'] ?? null;
  $num_paginas = intval($_POST['pagina'] ?? 1);
  $host='localhost';
  $dbname='gestion_fct';
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
    <title>Empresas</title>
</head>
<body>
 <form action="empresas.php" method="post">
  	<label for="nombre">NOMBRE: </label>
  	<input type="text" name="nombre" id="nombre" value="<?php echo $nombre?>">
  	<input type="submit" value="Buscar">
  	<input type="reset" value="Reset">
  </form>

<?php

    try {
    //Total registros
    $total_registros_query = $pdo->query("SELECT count(*) FROM empresa");
    $total_registros = $total_registros_query->fetchColumn();

    //Calculo del número total de páginas
    $registros_pagina = 10;
    $total_paginas = ceil($total_registros / $registros_pagina);
    

    //Calculo del registro desde el que comienza la pagina 
    $registros = ($num_paginas-1)*$registros_pagina;

    
    $sql = "SELECT nombre, cif, email, direccion, localidad, provincia, telefono, persona_contacto FROM empresa where true limit $registros, $registros_pagina";
    if(isset($_POST["nombre"])){
      $sql = "SELECT nombre, cif, email, direccion, localidad, provincia, telefono, persona_contacto FROM empresa where true and nombre like :nombre limit $registros, $registros_pagina"; 
    }
    $gsent = $pdo->prepare($sql);
    if(isset($_POST["nombre"])){
        
      $nombre = '%' . $_POST["nombre"] . '%';

      $gsent->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    }
    $gsent->execute();


      echo "<table>";
    	echo "<tr><th>Nombre</th><th>CIF</th><th>EMAIL</th><th>DIRECCIÓN</th><th>LOCALIDAD</th><th>PROVINCIA</th><th>TELEFONO</th><th>PERSONA DE CONTACTO</th></tr>";
    	while ($row = $gsent->fetch(PDO::FETCH_ASSOC)) {
      echo "<tr><td>".$row['nombre']."</td><td>".$row['cif']."</td><td>".$row['email']."</td><td>".$row['direccion']."</td><td>".$row['localidad']."</td><td>".$row['provincia']."</td><td>".$row['telefono']."</td><td>".$row['persona_contacto']."</td></tr>";	
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
  <form action="empresas.php" method="post">
    <input type="submit" name="primera_pagina" value="<<" <?php ?>>
    <input type="submit" name="pagina_anterior" value="<" <?php ?>>
    <input type="text" name="pagina" value="<?php echo $num_paginas?>">
    <input type="submit" name="siguiente_pagina" value=">">
    <input type="submit" name="ultima_pagina" value=">>" <?php ?>>
  </form>

  
  <h2>Insertar Empresa nueva:</h2>
  <form action="empresas.php" method="post">
  <label for="nia">NIA*: </label>
  <input type="text" name="nia" id="nia" pattern="[0-9]{8}">
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
    if(isset($_POST['nia']) && !empty($_POST['nia'])) {
      
      $nia=$_POST['nia'];

      //Se comprueba que el nia no está repetido
      $comprobarNia = $pdo->prepare("SELECT COUNT(*) FROM alumno WHERE nia = :nia");
      $comprobarNia->bindParam(':nia', $nia);
      $comprobarNia->execute();
      $comprobar = $comprobarNia->fetchColumn(); //se guarda en una variable las filas que coinciden con el nia introducido

      if($comprobar == 0){//cuando no hay filas que coincidan con el nia introducido entonces se introducen el resto de datos
        
        $nombre_i=$_POST['nombre_i'] ?? null;
        $telefono=$_POST['telefono'] ?? null;
        $email = $_POST['email'] ?? null;

        $sql = "insert into empresa (nia, nombre, telefono, email) values (:nia, :nombre, :telefono, :email)";
        $datos = [":nia"=>$nia, ":nombre"=>$nombre_i, ":telefono"=>$telefono, ":email"=>$email];
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
  
  <h2>Eliminar Empresa:</h2>
  <form action="empresas.php" method="post">
  <label for="nia">NIA*: </label>
  <input type="text" name="nia_d" id="nia_d" pattern="[0-9]{8}">

  <input type="submit" value="Eliminar">
  <input type="reset" value="Reset">

  <?php
  try{
    $nia_d = $_POST['nia_d'] ?? null;
  if(isset($_POST["nia_d"])){
    

    $sql = "DELETE FROM empresa where nia = :nia"; 
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nia',$nia_d);
    $stmt->execute();

    echo "Empresa con NIA $nia_d eliminado correctamente.";
  }
  }catch(PDOException $e){
    echo $e->getMessage();
  }
  ?>

</body>
</html>