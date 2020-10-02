<?php
	include("numlet.php");
	//echo $_GET['textoimp'];
	$textosimp=explode("|*|",$_GET['textoimp']);
	//echo count($textosimp);
	if($_GET['logo']!="") $logo=$_GET['logo'];
	else $logo='BRUAS';
	for($x=0;$x<count($textosimp);$x++){
		$texto=chr(27)."@";
		//$textoimp=explode("|",$_GET['textoimp']);
		$textoimp=explode("|",$textosimp[$x]);
		for($i=0;$i<count($textoimp);$i++){
			$texto.=$textoimp[$i].chr(10).chr(13);
		}
		if($_GET['barcode']!="")$texto.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2).$_GET['barcode'].chr(0);
		$texto.=chr(10).chr(13).chr(29).chr(86).chr(66).chr(0);
		if($file=fopen("nota.txt","w+")){
			fwrite($file,$texto);
			fclose($file);
			// chmod("abonos/F_".$folio.".txt", 0777);
		}
		if(substr(PHP_OS,0,3) != "WIN"){
			system("lp ".$logo.".TMB");
			system("lp nota.txt");
		}
		else{
			//system("copy ".$logo.".TMB lpt3");
			//system("copy nota.txt lpt3: >null:");
			if(file_exists($logo.".TMB")){
				exec('copy '.$logo.'.TMB "\\\\DESKTOP-8DI0F84\\EPSON TM-T20II Receipt"');
			}
			exec('copy nota.txt "\\\\DESKTOP-8DI0F84\\EPSON TM-T20II Receipt"');
		}
		if($_GET['copia']==1){
			$texto=chr(27)."@"."        COPIA".chr(10).chr(13);
			//$textoimp=explode("|",$_GET['textoimp']);
			$textoimp=explode("|",$textosimp[$x]);
			for($i=0;$i<count($textoimp);$i++){
				$texto.=$textoimp[$i].chr(10).chr(13);
			}
			if($_GET['barcode']!="")$texto.=chr(29)."h".chr(80).chr(29)."H".chr(2).chr(29)."k".chr(2).$_GET['barcode'].chr(0);
			$texto.=chr(10).chr(13).chr(29).chr(86).chr(66).chr(0);
			if($file=fopen("nota1.txt","w+")){
				fwrite($file,$texto);
				fclose($file);
				// chmod("abonos/F_".$folio.".txt", 0777);
			}
			//system("copy ".$logo.".TMB lpt3");
			if(substr(PHP_OS,0,3) != "WIN"){
				system("lp ".$logo.".TMB");
				system("lp nota1.txt");
			}
			else{
				//system("copy ".$logo.".TMB lpt3");
				//system("copy nota1.txt lpt3: >null:");
				if(file_exists($logo.".TMB")){
					exec('copy '.$logo.'.TMB "\\\\DESKTOP-8DI0F84\\EPSON TM-T20II Receipt"');
				}
				exec('copy nota1.txt "\\\\DESKTOP-8DI0F84\\EPSON TM-T20II Receipt"');
			}
		}
	}
?>