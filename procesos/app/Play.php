<?php 

namespace App;   


class Play {

    var $db;
    var $debug = false;
    var $URL_Confirmacion = "http://localhost:8081/confirmPlayer.php?token=<#TOKEN#>";

	
	//var $winner_template = "Estimado <#PARTICIPANTE#> ha sido ganador del sorteo de <#CATEGORIA#> el <#FECHAHORA#> haga click en el siguiente enlace si desea confirmar su reservacion <br><br>  <a href='<#LINKCONFIRMACION#>' target='_blank'>Enlace</a>";
	var $winner_template = "";
	/*
	<#PARTICIPANTE#><#FECHAHORA#><#CATEGORIA#><#ACCION#><#CEDULA#><#LINKCONFIRMACION#>
	*/
	
	
    var $USE_HANDICAP_FVG = true;

    function __construct(){

        include('inc/conexion.php');

		//leer template email
		$this->winner_template = file_get_contents('./views/templateEmail.html', FILE_USE_INCLUDE_PATH);
		
		/*
		echo $this->winner_template;
		die();
		*/
		
        $TotalItems  = 0;
        $token = "";
		$draw_id = -1;

        if (isset($_GET['action'])){


            if($_GET['action']==1){
				
                if (isset($_GET['draw_id']))
                {
                    $draw_id = $_GET['draw_id'];
                }
                else
                {
					//$draw_id=-1;
                    //echo "Token no especificado";
                }
                print_r($this->Sorteos($draw_id,$conn, $NOTIFICACIONES_conn));
				
			}
			else if($_GET['action']==2){
                if (isset($_GET['draw_id']))
                {
                    $draw_id = $_GET['draw_id'];
					print_r($this->SorteosReset($draw_id,$conn, $NOTIFICACIONES_conn));		
					echo "Proceso Finalizado<br>\n";    					
                }
                else
                {
					
                    echo "ID no especificado";
                }
                
			}
			else
			{
				echo "Proceso no definido";
			}

        } else {
            echo "Debe indicar el proceso a ejecutar";
        }
        sqlsrv_close( $conn);   
        sqlsrv_close( $NOTIFICACIONES_conn);   
    }

    //data Functions    
	
