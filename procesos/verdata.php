<?php 
    include('inc/conexion.php');

    $debug = true;

/*
$conn = sqlsrv_connect( $servername, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$sql = "select opening_time, closing_time from booking_times_packages WHERE package_id=2 AND number=4";*/

	

	$tsql = "select * from draw_requests  
            order by priority ASC, NEWID()";			
			
    if ($debug) echo $tsql . "<br>";

    $stmt = sqlsrv_query( $conn, $tsql);    
	if( $stmt === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
    
    $found = 0;
    $status = 1;        


	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		  echo "ID: " .  $row["draw_id"] .  " - " . $row["user_id"] . " - " . $row["package_id"] . " - " . $row["draw_date"] . " - " . $row["draw_time"] . " - " . $row["status"] . " - " . $row["priority"] . " - " . $row["score"] . " - " . $row["booking_id"]  .  "<br>"; 
	}

    /* Free statement and connection resources. */    
    sqlsrv_free_stmt( $stmt);    
    sqlsrv_close( $conn);   

    sqlsrv_close( $NOTIFICACIONES_conn);   


?>