<?php
	session_start();
	$array_modulos=array(1=>"Catalogos", 2=>"Parque Vehicular",3=>"Operadores",4=>'Recaudacion Unidades',
	6=>'Recaudacion Operadores',9=>'Recaudacion Pachuca',5=>'Movimientos',7=>'Accidentes',8=>'Taquilla',10=>'Taquilla sin Guia', 13=>"Gps",12=>"Servicios",16=>"Monitoreo",17=>"Bases",99=>"Administracion");
	
	include_once("../conexi.php");
	if(!isset($link)){
		
		$link = Conectarse();
		
	}
	// echo "menuppal2";
	// menuppal2();
	
	// print_r($link)
	
	function menuppal2() {
		global $base,$array_modulos,$array_plaza,$PHP_SELF,$_POST, $link;
		$url=split("/",$_SERVER["PHP_SELF"]);
		$url=array_reverse($url);
		echo '
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr><td height="20" bgcolor="#9CDAFE"><span class="style1">Menu </span></td></tr>
		<tr><td><a href="#" onClick="document.forma.cvemenu.value=1;atcr(\'inicio.php\',\'\',\'\',\'\')">-P&aacute;gina de Inicio</a></td></tr>
		<tr><td><a href="#" onClick="document.forma.cvemenu.value=2;atcr(\'cambiopass.php\',\'\',\'\',\'\')">-Cambiar Password</a></td></tr>
		';
		
		
		$mostrar="";
		foreach($array_modulos as $k=>$v){ 
			if($_SESSION['CveUsuario']==1){
				$rs=mysqli_query($link, "SELECT * FROM menu WHERE modulo='$k' ORDER BY orden");
			}
			elseif($SESSION['TipoUsuario']==1){
				$rs=mysqli_query($link,"SELECT * FROM menu WHERE modulo='$k'and cve!='67' and cve!='68' ORDER BY orden");
			}
			else{
				$rs=mysqli_query($link,"SELECT a.* FROM menu as a INNER JOIN usuario_accesos as b ON (b.menu=a.cve AND b.usuario='".$_SESSION['CveUsuario']."' AND b.acceso>0) WHERE a.modulo='$k' and a.cve!='67' and a.cve!='68' ORDER BY a.orden");
			}
			if(mysqli_num_rows($rs)>0){
				
				echo '
				<tr>	  
				<td height="20" bgcolor="#9CDAFE">
				<span id="tmenu1" class="style1" onClick="if((menu'.$k.'%2)==0) $(\'.cmenu'.$k.'\').show(\'slow\'); else $(\'.cmenu'.$k.'\').hide(\'slow\'); menu'.$k.'++;">
				'.$v.'
				</span>
				</td>
				</tr>
				<tr><td><table class="cmenu'.$k.'" style="display:none">';
				while($ro=mysqli_fetch_array($rs)) {
					
					IF($ro['link'] == "monitoreo.php"){
						
						echo "<tr><td><a href='{$ro['link']}' >-{$ro['nombre']}</a></td></tr>";
						
						
					}
					else{
						echo '
						<tr><td><a href="#" onClick="
						objeto=crearObjeto();
						if (objeto.readyState != 0) {
						alert(\'Error: El Navegador no soporta AJAX\');
						} else {
						objeto.open(\'POST\',\'inicio.php\',true);
						objeto.setRequestHeader(\'Content-Type\',\'application/x-www-form-urlencoded\');
						objeto.send(\'ajax=99&usuario='.$_POST['cveusuario'].'&cvereg='.$_POST['cveregistro'].'&idmenu='.$ro['cve'].'\');
						objeto.onreadystatechange = function()
						{
						if (objeto.readyState==4)
						{document.forma.cvemenu.value='.$ro['cve'].';atcr(\''.$ro['link'].'\',\''.$ro['target'].'\',\'0\',\'\');}
						}
						}
						">-'.$ro['nombre'].'</a></td></tr>';
						if($_POST['cvemenu']==$ro['cve'])
						$mostrar='cmenu'.$k;
					}
				}
				echo '</table></td></tr>';
			}
		}
		
		
		echo '</table>';
		// if($mostrar!='') {
		// echo '<script language="javascript">$(\'.'.$mostrar.'\').show();'.substr($mostrar,1).'++;</script>';
		// }
	}
	
?>



<ul class="sidebar navbar-nav d-print-none">
	<li class="nav-item active"> 
		<a class="nav-link" href="../inicio.php">
			<i class="fas fa-fw fa-home"></i>
			<span>
				Inicio 
				
			</span>
		</a>
	</li>
	
	
	<?php
		foreach($array_modulos as $key=>$modulo){	
			
		?>
		
		
		<li class="nav-item dropdown ">
			<a class="nav-link dropdown-toggle" href="#"  data-toggle="dropdown" >
				<span><?= $modulo?></span>
			</a>
			<div class="dropdown-menu " >
				<?php 
					
					if($_SESSION['CveUsuario']==1){
						$q_catalogos = "SELECT * FROM menu WHERE modulo='$key' ORDER BY orden";
					}
					else{
						$q_catalogos = "SELECT a.* FROM menu as a INNER JOIN usuario_accesos as b ON (b.menu=a.cve AND b.usuario='".$_SESSION['CveUsuario']."' AND b.acceso>0) WHERE a.modulo='$key' and a.cve!='67' and a.cve!='68' ORDER BY a.orden";	
						
					}
					
					$result_catalogos = mysqli_query($link, $q_catalogos);
					if(!$result_catalogos){
						echo mysqli_error($link);
					}
					
					while($fila = mysqli_fetch_assoc($result_catalogos)){
						echo "<a class='dropdown-item' href='../{$fila["link"]}' ";
						echo ">-{$fila['nombre']}</a>";
						
					}
				?> 
			</div>
		</li> 
		
		
		
		
		
		
		<?php
		}
	?>
	
</ul>