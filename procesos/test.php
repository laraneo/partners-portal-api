<?php

$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        
        $cabeceras .= 'From: Tickets Abiertos <webmaster@avaytec.com>' . "\r\n" ;
		//$cabeceras .= 'From: Tickets Abiertos <webmaster@avaytec.com>' . "\r\n" . "CC: nortiz@avaytec.com";
        
        //$cabeceras .= 'From: Tickets Abiertos <webmaster@avaytec.com>' . "\r\n";
        $date = date('m/d/Y h:i:s a', time());
        $para = "rechacon@avaytec.com, laraneo@gmail.com, nortiz@avaytec.com";
        $titulo = "Tickets Abiertos  - Awaiting User " . $date;
        $mensaje = $this->buildHTMLMessage2();
		
		echo date('m/d/Y h:i:s a', time()) . "<br>";
		echo $mensaje . "<br>";
		
        mail($para, $titulo, $mensaje, $cabeceras);
		
		?>