    function dataToSorteos($draw_id, $conn){

		//echo $draw_id;
        $limit = "";
        $draw_id_filter = "";
        
        if ($draw_id != -1) $draw_id_filter = " AND  d.id = " . $draw_id;
		//echo $draw_id_filter;
		
		$fecha = "GETDATE()";
		
        $sql = "SELECT d.id as draw_id, e.id, e.description,  e.date, e.time1, e.time2, e.drawtime1, e.drawtime2
			  FROM [events] e , draws d
			  where  e.is_active=1
				and d.event_id = e.id " . $draw_id_filter .  "
			   and e.event_type=2   and e.drawtime2<=" . $fecha . " order by e.category_id";

        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql); 

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }	

    function dataToParticipantes($draw_id, $prioridad, $conn){

        $limit = "";
        $draw_id_filter = "";
        
        if ($draw_id != -1) $draw_id_filter = " AND  draw_id = " . $draw_id;

        $sql = "   select u.doc_id, p.category_id,
				   dr.id, c.title, u.first_name, u.last_name, u.group_id, u.email, u.handicap, u.phone_number,
				   dr.draw_id, dr.user_id, dr.package_id, dr.draw_date, dr.draw_time, dr.priority , dr.score
				   from draw_requests dr, packages p, categories c, users u
				   where 
				   p.id= dr.package_id
				   and c.id = p.category_id
				   and dr.status not in ('GANADOR','PERDIDO')
				   AND ISNULL(dr.score,0)=0
				   AND ISNULL(dr.booking_id,0)=0
				   and priority = " . $prioridad . " and u.id = dr.user_id   " . $draw_id_filter . " order by priority ASC, NEWID()";

        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql);   

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}		
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }	
	
    function dataToParticipantesScored($draw_id, $prioridad, $conn){

        $limit = "";
        $draw_id_filter = "";
        
        if ($draw_id != -1) $draw_id_filter = " AND  draw_id = " . $draw_id;

        $sql = "   select u.doc_id, p.category_id,
				   dr.id, c.title, u.first_name, u.last_name, u.group_id, u.email, u.handicap, u.phone_number,
				   dr.draw_id, dr.user_id, dr.package_id, dr.draw_date, dr.draw_time, dr.priority , dr.score
				   from draw_requests dr, packages p, categories c, users u
				   where 
				   p.id= dr.package_id
				   and dr.status not in ('GANADOR','PERDIDO')				   
				   and c.id = p.category_id
				   AND ISNULL(dr.booking_id,0)=0
				   and priority = " . $prioridad . " and u.id = dr.user_id   " . $draw_id_filter . " order by priority ASC, dr.score DESC,   NEWID()";

        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql);   

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}		
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }	
	
    function dataToDrawRequest($draw_request_id, $conn){

        $limit = "";
        $drawrequest_id_filter = "";
        
        if ($draw_request_id != -1) $drawrequest_id_filter = " AND  dr.id = " . $draw_request_id;

        $sql = "   select 
				   e.description ,
				   u.doc_id,
				   dr.locator, dr.draw_address, dr.draw_instructions, dr.google_calendar_event_id,
				   dr.id, c.title, u.first_name, u.last_name, u.group_id, u.email, u.handicap, u.phone_number,
				   dr.draw_id, dr.user_id, dr.package_id, dr.draw_date, dr.draw_time, dr.priority , dr.score
				   from draw_requests dr, packages p, categories c, users u, draws d, events e
				   where 
				    d.id= draw_id
				   and d.event_id = e.id
				   and p.id= dr.package_id
				   and c.id = p.category_id
				   " . $drawrequest_id_filter . " and u.id = dr.user_id";
				   
        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql); 

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }		

	
	function dataToDrawRequestParticipants($draw_request_id, $conn){

        $limit = "";
        $drawrequest_id_filter = "";
        
        if ($draw_request_id != -1) $drawrequest_id_filter = " AND  dp.draw_request_id = " . $draw_request_id;

        $sql = "   select * from draw_players dp
				   where 1=1
				   " . $drawrequest_id_filter . "";

        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql); 

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }		


	function dataToDrawRequestAddons($draw_request_id, $conn){

         $limit = "";
        $drawrequest_id_filter = "";
        
        if ($draw_request_id != -1) $drawrequest_id_filter = " AND  da.draw_request_id = " . $draw_request_id;

        $sql = "   select * from draw_addon da
				   where 1=1
				   " . $drawrequest_id_filter . "";

        if ($this->debug) echo $sql . "<br><br>";
        $stmt = sqlsrv_query( $conn, $sql); 

		if( $stmt === false) {
			die( print_r( sqlsrv_errors(), true) );
		}
		
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }		
	
	
	function dataToBookingID($user_id, $locator, $conn){

	 $limit = "";
	$drawrequest_id_filter = "";
	
	if ($user_id != -1) $user_id_filter = " AND  user_id = " . $user_id;
	if ($locator != -1) $locator_filter = " AND  locator = '" . $locator . "'";
	

	$sql = "   select * from bookings 
			   where 1=1
			   " . $user_id_filter . $locator_filter .  "";

	if ($this->debug) echo $sql . "<br><br>";
	$stmt = sqlsrv_query( $conn, $sql); 

	if( $stmt === false) {
		die( print_r( sqlsrv_errors(), true) );
	}
	
	return $stmt;
	//return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }	
   
	
	
    function dataToCalcularScores($TotalItems, $draw_id, $conn){

        $limit = "";
        $draw_id_filter = "";
        if ($TotalItems != -1) $limit = " TOP " . $TotalItems;
        if ($draw_id != -1) $draw_id_filter = " AND  draw_id = " . $draw_id;

        //$sql = "SELECT " . $limit .  " * FROM tournament_users where status=0 AND date_confirmed is null ORDER BY id ASC ";

        $sql = "SELECT " . $limit .  " * from draw_requests where draw_id in 
        (
        select id 
        from draws 
        where event_id in 
                (select id from events 
                where event_type = 2 and  
                CONVERT(DATETIME,GETDATE()) between CONVERT(DATETIME,drawtime1) and CONVERT(DATETIME,drawtime2)
                and is_active = 1)
        and status=1
        )
        AND score <>-1 " . $draw_id_filter . " order by priority ASC, NEWID()";


        if ($this->debug) echo $sql . "<br>";
        $stmt = sqlsrv_query( $conn, $sql);    
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }


    function dataToPremiacionSorteos($TotalItems, $draw_id, $conn){

        $limit = "";
        $draw_id_filter = "";
        if ($TotalItems != -1) $limit = " TOP " . $TotalItems;
        if ($draw_id != -1) $draw_id_filter = " AND  draw_id = " . $draw_id;

        //$sql = "SELECT " . $limit .  " * FROM tournament_users where status=0 AND date_confirmed is null ORDER BY id ASC ";


        $sql = "SELECT " . $limit .  " dr.*, e.id, e.date, e.time1, e.time2, e.drawtime1, e.drawtime2, e.event_type, u.first_name, u.last_name, u.doc_id, u.email, u.phone_number
            FROM draw_requests dr, events e, draws d, users u
            WHERE  d.event_id = e.id AND dr.draw_id=d.id AND u.id=dr.user_id "
             . $draw_id_filter 
             . " order by  NEWID() ";
             //. " order by draw_id asc, priority asc, score asc ";

        if ($this->debug) echo $sql . "<br>";
        $stmt = sqlsrv_query( $conn, $sql);    
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }


    function dataToPurge($draw_id, $conn){

        //$limit = "";
        $draw_id_filter = "";
        //if ($TotalItems != -1) $limit = " TOP " . $TotalItems;
        if ($draw_id != -1) $draw_id_filter = " AND  draw_id = " . $draw_id;

        //$sql = "SELECT " . $limit .  " * FROM tournament_users where status=0 AND date_confirmed is null ORDER BY id ASC ";


        $sql = " UPDATE draw_requests SET scores = -1 
            WHERE scores IS NULL 
            AND  draw_id NOT IN  
            (
                /*eventos con inscripciones activas*/
                select d.id 
                from draws d, events e  
                where  e.id= d.event_id and d.status = 1 and e.is_active=1 and e.event_type = 2 
                AND CONVERT(DATETIME,GETDATE()) between CONVERT(DATETIME,e.drawtime1) and CONVERT(DATETIME,e.drawtime2)
            )
            AND draw_id  IN
            (
                /*eventos que ya se efectuaron*/
                select d.id 
                from draws d, events e  
                where  e.id= d.event_id and d.status = 1 and e.is_active=1 and e.event_type = 2 
                AND CONVERT(DATETIME,GETDATE()+1) >= CONVERT(DATETIME,e.date)
            )

            ";

             //. " order by draw_id asc, priority asc, score asc ";

        if ($this->debug) echo $sql . "<br>";
        $stmt = sqlsrv_query( $conn, $sql);    
        return $stmt;
        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;
    }




    //Funciones principales
	
	function  SorteosReset($draw_id,$conn, $NOTIFICACIONES_conn)
	{
		$tsql = "UPDATE draw_requests set score=NULL, booking_id=null, status='Procesando' WHERE draw_id=" . $draw_id . " ";
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Sorteo Reinicializado exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al actualizar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		
		sqlsrv_free_stmt( $stmtUpdate);				
		
	}
	

	function Sorteos($draw_id,$conn, $NOTIFICACIONES_conn)
    {
		
		if ($draw_id!=-1)  echo "<b><h1>Filtro Sorteo ID " . $draw_id .  "</h1></b><br>";
        $stmt = $this->dataToSorteos($draw_id, $conn); 
        

		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		  //echo $row['description']."<br />";
		  $draw_idSorteo = $row['draw_id'];
		  echo "<b><h1>Sorteo ID " . $draw_idSorteo . " - " . $row['description'] . "</h1></b><br>";
		  
			//calculando scores para cada Prioridad
			echo "<b>Calculando Scores Participantes Sorteo </b><br>" ;
		  
			for ($prioridad = 1; $prioridad <= 3; $prioridad++) {
				echo "<i>PRIORIDAD " . $prioridad ."</i><br />";
			
				$stmtSorteo = $this->dataToParticipantes($draw_idSorteo, $prioridad, $conn); 
				while( $rowSorteo = sqlsrv_fetch_array( $stmtSorteo, SQLSRV_FETCH_ASSOC) ) {
					  $doc_id=$rowSorteo['doc_id'];
					  $user_id=$rowSorteo['user_id'];
					  $category_id=$rowSorteo['category_id'];
					  
					  echo " Calculando score para " .  $doc_id;
					  $score = $this->CalcularScore($user_id,$category_id, $conn);
					  echo " - SCORE: " .  $score . "<br>";
					  $this->SetScore($score, $user_id, $category_id, $conn);
					  echo " - SET " . "<br />";
				}
			}

			//Asignando Ganadores Sorteo
			echo "<b>Asignando Ganadores Sorteo </b><br>" ;
		  
			for ($prioridad = 1; $prioridad <= 3; $prioridad++) {
				echo "PRIORIDAD " . $prioridad ."<br />";
			
				$stmtSorteo = $this->dataToParticipantesScored($draw_idSorteo, $prioridad, $conn); 
				while( $rowSorteo = sqlsrv_fetch_array( $stmtSorteo, SQLSRV_FETCH_ASSOC) ) {
					  $draw_request_id=$rowSorteo['id'];
					  $doc_id=$rowSorteo['doc_id'];
					  $user_id=$rowSorteo['user_id'];
					  $category_id=$rowSorteo['category_id'];
					  
					  $package_id=$rowSorteo['package_id'];
					  $draw_date = $rowSorteo['draw_date'];
					  $draw_time = $rowSorteo['draw_time'];
					  
					  echo " Convirtiendo en reserva <br>" ;
					  $this->ConvertDrawtoBooking ($draw_request_id, $user_id, $conn, $NOTIFICACIONES_conn , $this->winner_template);
					  
					  echo " Anulando demas peticiones del usuario para ese sorteo <br>" ;
					  $this->DisablingRequestsUser($draw_idSorteo, $user_id, $prioridad, $conn);
					  
					  //anulando reservas en dicha hora
					  echo " Anulando solicitudes sorteos del  sorteo "  . $draw_idSorteo . " el " .   $draw_date . " - " . $draw_time . "<br>";
					  $this->DisablingRequestsOtherUsers($draw_idSorteo, $user_id, $draw_date, $draw_time, $conn);
					  

				}
			}
			sqlsrv_free_stmt( $stmtSorteo);	
		}			
		sqlsrv_free_stmt( $stmt);
		
	}			
			


    
	
	
	function CrearBooking($user_id, $locator,$package_id, $booking_address, $booking_instructions, $booking_date, $booking_time, $google_calendar_event_id,  $conn)
	{
		$booking_id= -1;
		$status='Procesando';
		
		$tsql = "INSERT INTO bookings ([user_id] ,[locator],[package_id] ,[booking_address] ,[booking_instructions],[booking_date] ,[booking_time] ,[google_calendar_event_id],[status] ,[created_at] ,[updated_at])  VALUES  " .
			"(" . 
				  "" .  $user_id . "," .
				  "'" .  $locator . "'," .
				  "" .  $package_id . "," .
				  "'" .  $booking_address . "'," .
				  "'" .  $booking_instructions . "'," .
				  "'" .  $booking_date . "'," .
				  "'" .  $booking_time . "'," .
				  "'" .   $google_calendar_event_id . "'," .
				  "'" .  $status . "'," .
				  "" .  "GETDATE()" . "," .
				  "" .  "GETDATE()" . 
				  ")";
                     		
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Notificando GANADOR exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al insertar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		sqlsrv_free_stmt( $stmtUpdate);		
		
		
		//calcular ID
		$stmtBookingID = $this->dataToBookingID($user_id, $locator, $conn); 
		while( $rowBookingID = sqlsrv_fetch_array( $stmtBookingID, SQLSRV_FETCH_ASSOC) ) {
			  $booking_id=$rowBookingID['id'];
		}
			
		sqlsrv_free_stmt( $stmtBookingID);		
		
		return $booking_id;
	}	
	
	function CrearBookingPlayer($booking_id, $doc_id, $player_type, $token, $draw_id,$conn)
	{
		$tsql = "INSERT INTO booking_players ([booking_id] ,[doc_id],[player_type] ,[confirmed] ,[confirmed_at],[token] ,[created_at] ,[updated_at])  VALUES  " .
			"(" . 
				  "" .  $booking_id . "," .
				  "'" .  $doc_id . "'," .
				  "" .  $player_type . ",0,NULL," .
				  "'" .  $token . "'," .
				  "" .  "GETDATE()" . "," .
				  "" .  "GETDATE()" . 
				  ")";
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Convirtiendo Player exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al insertar el registro</font><br>";     
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		sqlsrv_free_stmt( $stmtUpdate);		

	}	
	
	function CrearBookingAddon($booking_id, $addon_id,$conn)
	{
		$tsql = "INSERT INTO addon_booking ([booking_id] ,[addon_id],[created_at] ,[updated_at])  VALUES  " .
			"(" . 
				  "" .  $booking_id . "," .
				  "" .  $addon_id . "," .
				  "" .  "GETDATE()" . "," .
				  "" .  "GETDATE()" . 
				  ")";
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Convirtiendo Addon exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al insertar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		sqlsrv_free_stmt( $stmtUpdate);		

	}		
	
	function ConvertDrawtoBooking ($draw_request_id, $user_id, $conn, $NOTIFICACIONES_conn, $winner_template)
	{
		$booking_id = -1;

		$sTorneo = "";
		$sCorreo = "";
		$sTelefono = "";
		$sAsunto = "";
		$sDestinatario = "";
		$sAccion = "";
		$sCedula = "";
		$sLink = "";		
		$sCategoria = "";		
		$sFechaHora = "";		
		$sToken = "";		
		
		
		$draw_id="";
		$user_id="";
		$locator="";
		$package_id="";
		$booking_address="";
		$booking_instructions="";
		$booking_date="";
		$booking_time="";
		$google_calendar_event_id ="";
		
		
		//obtener detalles del request
		$stmtDrawRequest = $this->dataToDrawRequest($draw_request_id,  $conn); 
		while( $rowDrawRequest = sqlsrv_fetch_array( $stmtDrawRequest, SQLSRV_FETCH_ASSOC) ) {
			  $sTorneo=$rowDrawRequest['description'];
			  $sCorreo=$rowDrawRequest['email'];
			  $sTelefono=$rowDrawRequest['phone_number'];
			  $sDestinatario=$rowDrawRequest['first_name'] . " " . $rowDrawRequest['last_name'];
			  $sAccion=$rowDrawRequest['group_id'];
			  $sCedula=$rowDrawRequest['doc_id'];
			  
			  $sCategoria=$rowDrawRequest['title'];
			  $sFechaHora=$rowDrawRequest['draw_date'] . " " . $rowDrawRequest['draw_time'];
			  
				$draw_id=$rowDrawRequest['draw_id'];  
				$user_id=$rowDrawRequest['user_id'];
				$locator=$rowDrawRequest['locator'];
				$package_id=$rowDrawRequest['package_id'];
				$booking_address=$rowDrawRequest['draw_address'];
				$booking_instructions=$rowDrawRequest['draw_instructions'];
				$booking_date=$rowDrawRequest['draw_date'];
				$booking_time=$rowDrawRequest['draw_time'];
				$google_calendar_event_id =$rowDrawRequest['google_calendar_event_id'];
				
				$sToken=$rowDrawRequest['locator'];
			  
		}
		sqlsrv_free_stmt( $stmtDrawRequest);	
		
		//crear booking
		//obtener id del booking creado
		$booking_id=$this->CrearBooking($user_id, $locator,$package_id, $booking_address, $booking_instructions, $booking_date, $booking_time, $google_calendar_event_id,  $conn);		
		
		echo "booking_id generado " . $booking_id . "<br>";
		
		//obtener detalles de los participantes
		echo "Convirtiendo Participantes <br>";
		
		$stmtDrawRequestParticipants = $this->dataToDrawRequestParticipants($draw_request_id,  $conn); 
		while( $rowDrawRequestParticipants = sqlsrv_fetch_array( $stmtDrawRequestParticipants, SQLSRV_FETCH_ASSOC) ) {
			  $player_type=$rowDrawRequestParticipants['player_type'];
			  $doc_id=$rowDrawRequestParticipants['doc_id'];
			  $token=$rowDrawRequestParticipants['token'];
			  //$draw_id=$rowDrawRequestParticipants['draw_id'];
			  
			  $this->CrearBookingPlayer($booking_id, $doc_id, $player_type, $token, $draw_id,$conn);
		}
		sqlsrv_free_stmt( $stmtDrawRequestParticipants);		
		
		//obtener detalles de los addons
		echo "Convirtiendo Addons <br>";
		
		$stmtDrawRequestAddons = $this->dataToDrawRequestAddons($draw_request_id,  $conn); 
		while( $rowDrawRequestAddons = sqlsrv_fetch_array( $stmtDrawRequestAddons, SQLSRV_FETCH_ASSOC) ) {
			  $addon_id=$rowDrawRequestAddons['addon_id'];
			  
			  $this->CrearBookingAddon($booking_id, $addon_id,$conn );
		}
		sqlsrv_free_stmt( $stmtDrawRequestAddons);		

		

		//marcar como ganador	
		$tsql = "UPDATE draw_requests set booking_id = " . $booking_id . ", status='GANADOR'  WHERE id=" . $draw_request_id . "";
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Request GANADOR Actualizado exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al insertar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		sqlsrv_free_stmt( $stmtUpdate);		
		
		

		
		//notificar al ganador
		$sFuente = "DRAW_WINNER";    
		$sAsunto = "Sorteo adjudicado " . $sTorneo;
		//$sLink = $data["sLink"];
		$nStatus = 0;
		$nTipo = 1;
		$dFecha = "GETDATE()";
		$dFechaProgramada = "GETDATE()";
		$dFechaProcesada = "NULL";
		$sArchivo = "";
		$sLink = $this->URL_Confirmacion;
		
		//var $URL_Confirmacion = "http://190.216.224.53:8084/confirmPlayer.php?token=<#TOKEN#>";

		$sContenidoParsed = $winner_template;   
		$sContenidoParsed = str_replace("<#PARTICIPANTE#>", $sDestinatario , $sContenidoParsed);
		$sContenidoParsed = str_replace("<#FECHAHORA#>", $sFechaHora , $sContenidoParsed);
		$sContenidoParsed = str_replace("<#CATEGORIA#>", $sCategoria , $sContenidoParsed);
		
		$sContenidoParsed = str_replace("<#ACCION#>", $sAccion  , $sContenidoParsed);
		$sContenidoParsed = str_replace("<#CEDULA#>", $sCedula  ,$sContenidoParsed);
		$sContenidoParsed = str_replace("<#LINKCONFIRMACION#>", $sLink  ,$sContenidoParsed);
		$sContenidoParsed = str_replace("<#TOKEN#>", $sToken  ,$sContenidoParsed);
		$sContenido = $sContenidoParsed;

		$sContenido = str_replace("'", ""  ,$sContenido);
		

		$sCuenta = "";
		$nIntentos = 0;

		$tsql = "INSERT INTO Notificaciones (sFuente,sCorreo, sTelefono, sAsunto, sDestinatario ,sAccion ,nStatus ,nTipo ,dFecha ,dFechaProgramada ,dFechaProcesada ,sArchivo ,sContenido ,sCuenta ,nIntentos )  VALUES  " .
			"(" . 
				  "'" .  $sFuente . "'," .
				  "'" .  $sCorreo . "'," .
				  "'" .  $sTelefono . "'," .
				  "'" .  $sAsunto . "'," .
				  "'" .  $sDestinatario . "'," .
				  "'" .  $sAccion . "'," .
				  "" .  $nStatus . "," .
				  "" .  $nTipo . "," .
				  "" .  $dFecha . "," .
				  "" .  $dFechaProgramada . "," .
				  "" .  $dFechaProcesada . "," .
				  "'" .  $sArchivo . "'," .
				  "'" .  $sContenido . "'," . 
				  "'" .  $sCuenta . "'," .
				  "" .  $nIntentos . "" 
			.  ")";
                     		
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($NOTIFICACIONES_conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Notificando GANADOR exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al insertar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		sqlsrv_free_stmt( $stmtUpdate);		
	
	
	
	
		
	}
	
	function SetScore($score, $user_id, $category_id, $conn)
	{
		$tsql = "UPDATE draw_requests set score=" . $score . " WHERE user_id='" . $user_id . "' and package_id in (SELECT package_id FROM packages where category_id=" . $category_id .  ")";
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Score Actualizado exitosamente<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al actualizar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		
		sqlsrv_free_stmt( $stmtUpdate);		
	}

	function DisablingRequestsUser($draw_id, $user_id, $prioridad_expect, $conn)
	{
		$tsql = "UPDATE draw_requests set status='" . "PERDIDO" . "' WHERE user_id='" . $user_id . "' and draw_id=" . $draw_id . " AND priority <>" . $prioridad_expect;
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Solicitud Anulada<br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al actualizar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		
		sqlsrv_free_stmt( $stmtUpdate);		
	}	

	function DisablingRequestsOtherUsers($draw_id, $user_id, $draw_date, $draw_time, $conn)
	{
		$tsql = "UPDATE draw_requests set status='" . "PERDIDO" . "' WHERE user_id<>'" . $user_id . "' and draw_id=" . $draw_id . " AND draw_date='" . $draw_date . "' AND draw_time='" . $draw_time  . "'";
		
		if ($this->debug) echo $tsql . "<br>";

		$stmtUpdate = sqlsrv_query($conn, $tsql);   
			
		if ( $stmtUpdate )    
		{    
			 if ($this->debug) echo "Solicitudes de otros Usuarios para "  . $draw_date . " - " . $draw_time  . " marcadas como Perdidas <br>\n";    
		}     
		else     
		{    
			 echo "<font color='red'> Error al actualizar el registro</font><br>";    
			 if ($this->debug) print_r( sqlsrv_errors(), true);
			 //die( print_r( sqlsrv_errors(), true));    
		}  
		
		sqlsrv_free_stmt( $stmtUpdate);		
	}	

	function CalcularScore($user_id, $category_id, $conn)
	{
		$score = 100;
		return $score;
	}
	
	
	
    function ConfirmarParticipante($token, $conn, $NOTIFICACIONES_conn)
    {
        $stmt = $this->dataToConfirmarParticipante($token, $conn, $NOTIFICACIONES_conn); 

        echo "Confirmando Participante " . $token .  "<br>";

        if ( $stmt )    
        {
            $row_count = sqlsrv_num_rows( $stmt) ;

            while( $data = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_BOTH ))    
            {    
                echo "id: " . $data["id"]. "<br>";

                //confirmar participante
                $tsql = "UPDATE tournament_users SET date_confirmed = GETDATE() WHERE id=" . $data["id"] . "";                 
                if ($this->debug) echo $tsql . "<br>";

                $stmtUpdate = sqlsrv_query( $conn, $tsql);    
                    
                if ( $stmtUpdate )    
                {    
                     //echo "Statement executed.<br>\n";    

                    //encolar notificacion         
                    echo "Enviando correo de Bienvenida " .  "<br>";

                    $sFuente = "TOURNAMENT_WELCOME";    
                    $sCorreo = $data["sCorreo"];
                    $sTelefono = $data["sTelefono"];
                    $sAsunto = "Bienvenido al Torneo " . $data["sTorneo"];
                    $sDestinatario = $data["sDestinatario"];
                    $sAccion = $data["sAccion"];
                    $sCedula = $data["sCedula"];
                    $sLink = $data["sLink"];
                    $nStatus = 0;
                    $nTipo = 1;
                    $dFecha = "GETDATE()";
                    $dFechaProgramada = "GETDATE()";
                    $dFechaProcesada = "NULL";
                    $sArchivo = "";

                    $sContenidoParsed = $data["template_welcome_mail"];   
                    $sContenidoParsed = str_replace("<#PARTICIPANTE#>", $sDestinatario , $sContenidoParsed);
                    $sContenidoParsed = str_replace("<#ACCION#>", $sAccion  , $sContenidoParsed);
                    $sContenidoParsed = str_replace("<#CEDULA#>", $sCedula  ,$sContenidoParsed);
                    $sContenidoParsed = str_replace("<#LINKCONFIRMACION#>", $sLink  ,$sContenidoParsed);
                    $sContenido = $sContenidoParsed;

                    $sContenido = str_replace("'", ""  ,$sContenido);
                    

                    $sCuenta = "";
                    $nIntentos = 0;

                    $tsql = "INSERT INTO Notificaciones (sFuente,sCorreo, sTelefono, sAsunto, sDestinatario ,sAccion ,nStatus ,nTipo ,dFecha ,dFechaProgramada ,dFechaProcesada ,sArchivo ,sContenido ,sCuenta ,nIntentos )  VALUES  " .
                        "(" . 
                              "'" .  $sFuente . "'," .
                              "'" .  $sCorreo . "'," .
                              "'" .  $sTelefono . "'," .
                              "'" .  $sAsunto . "'," .
                              "'" .  $sDestinatario . "'," .
                              "'" .  $sAccion . "'," .
                              "" .  $nStatus . "," .
                              "" .  $nTipo . "," .
                              "" .  $dFecha . "," .
                              "" .  $dFechaProgramada . "," .
                              "" .  $dFechaProcesada . "," .
                              "'" .  $sArchivo . "'," .
                              "'" .  $sContenido . "'," . 
                              "'" .  $sCuenta . "'," .
                              "" .  $nIntentos . "" 
                        .  ")";
                     
                    if ($this->debug) echo $tsql . "<br>";

                    $stmtInsertNotificacion = sqlsrv_query($NOTIFICACIONES_conn, $tsql);   
                        
                    if ( $stmtInsertNotificacion )    
                    {    
                         if ($this->debug) echo "Notificacion generada exitosamente<br>\n";    
                    }     
                    else     
                    {    
                         echo "<font color='red'> Error al insertar el registro</font><br>";    
                         if ($this->debug) print_r( sqlsrv_errors(), true);
                         //die( print_r( sqlsrv_errors(), true));    
                    }  
                    
                    sqlsrv_free_stmt( $stmtInsertNotificacion);




                }     
                else     
                {    
                     echo "<font color='red'> Error al actualizar el registro</font><br>";    
                     if ($this->debug) print_r( sqlsrv_errors(), true);
                     //die( print_r( sqlsrv_errors(), true));    
                }  
                
                sqlsrv_free_stmt( $stmtUpdate);

            } 
        }     
        else     
        {    
             echo "<font color='red'> Error en consulta</font><br>";    
             die( print_r( sqlsrv_errors(), true));    
        } 

        /* Free statement and connection resources. */    
        sqlsrv_free_stmt( $stmt);    
    }

    function GenerarConfirmaciones($TotalItems, $conn, $NOTIFICACIONES_conn)
    {
        $stmt = $this->dataToConfirmaciones($TotalItems,$conn, $NOTIFICACIONES_conn); 

        echo "Generando correos de Confirmacion a " . $TotalItems . " participantes". "<br>";

        if ( $stmt )    
        {
            $row_count = sqlsrv_num_rows( $stmt) ;

            while( $data = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_BOTH ))    
            {    
                echo "id: " . $data["id"]. "<br>";

                //confirmar participante
                $tsql = "UPDATE tournament_users SET status = 2 WHERE id=" . $data["id"] . "";                 
                if ($this->debug) echo $tsql . "<br>";

                $stmtUpdate = sqlsrv_query( $conn, $tsql);    
                    
                if ( $stmtUpdate )    
                {    
                     //echo "Statement executed.<br>\n";    

                    //encolar notificacion         
                    echo "Enviando correo de confirmacion " .  "<br>";

                    $sFuente = "TOURNAMENT_CONFIRMATION";    
                    $sCorreo = $data["sCorreo"];
                    $sTelefono = $data["sTelefono"];
                    $sAsunto = "Confirmacion inscripcion Torneo " . $data["sTorneo"];
                    $sDestinatario = $data["sDestinatario"];
                    $sAccion = $data["sAccion"];
                    $sCedula = $data["sCedula"];
                    $sLink = $data["sLink"];
                    $nStatus = 0;
                    $nTipo = 1;
                    $dFecha = "GETDATE()";
                    $dFechaProgramada = "GETDATE()";
                    $dFechaProcesada = "NULL";
                    $sArchivo = "";

                    //$sContenidoParsed = $data["template_welcome_mail"];   
                    $sContenidoParsed = "Favor confirme su inscripcion en el Torneo <#TORNEO#> haciendo click en el siguiente <a href='<#URL#>' target='_blank'> enlace </a>";

                    $sContenidoParsed = str_replace("<#TORNEO#>", $data["sTorneo"] , $sContenidoParsed);
                    $sContenidoParsed = str_replace("<#URL#>", $sLink , $sContenidoParsed);

                    $sContenidoParsed = str_replace("<#PARTICIPANTE#>", $sDestinatario , $sContenidoParsed);
                    $sContenidoParsed = str_replace("<#ACCION#>", $sAccion  , $sContenidoParsed);
                    $sContenidoParsed = str_replace("<#CEDULA#>", $sCedula  ,$sContenidoParsed);
                    $sContenidoParsed = str_replace("<#LINKCONFIRMACION#>", $sLink  ,$sContenidoParsed);
                    $sContenido = $sContenidoParsed;

                    $sContenido = str_replace("'", ""  ,$sContenido);

                    $sCuenta = "";
                    $nIntentos = 0;

                    $tsql = "INSERT INTO Notificaciones (sFuente,sCorreo, sTelefono, sAsunto, sDestinatario ,sAccion ,nStatus ,nTipo ,dFecha ,dFechaProgramada ,dFechaProcesada ,sArchivo ,sContenido ,sCuenta ,nIntentos )  VALUES  " .
                        "(" . 
                              "'" .  $sFuente . "'," .
                              "'" .  $sCorreo . "'," .
                              "'" .  $sTelefono . "'," .
                              "'" .  $sAsunto . "'," .
                              "'" .  $sDestinatario . "'," .
                              "'" .  $sAccion . "'," .
                              "" .  $nStatus . "," .
                              "" .  $nTipo . "," .
                              "" .  $dFecha . "," .
                              "" .  $dFechaProgramada . "," .
                              "" .  $dFechaProcesada . "," .
                              "'" .  $sArchivo . "'," .
                              "'" .  $sContenido . "'," . 
                              "'" .  $sCuenta . "'," .
                              "" .  $nIntentos . "" 
                        .  ")";
                     
                    if ($this->debug) echo $tsql . "<br>";

                    $stmtInsertNotificacion = sqlsrv_query($NOTIFICACIONES_conn, $tsql);   
                        
                    if ( $stmtInsertNotificacion )    
                    {    
                         if ($this->debug) echo "Notificacion generada exitosamente<br>\n";    
                    }     
                    else     
                    {    
                         echo "<font color='red'> Error al insertar el registro</font><br>";    
                         if ($this->debug) print_r( sqlsrv_errors(), true);
                         //die( print_r( sqlsrv_errors(), true));    
                    }  
                    
                    sqlsrv_free_stmt( $stmtInsertNotificacion);




                }     
                else     
                {    
                     echo "<font color='red'> Error al actualizar el registro</font><br>";    
                     if ($this->debug) print_r( sqlsrv_errors(), true);
                     //die( print_r( sqlsrv_errors(), true));    
                }  
                
                sqlsrv_free_stmt( $stmtUpdate);

            } 
        }     
        else     
        {    
             echo "<font color='red'> Error al ejecutar la consulta</font><br>";    
             die( print_r( sqlsrv_errors(), true));    
        } 

        /* Free statement and connection resources. */    
        sqlsrv_free_stmt( $stmt);  
    }

    function AsignarParticipantesGrupos($TotalItems, $conn)
    {
     $stmt = $this->dataToGrupos($TotalItems,$conn); 

        echo "<h1>Calculando Grupos del Torneo a " . $TotalItems . " participantes". "</h1>";

        if ( $stmt )    
        {
            $row_count = sqlsrv_num_rows( $stmt) ;

            while( $data = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_BOTH ))    
            {    
                //echo "id: " . $data["id"]. "<br>";

                //calcular Grupo
                $group_id = -1;
                $Age = $data["age"];
                $Sex = $data["gender_id"];
                $handicap = $data["handicap"];
                $handicapFVG = $data["handicapFVG"];
                $tournament_id = $data["tournament_id"];

                if ($this->USE_HANDICAP_FVG)
                {
                    if ($this->debug) echo  "<b>Usando handicap FVG </b>" . "<br>";
                    if ($this->debug) echo  "Handicap " . $handicap . " vs FVG " . $handicapFVG ;

                    $handicap = $handicapFVG;
                }

                if ($this->debug) echo  "<br><b>Valores participante </b><br>" ;                
                if ($this->debug) echo  "UserID " . $data["user_id"] . " - " .  $data["sDestinatario"] .  "<br>";  
                if ($this->debug) echo  "Handicap " . $handicap  . "<br>";                
                if ($this->debug) echo  "Edad " . $Age  . "<br>";                
                if ($this->debug) echo  "Sexo " . $Sex  . "<br>";                
                if ($this->debug) echo  "Torneo " . $tournament_id  . "<br><br>";                


                //recorro todos los grupos asignados al torneo    
                $sqlGroups = " SELECT  tg.id as tu_id,  cg.id as group_id, cg.description as group_name, cg.gender_id, cg.age_from, cg.age_to, cg.golf_handicap_from, cg.golf_handicap_to " 
                                . " from    t_category_groups__tournaments tg,   t_categories_groups cg,  tournaments t  " 
                                . " where t.id = tg.tournament_id and  cg.id = tg.t_categories_groups_id  and t.id =" . $tournament_id;

                if ($this->debug) echo "<i>" . $sqlGroups . "</i><br>";

                $stmtGroups = sqlsrv_query( $conn, $sqlGroups);    

                $found = 0;
                while( $row = sqlsrv_fetch_array( $stmtGroups, SQLSRV_FETCH_BOTH))    
                {    
                                 
                    if ($found !=1)
                    {
                        $ok = true;

                        $group_name  =  $row["group_name"];  
                        $group_id  =  $row["group_id"]; 
                        $gender_id =  $row["gender_id"]; 

                        $age_from =  $row["age_from"]; 
                        $age_to =  $row["age_to"]; 

                        $golf_handicap_from =  $row["golf_handicap_from"]; 
                        $golf_handicap_to =  $row["golf_handicap_to"]; 



                        if ($ok)
                        {
                            if ($this->debug) echo "Edades: " . $age_from . " <= " .  $Age . " <= " . $age_to . " ---> ";
                            //if ($this->debug) echo  "Edad ";

                             if (($age_from  > 0) && ($age_to  > 0))
                             {
                                if (($Age >= $age_from) && ($Age <= $age_to))
                                {
                                    $ok = true;
                                    if ($this->debug) echo  "<b><font color='#0000FF'>SI</font></b><br>";
                                }
                                else
                                {
                                    $ok = false;   
                                    if ($this->debug) echo  "<b>NO</b><br>";
                                }
                             }
                        }

                        if ($ok)
                         {

                            if ($this->debug) echo "Handicap: " . $golf_handicap_from . " <= " .  $handicap . " <= " . $golf_handicap_to . " ---> ";
                            //if ($this->debug) echo  "Handicap ";

                            if (($golf_handicap_from  > 0) && ($golf_handicap_to  > 0))
                             {
                                if (($handicap >= $golf_handicap_from) && ($handicap <= $golf_handicap_to))
                                {
                                    $ok = true;
                                    if ($this->debug) echo  "<b><font color='#0000FF'>SI</font></b><br>";
                                }
                                else
                                {
                                    $ok = false;   
                                    if ($this->debug) echo  " <b>NO</b><br>";
                                }                                
                             }                            
                         }

                         if ($ok)
                         {

                            if ($this->debug) echo "Sexo: " . $gender_id . " = " .  $Sex . " ---> ";
                            //if ($this->debug) echo  "Sexo ";

                            if (($gender_id  > 0))
                             {
                                if (($gender_id == $Sex))
                                {
                                    $ok = true;
                                    if ($this->debug) echo  "<b><font color='#0000FF'>SI</font></b><br>";
                                }
                                else
                                {
                                    $ok = false;   
                                    if ($this->debug) echo  " <b>NO</b><br>";
                                }
                             }                            
                         }

                        if ($this->debug) echo  "<h2>RESULTADO GRUPO " .  $group_name ;

                        if (($ok))
                        {
                            if ($this->debug) echo  "<b><font color='#0000FF'> SI</font></b><br>";
                        }
                        else
                        {
                            if ($this->debug) echo  "<b> NO</b><br>";
                        }
                        if ($this->debug) echo  "</h2>";


                         if ($ok)  
                         {
                            $found = 1;   
                            $group_id_asignar = $group_id ;

                            if ($this->debug)   "Asignando al registro " .  $row["tu_id"] . " - user_id "  . $data["user_id"] . " - " . $data["sDestinatario"]  . " el grupo " . $group_id_asignar . " - " . $group_name . "<br>";         
                         }
                         else
                         {
                            if ($this->debug)   "El registro " .  $row["tu_id"] . " no se puede asignar a ningun grupo " . "<br>";           
                         }



                    }
                    
                    
                } 
                sqlsrv_free_stmt( $stmtGroups); 
                
                if ($found==0) $group_id_asignar = -1;

                //actualizar grupo del  participante
                $tsql = "UPDATE tournament_users SET t_categories_groups_id =" . $group_id_asignar . " WHERE  user_id=" . $data["user_id"] . " AND  t_categories_groups_id IS NULL";                 
                if ($this->debug) echo "<i>" . $tsql . "</i><br>";

                $stmtUpdate = sqlsrv_query( $conn, $tsql);    
                    
                if ( $stmtUpdate )    
                {    
                     //echo "Statement executed.<br>\n";    
                    if ($this->debug) echo  "Asignando Grupo " . $group_id_asignar . " a participante " . $data["sDestinatario"];
                }     
                else     
                {    
                     echo "<font color='red'> Error al actualizar el registro</font><br>";    
                     if ($this->debug) print_r( sqlsrv_errors(), true);
                     //die( print_r( sqlsrv_errors(), true));    
                }  
                
                sqlsrv_free_stmt( $stmtUpdate);

            } 
        }     
        else     
        {    
             echo "<font color='red'> Error al ejecutar consulta</font><br>";    
             die( print_r( sqlsrv_errors(), true));    
        } 

        /* Free statement and connection resources. */    
        sqlsrv_free_stmt( $stmt);  
    }     




    

/*
    function buildHTMLMessage(){
        ob_start();
        $data = $this->dataToMail(); 
        include(dirname(__DIR__)."/views/htmlMail.php");
        $pngString = ob_get_contents();
        ob_end_clean();
        return $pngString;
    }


    function sendEmail(){
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $cabeceras .= 'From: Tickets Abiertos <webmaster@avaytec.com>' . "\r\n" ;
		//$cabeceras .= 'From: Tickets Abiertos <webmaster@avaytec.com>' . "\r\n" . "CC: nortiz@avaytec.com";
		$date = date('m/d/Y h:i:s a', time());
        $para = "rechacon@avaytec.com, laraneo@gmail.com, nortiz@avaytec.com";
        $titulo = "Tickets Abiertos " . $date;
        $mensaje = $this->buildHTMLMessage();
		
		echo date('m/d/Y h:i:s a', time()) . "<br>";
		echo $mensaje . "<br>";
		
        return  mail($para, $titulo, $mensaje, $cabeceras);
    }
*/

}



new Play();