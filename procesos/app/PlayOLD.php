<?php 

namespace App;   

use INC\DB;

use INC\Reader;

class Play {

    var $db;

    function __construct(){

        Reader::open('inc/Settings.inc');

        $data = Reader::getConnectionArray();

        $this->db = new DB($data["HOST"], $data['PORT'],$data["DB_NAME"], $data["DB_USER"], $data["DB_PASS"]);



        if (isset($_GET['status'])){
            if($_GET['status']==1){
                print_r($this->sendEmail());
            }
            if($_GET['status']==2){
                print_r($this->sendEmail2());
            }
        } else {
            echo "Debe indicar un tipo de envio";
        }
    }

    

    function dataToMail(){

        /*$sql = "SELECT   t.ID,  t.reference, t.user_id, u.name, u.username, u.email,  m.subject, m.body, m.posted,   t.ticket_status_id,  st.title status, t.ticket_dept_id, dep.title, t.opened, t.closed, t.lastupdate

                FROM  cquko_fss_ticket_status st, cquko_fss_ticket_dept dep, cquko_users u, cquko_fss_ticket_ticket t, cquko_fss_ticket_messages m

                WHERE u.id = t.user_id

                AND t.ticket_status_id = st.id

                AND t.ticket_dept_id = dep.id

                AND m.ticket_ticket_id = t.id

                AND m.subject <>'Audit Message' AND st.title <> 'Closed'

                ORDER BY opened DESC, reference asc";
*/
				

				



      $sql = "SELECT   MAX(m.id),  m.id, t.ID,  t.reference, t.user_id, u.name, u.username, u.email,  m.subject, m.body, MAX(m.posted) posted,   t.ticket_status_id,  st.title estatus, t.ticket_dept_id, dep.title, t.opened, 		t.closed, t.lastupdate FROM  cquko_fss_ticket_status st, cquko_fss_ticket_dept dep, cquko_users u, cquko_fss_ticket_ticket t, cquko_fss_ticket_messages m WHERE u.id = t.user_id AND t.ticket_status_id = st.id AND t.ticket_dept_id = dep.id AND m.ticket_ticket_id = t.id AND m.subject <>'Audit Message' AND st.title <> 'Closed' AND st.title = 'Open' GROUP BY t.id ORDER BY opened DESC, reference asc";

				

        return $this->db->query($sql);

       

        //return $this->db->fetchAll(PDO::FETCH_ASSOC) ;

    }

    





function dataToMail2(){
      $sql = "SELECT   MAX(m.id),  m.id, t.ID,  t.reference, t.user_id, u.name, u.username, u.email,  m.subject, m.body, MAX(m.posted) posted,   t.ticket_status_id,  st.title estatus, t.ticket_dept_id, dep.title, t.opened,      t.closed, t.lastupdate FROM  cquko_fss_ticket_status st, cquko_fss_ticket_dept dep, cquko_users u, cquko_fss_ticket_ticket t, cquko_fss_ticket_messages m WHERE u.id = t.user_id AND t.ticket_status_id = st.id AND t.ticket_dept_id = dep.id AND m.ticket_ticket_id = t.id AND m.subject <>'Audit Message' AND st.title <> 'Closed' AND st.title = 'Awaiting User' GROUP BY t.id ORDER BY opened DESC, reference asc";
        return $this->db->query($sql);
    }











    function buildHTMLMessage2(){
        ob_start();
        $data = $this->dataToMail2(); 
        include(dirname(__DIR__)."/views/htmlMail.php");
        $pngString = ob_get_contents();
        ob_end_clean();
        return $pngString;
    }




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


    function sendEmail2(){
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
		
        return  mail($para, $titulo, $mensaje, $cabeceras);
    }
}



new Play();