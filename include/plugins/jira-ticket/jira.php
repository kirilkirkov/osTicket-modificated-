<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once(__DIR__.'/config.php');
require_once(__DIR__.'/lib/Httpful/Bootstrap.php');

\Httpful\Bootstrap::init();
use \Httpful\Request;

define('JIRA_TABLE', TABLE_PREFIX."jira_tickets");

class JIRAPlugin extends Plugin {
	var $config_class = 'JIRAConfig';
	
	static $JIRA_URL;
	static $JIRA_username;
	static $JIRA_password;
	
	function __construct(){
		$res = db_query("SELECT * FROM ".TABLE_PREFIX."plugin WHERE install_path='plugins/jira-ticket' AND isactive=1 limit 1 ");
		$tickets = db_assoc_array($res,MYSQLI_ASSOC);
		parent::__construct($tickets[0]['id']?$tickets[0]['id']:0);
	}
	
	function bootstrap() {
		$this->loadConfig();
		
		// check table installed!
		if(db_num_rows(db_query("SHOW TABLES LIKE '".JIRA_TABLE."'"))<1) {
			db_query("CREATE TABLE IF NOT EXISTS `".JIRA_TABLE."` (
				  `ticket_id` int(10) NOT NULL DEFAULT '0',
				  `jira_ticket_id` int(10) NOT NULL DEFAULT '0',
				  `jira_ticket_key` varchar(10) NOT NULL DEFAULT '',
				  UNIQUE KEY `tick_to_jira` (`ticket_id`,`jira_ticket_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			");
		}
		
		// Attach to the AJAX call
		Signal::connect('ajax.scp', function($dispatcher, $data) {
		    $dispatcher->append(
		        url('^/jira/', patterns('jira.php:JIRAPlugin',
	        		url_get('^(?P<tid>\d+)/create$', 'createForm'),
	        		url_post('^(?P<tid>\d+)/create$', 'createTicket'),
	        		url_get('^(?P<tid>\d+)/add$', 'addForm'),
	        		url_post('^(?P<tid>\d+)/add$', 'addTicket'),
	        		url_get('^apiCall(.*)$', 'doCallGet')
	        	))
		    );
		});
	}
	
	function doCallGet($url){
		$data = self::apiRequest('get',$url);
		echo json_encode($data);
	}
	
	public function loadConfig(){
		$config = $this->getConfig();
		
		self::$JIRA_URL = $config->get('url');
		self::$JIRA_username = $config->get('username');
		self::$JIRA_password = $config->get('password');
	}
	
	
	public function createForm($ticketID=''){
		header('Content-type: text/html; charset=UTF-8;');
		
		$ticket=Ticket::lookup($ticketID);
		
		$lastMessage = $ticket->getLastMessage();
		$lastMessageBody = '';
		if($lastMessage->ht['format'] == 'text'){
			$lastMessageBody = Format::htmlchars($lastMessage->ht['body'], $lastMessage->ht['format']);
		}else{
			$lastMessageBody = Format::html2text($lastMessage);
		}
		
		$metadata = self::apiRequest('get','/issue/createmeta');
		$projects = $metadata->projects;
		
		$issue_types = array();
		$proj_keys = array();
		foreach($projects as $project){
			$proj_keys[] = $project->key;
			foreach($project->issuetypes as $issue){
				$issue_types[$project->id][] = array('id'=>$issue->id, 'name'=>$issue->name);
			}
		}
		
		$priorities = self::apiRequest('get','/priority');
		$users = self::apiRequest('get','/user/assignable/multiProjectSearch?projectKeys='.join(',',$proj_keys));
		
		ob_start();
		include(dirname(__FILE__).'/templates/createForm.tmpl.php');
		$resp = ob_get_contents();
        ob_end_clean();
        
        return $resp;
	}
	
	public function createTicket($ticketID=''){
		global $thisstaff;
		$postData= array(
			'fields'=>array(
				'project'=>array('id'=>$_POST['project']),
				'summary'=>$_POST['subject'],
				'description'=>$_POST['description'],
				'issuetype'=>array("id"=>$_POST['issuetype']),
				'priority'=>array("id"=>$_POST['priority']),
				'assignee'=>array('name'=>$_POST['assignee']),
				'reporter'=>array('name'=>$thisstaff->getUserName()),
			)
		);
		if($_POST['duedate'] && strtotime($_POST['duedate'])>0){
			$postData['fields']['duedate'] = date('Y-m-d', strtotime($_POST['duedate']));
		}
		
		$response = self::apiRequest('post','/issue', $postData);
		
		if(!empty($response->errors)){
			echo(''.$this->trackURL.'/'.$this->trackProject.'/ticket/'.$r_data['ticketID']);
			echo '<div class="error-banner">'.join('<br />',(array)$response->errors).join('<br />',(array)$issue->errorMessages).'</div>';
			header('HTTP/1.1 288 See Other', true, 288);
		}else{			
			db_query("REPLACE INTO ".JIRA_TABLE." (ticket_id, jira_ticket_id, jira_ticket_key) VALUES ('".$_POST['ticket_id']."','".$response->id."','".$response->key."')");
						
			echo(self::$JIRA_URL.'/browse/'.$response->key);
			
			// delete creator
			self::apiRequest('delete','/issue/'.$response->key.'/watchers?username='.self::$JIRA_username);
			// add creator as watcher
			self::apiRequest('post','/issue/'.$response->key.'/watchers',null,$thisstaff->getUserName());
			
			header('HTTP/1.1 299 See Other', true, 299);
		}
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
		if(preg_match("#http.*?/browse/(\w*-\d*)/?#",$tNum,$match)){
			$tNum = $match[1];
		}
		
		$issue = self::apiRequest('get','/issue/'.$tNum);

		if($issue->id>0){
			db_query("REPLACE INTO ".JIRA_TABLE." (ticket_id, jira_ticket_id, jira_ticket_key) VALUES ('".$_POST['ticket_id']."','".$issue->id."','".$issue->key."')");
		}else{
                    	echo '<div class="error-banner">'.join('<br />',(array)$issue->errors).join('<br />',(array)$issue->errorMessages).'</div>';
			header('HTTP/1.1 288 See Other', true, 288);
		}
	}
	
	
	
	
	
	
	
	
	/* Static functions ...*/
	public static function loadStaticConfig(){
		$obj = new JIRAPlugin();
		$config = $obj->getConfig();
		
		self::$JIRA_URL = $config->get('url');
		self::$JIRA_username = $config->get('username');
		self::$JIRA_password = $config->get('password');
	}
	
	public static function ticketButton($ticketID){
		ob_start();
		include(__DIR__.'/templates/ticketButton.tmpl.php');
		$resp = ob_get_contents();
        ob_end_clean();
        
        return $resp;
	}
	
	static function apiRequest($requestType, $requestPath, $requestData=array(), $watcherHack = false){
		self::loadStaticConfig();
		$requestURL = rtrim(self::$JIRA_URL,'/').'/rest/api/2'.$requestPath;
		
		try{
			$response = Request::$requestType($requestURL)
				->authenticateWith(self::$JIRA_username, self::$JIRA_password);
			
			if(!empty($requestData)){
				$response = $response->sendsJson()->body(json_encode($requestData));
			}
			if($watcherHack){
				$response = $response->body('"'.$watcherHack.'"');
			}
			$response = $response->send();
		}catch(Exception $e){
			var_dump($e->getMessage());
		}
		
		return $response->body;
	}
	
	public static function GetTicket($ticketID){
		self::loadStaticConfig();
		
		$res = db_query("SELECT * FROM ".JIRA_TABLE." WHERE ticket_id='".$ticketID."' ");
		$tickets = db_assoc_array($res,MYSQLI_ASSOC);
		
		if(!db_num_rows($res) || empty($tickets)){
			return false;
		}
		
		foreach($tickets as $ticket){
			$url = self::$JIRA_URL.'/browse/'.$ticket['jira_ticket_key'];
			?>
			<span style="padding: 0 2px; font-weight:bold;">
				<a href="<?=$url?>" style="color: red" target="_blank" title=""><?=$ticket['jira_ticket_key']?></a>
			</span>
			<?php
		}
	}
}