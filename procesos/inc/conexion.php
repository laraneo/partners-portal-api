<?php  

$servername = "10.0.0.17";
$username = "partnerLCC";
$password = "luca123456***";
$dbname = "PartnersBookings";

$NOTIFICACIONES_servername = "10.0.0.17";
$NOTIFICACIONES_username = "partnerLCC";
$NOTIFICACIONES_password = "luca123456***";
$NOTIFICACIONES_dbname = "PartnersNotificaciones";



// Create connection
//$conn = new mysqli($servername, $username, $password, $dbname);

$connectionInfo = array( "UID"=>$username,                              
							 "PWD"=>$password,                              
							 "Database"=>$dbname);   
		
/* Connect using SQL Server Authentication. */    
$conn = sqlsrv_connect( $servername, $connectionInfo);   


$connectionInfoNotificaciones = array( "UID"=>$NOTIFICACIONES_username,                              
							 "PWD"=>$NOTIFICACIONES_password,                              
							 "Database"=>$NOTIFICACIONES_dbname);   
		
/* Connect using SQL Server Authentication. */    
$NOTIFICACIONES_conn = sqlsrv_connect( $NOTIFICACIONES_servername, $connectionInfoNotificaciones);   


// Check connection
/*if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} */