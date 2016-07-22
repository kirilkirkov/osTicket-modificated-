<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class TracPlugin extends Plugin {
	var $config_class = 'TracConfig';
	var $trackURL;
	var $trackComunicatorUrl;
	var $trackProject;
	
	static $_trackURL;
	static $_trackComunicatorUrl;
	static $_trackProject;
	
	function __construct(){
		$res = db_query("SELECT * FROM ".TABLE_PREFIX."plugin WHERE install_path='plugins/trac-ticket' AND isactive=1 limit 1 ");
		$tickets = db_assoc_array($res,MYSQLI_ASSOC);
		parent::__construct($tickets[0]['id']?$tickets[0]['id']:0);
	}
	
	function bootstrap() {
		$this->loadConfig();
		
		// check table installed!
		if(db_num_rows(db_query("SHOW TABLES LIKE '".TABLE_PREFIX."trac_tickets'"))<1) {
			db_query("CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."trac_tickets` (
				  `ticket_id` int(10) NOT NULL DEFAULT '0',
				  `trac_ticket_id` int(10) NOT NULL DEFAULT '0',
				  UNIQUE KEY `tick_to_tick` (`ticket_id`,`trac_ticket_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
				
		// Attach to the AJAX call
		Signal::connect('ajax.scp', function($dispatcher, $data) {
		    $dispatcher->append(
		        url('^/trac/', patterns('trac.php:TracPlugin',
	        		url_get('^(?P<tid>\d+)/create$', 'createForm'),
	        		url_post('^(?P<tid>\d+)/create$', 'createTicket'),
	        		url_get('^(?P<tid>\d+)/add$', 'addForm'),
	        		url_post('^(?P<tid>\d+)/add$', 'addTicket')
	        	))
		    );
		});
		
	}
	
	public function loadConfig(){
		$config = $this->getConfig();
		
		$this->trackURL = $config->get('url');
		$this->trackComunicatorUrl = $config->get('comunicator');
		$this->trackProject = $config->get('project');
	}
	public static function loadStaticConfig(){
		$obj = new TracPlugin();
		$config = $obj->getConfig();
		
		self::$_trackURL = $config->get('url');
		self::$_trackComunicatorUrl = $config->get('comunicator');
		self::$_trackProject = $config->get('project');
	}
	
	public static function tracTicketButton($ticketID){
		
		ob_start();
		include(dirname(__FILE__).'/templates/ticketButton.tmpl.php');
		$resp = ob_get_contents();
        ob_end_clean();
        
        return $resp;
	}
	
	public function createForm($ticketID=''){
		header('Content-type: text/html; charset=UTF-8;');
		
		$ticket=Ticket::lookup($ticketID);
		
		ob_start();
		include(dirname(__FILE__).'/templates/createForm.tmpl.php');
		$resp = ob_get_contents();
        ob_end_clean();
        
        return $resp;
	}
	
	public function createTicket($ticketID=''){
		global $thisstaff;
		
		$this->loadConfig();
		
		$postData = array(
			'action'=>'CreateTicket',
			'domain'=>$this->trackProject,
			'subject'=> base64_encode($_POST['subject']),
			'description'=> base64_encode($_POST['description']),
			'username' => $thisstaff->getUserName(),
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->trackComunicatorUrl ); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST ,true);
		curl_setopt($ch, CURLOPT_POSTFIELDS , $postData );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);
		$r_data = unserialize($response);
		if($r_data['ticketID']>0){
			db_query("REPLACE INTO ".TABLE_PREFIX."trac_tickets (ticket_id, trac_ticket_id) VALUES ('".$_POST['ticket_id']."','".$r_data['ticketID']."')");
			echo(''.$this->trackURL.'/'.$this->trackProject.'/ticket/'.$r_data['ticketID']);
			header('HTTP/1.1 299 See Other', true, 299);
		}else{
			echo 'Error creating ticket !!!!';
		}
	}
	
	public static function GetTicketTracs($ticketID){
		
		self::loadStaticConfig();
		
		$res = db_query("SELECT * FROM ".TABLE_PREFIX."trac_tickets WHERE ticket_id='".$ticketID."' ");
		$tickets = db_assoc_array($res,MYSQLI_ASSOC);
		
		if(!db_num_rows($res) || empty($tickets)){
			return false;
		}
		
		$isd = array();
		foreach($tickets as $i=>$data){
			array_push($isd, $data['trac_ticket_id']);
		}
		
		$statuses = self::GetTracStatus($isd);
		
		foreach($tickets as $ticket){
			$status = $statuses[$ticket['trac_ticket_id']]['resolution'];
			$url = self::$_trackURL.'/'.self::$_trackProject.'/ticket/'.$ticket['trac_ticket_id'];
			
			$style = 'color: red';
			if($status == 'fixed' || $status == 'duplicate'){
				$style = 'color: green; text-decoration:line-through;';
			}
			if($status == 'worksforme' || $status == 'invalid'){
				$style = 'color: orange; text-decoration:line-through;';
			}
			if($status == 'wontfix' ){
				$style = 'color: red; text-decoration:line-through;';
			}
			?>
			<span style="padding: 0 2px; font-weight:bold;">
				<a href="<?=$url?>" style="<?=$style?>" target="_blank" title="<?=$status?>">#<?=$ticket['trac_ticket_id']?></a>
			</span>
			<?php
		}
	}
	
	public static function GetTracStatus($ids = array()){
		self::loadStaticConfig();

		if(!empty($ids)){
			$postData = array(
				'action'=>'GetTicketsStatus',
				'domain'=>self::$_trackProject,
				'ids'=> base64_encode(serialize($ids)),
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, self::$_trackComunicatorUrl ); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST ,true);
			curl_setopt($ch, CURLOPT_POSTFIELDS , $postData );
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			curl_close($ch);
			
			return unserialize(base64_decode($response));
		}
		return array();
	}
	
	function addForm($ticketID = 0){
		$ticket=Ticket::lookup($ticketID);
		
		ob_start();
		include(dirname(__FILE__).'/templates/addForm.tmpl.php');
		$resp = ob_get_contents();
        ob_end_clean();
        
        return $resp;
	}
	
	function addTicket($ticketID=''){
		$tNum = $_POST['number'];
		$tNum = preg_replace("/#/", "", $tNum);
		if(preg_match("/http.*\/(\d+)$/",$tNum,$match)){
			$tNum = $match[1];
		}
		if($tNum>0){
			db_query("REPLACE INTO ost_trac_tickets (ticket_id, trac_ticket_id) VALUES ('".$ticketID."','".$tNum."')");
		}
	}
}