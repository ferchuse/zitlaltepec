<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>:: Grupo Zitlaltepec ::</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script>
	//if(top.window.location.href!="http://vereficentros.com/" && top.window.location.href!="http://verificentros.net/" && top.window.location.href!="http://vereficentros.hgaribay.com/")
		//top.window.location.href="http://verificentros.net";
</script>
</head>

<body style="background: white;">
<p>&nbsp;</p>
<form id="forma" name="forma" method="POST" action="http://roa.agaribay.net/inicio.php">
<table border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td>&nbsp;</td>
        <td align="center" width="250"><img src="images/logo_zitlaltepec2.png" height="150" width="250" style="border:1px solid rgb(241, 243, 245);"></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td align="center" width="250"><img src="images/logo_turiazz.jpg" height="150" width="250" style="border:1px solid rgb(241, 243, 245);"></td>
        <td align="center" width="250" style="background-color: rgb(241, 243, 245);">
            <table width="250" border="0" align="center" cellpadding="8" cellspacing="0" bgcolor="#F1F3F5" class="">
              <tbody>
              <tr>
                 <!--
                <td width="50%"><div align="center">
                    <p>&nbsp;</p>
                  <table width="70%" border="0">
                      <tbody><tr>
                        <td><div align="center"><img src="./logingif.gif" width="80" height="69"></div></td>
                      </tr>
                      <tr>
                        <td class="bodyText"><div align="center">� Grupo Zitlaltepec ! </div></td>
                      </tr>
                      <tr>
                        <td class="bodyText"><div align="center"></div></td>
                      </tr>
                      <tr>
                        <td class="bodyText"><div align="center">Introduzca su usuario y password </div></td>
                      </tr>
                    </tbody></table>
                  <p>&nbsp;</p>
                </div></td>
                -->
                <td width="100%"><div align="center">
          			<!--<table width="90%" border="0" class="loginInnerTable">
          				<tr><td><font color="RED" size="5">Respaldando base de datos a nivel general... disculpe las molestias</font></td></tr>
          			</table>-->
                    <table width="90%" border="0" class="loginInnerTable" >
                      <tbody>
                      <tr>
                        <td>&nbsp;</td>
                        <td class="bodyTextBold">Usuario</td>
                        <td><input name="loginUser" type="text" class="textField" id="loginUser"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td class="bodyTextBold">Password</td>
                        <td><input name="loginPassword" type="password" class="textField" id="loginPassword"></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div align="center">
                            <input name="Submit" type="submit" class="appDefButton" value="Login">
                        </div></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                      </tr>
					  </tr>
					  <?php
							  if($_GET['ErrLogUs']) {
								  echo '<tr><th colspan="5"><font color="RED">Usuario y/o Password Incorrectos !</font></th></tr>';
							  }
					  
					  ?>
					  <tr>					  
                      <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <!--<td><a href="contacto_login.php">Contactenos</a></td>-->
                      </tr>
                    </tbody></table>
          	  </div></td>
              </tr>
            </tbody></table>
        </td>
        <td align="center" width="250"><img src="images/logo_taxibus.png" height="150" width="250" style="border:1px solid rgb(241, 243, 245);"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td align="center" width="250"><img src="images/logo_mexiquense.jpg" height="150" width="250" style="border:1px solid rgb(241, 243, 245);"></td>
        <td>&nbsp;</td>
    </tr>
</table>


</form>
</body>
</html>