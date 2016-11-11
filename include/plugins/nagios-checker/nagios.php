<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class NagiosPlugin extends Plugin {
    var $config_class = 'NagiosConfig';
    
    function __construct(){
		$res = db_query("SELECT * FROM ".TABLE_PREFIX."plugin WHERE install_path='plugins/nagios-checker' AND isactive=1 limit 1 ");
		$tickets = db_assoc_array($res,MYSQLI_ASSOC);
		parent::__construct($tickets[0]['id']?$tickets[0]['id']:0);
	}
	
    function bootstrap() {
    	
        $config = $this->getConfig();
        		        
        // Attach to the API call
        Signal::connect('api', function($dispatcher, $data) {
		    $dispatcher->append(
		        url('^/nagios/?', array('nagios.php:NagiosPlugin', 'check'))
		    );
		});
        
        // Add out of office template
        EmailTemplateGroup::$all_names['ticket.autoresp_offline'] = array(
       		'group'=>'ticket.user',
	   		'name'=>'New Ticket Auto-response Offline',
	   		'desc'=>'Autoresponse sent to user, if enabled, on new ticket. Only when it is not working time.'
	   	);
    }
    
    public function check($var1='', $var2='', $var3=''){
		global $ost;		
		$config = $this->getConfig();
		
		$workDays = array(
			1=>(bool)$config->get('workdays_1'),
			2=>(bool)$config->get('workdays_2'),
			3=>(bool)$config->get('workdays_3'),
			4=>(bool)$config->get('workdays_4'),
			5=>(bool)$config->get('workdays_5'),
			6=>(bool)$config->get('workdays_6'),
			7=>(bool)$config->get('workdays_7'),
		);
		
		$serverTimezone = new DateTimeZone(date_default_timezone_get());
		date_default_timezone_set('Europe/Sofia');
		$userTimezone = new DateTimeZone(date_default_timezone_get());
		
		// Is this holliday?
		$adtitional = explode("\n",$config->get('additional_rest'));
		foreach($adtitional as $row){
			$row = trim($row);
			if(strtotime($row)>0){
				if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00')) && strtotime($row) < strtotime(date('Y-m-d 23:59:59'))){
					print "OK: It is holliday!\n";
					exit(0);
				}
			}
		}
		
		// Is this extra work?
		$extraWork = false;
		$adtitional_work = explode("\n",$config->get('additional_work'));
		foreach($adtitional_work as $row){
			$row = trim($row);
			if(strtotime($row)>0){
				//echo '-->'.$row;
				if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00')) && strtotime($row) < strtotime(date('Y-m-d 23:59:59'))){
					$extraWork = true;
				}
			}
		}
		
		// Is this work day?
		$dayOfWeek = date('N');
		if(!$extraWork && !$workDays[$dayOfWeek]){
			print "OK: It is rest day!\n";
			exit(0);
		}
		
		// Is this working time?
		if(time() >= strtotime(date('Y-m-d '.$config->get('work_start')).' +'.intval($config->get('answer_timeout')).' minutes') && time() < strtotime(date('Y-m-d '.$config->get('work_end').''))){
			$sql="SELECT * FROM ost_ticket WHERE status_id=1 AND isanswered=0 AND (lastmessage>lastresponse OR lastresponse IS NULL)";
			if(($res=db_query($sql)) && db_num_rows($res)) {
				$critical=0;
				$warning=0;
				$oldest = 0;
				while($ticket=db_fetch_array($res)){
					$myDateTime = null;
					
					if($ticket['lastmessage']){
						$myDateTime = new DateTime($ticket['lastmessage'], $serverTimezone);
						$myDateTime->setTimezone($userTimezone);
					}else if($ticket['lastresponse']){
						$myDateTime = new DateTime($ticket['lastresponse'], $serverTimezone);
						$myDateTime->setTimezone($userTimezone);
					}else{
						$myDateTime = new DateTime($ticket['created'], $serverTimezone);
						$myDateTime->setTimezone($userTimezone);
					}
					//echo '#'.$ticket['number'].'-->'.$myDateTime->format('d.m.Y H:i:s U')."\n";
					if($config->get('error') && ($myDateTime->format('U')+($config->get('error')*60))<time()){
						$critical++;
					}else if($config->get('warning') && ($myDateTime->format('U')+($config->get('warning')*60))<time()){
						$warning++;
					}
					if($oldest >$myDateTime->format('U') || $oldest==0){
						$oldest = $myDateTime->format('U');
					}
				}
				$late = '';
				if($critical || $warning){
					$diff=time()-$oldest;
					$diff = abs($diff);
		        	$days = floor($diff/(60*60*24));
		        	$diff -= $days*(60*60*24);
		        	$hours = floor($diff/(60*60));
		        	$diff -= $hours*(60*60);
		        	$minutes = floor($diff/(60));
		  			$late =' late '.($days?$days.($days>1?' days ':' day '):'').($hours?$hours.($hours>0?' hours ':' hour '):'').$minutes.' min';
				}
				
				if($critical){
					print 'CRITICAL: '.constant('SYS_PREFIX').' '.$critical.' ticket'.($critical>1?'s':'').$late;
					exit(2);
				}else if($warning){
					print 'WARNING: '.constant('SYS_PREFIX').' '.$warning.' ticket'.($critical>1?'s':'').$late;
					exit(1);
				}else{
					print "OK: No problems! NOSMS\n";
					exit(0);
				}
			}else{
				print "OK: No late tickets! NOSMS\n";
				exit(0);
			}
		}else{
			print "OK: It is not working time! NOSMS\n";
			exit(0);
		}
	}
	
	public static function isWorkingTime(){
		global $ost;
				
		$obj = new NagiosPlugin();
		$config = $obj->getConfig();
		
        $workDays = array(
			1=>(bool)$config->get('workdays_1'),
			2=>(bool)$config->get('workdays_2'),
			3=>(bool)$config->get('workdays_3'),
			4=>(bool)$config->get('workdays_4'),
			5=>(bool)$config->get('workdays_5'),
			6=>(bool)$config->get('workdays_6'),
			7=>(bool)$config->get('workdays_7'),
		);
        
        // Set to BG timezone
		$serverTimezone = new DateTimeZone(date_default_timezone_get());
		date_default_timezone_set('Europe/Sofia');
		$userTimezone = new DateTimeZone(date_default_timezone_get());
		
		// Is this holliday?
		$adtitional = explode("\n",$config->get('additional_rest'));
		foreach($adtitional as $row){
			$row = trim($row);
			if(strtotime($row)>0){
				if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00')) && strtotime($row) < strtotime(date('Y-m-d 23:59:59'))){
					return false;
				}
			}
		}
		// Is this extra work?
		$extraWork = false;
		$adtitional_work = explode("\n",$config->get('additional_work'));
		foreach($adtitional_work as $row){
			$row = trim($row);
			if(strtotime($row)>0){
				if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00')) && strtotime($row) < strtotime(date('Y-m-d 23:59:59'))){
					$extraWork = true;
				}
			}
		}
		// Is this work day?
		$dayOfWeek = date('N');
		if(!$extraWork && !$workDays[$dayOfWeek]){
			return false;
		}
		// Is this working time?
		if(time() >= strtotime(date('Y-m-d '.$config->get('work_start')).' ') && time() < strtotime(date('Y-m-d '.$config->get('work_end').''))){
			return true;
		}else{
			return false;
		}
	}
	
	public static function getRestHours(){
		global $ost;
				
		$obj = new NagiosPlugin();
		$config = $obj->getConfig();
		
        $workDays = array(
			1=>(bool)$config->get('workdays_1'),
			2=>(bool)$config->get('workdays_2'),
			3=>(bool)$config->get('workdays_3'),
			4=>(bool)$config->get('workdays_4'),
			5=>(bool)$config->get('workdays_5'),
			6=>(bool)$config->get('workdays_6'),
			7=>(bool)$config->get('workdays_7'),
		);
        
        // Set to BG timezone
		$serverTimezone = new DateTimeZone(date_default_timezone_get());
		date_default_timezone_set('Europe/Sofia');
		$userTimezone = new DateTimeZone(date_default_timezone_get());
		
		// Is this holliday?
		$adtitional = explode("\n",$config->get('additional_rest'));
		$adtitional_work = explode("\n",$config->get('additional_work'));
		
		// is extra holiday
		$extraholiday = false;
		foreach($adtitional as $row){
			$row = trim($row);
			if(strtotime($row)>0){
				if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00')) && strtotime($row) < strtotime(date('Y-m-d 23:59:59'))){
					$extraholiday = true;
				}
			}
		}
		$timeDiff = 0;
		if(time() >= strtotime(date('Y-m-d '.$config->get('work_end')).' ') || time() < strtotime(date('Y-m-d '.$config->get('work_start').'')) || $extraholiday){
			$repeat = false;
			if(time() >= strtotime(date('Y-m-d '.$config->get('work_end')).' ') ){
				$nextDay = strtotime(date('Y-m-d').' +1 day');
			}else{
				$nextDay = strtotime(date('Y-m-d').'');
			}
			$itter = 0;
			// Determine next working day.
			do{
				$repeat = false;
				// is extra holiday
				foreach($adtitional as $row){
					$row = trim($row);
					if(strtotime($row)>0){
						if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00', $nextDay)) && strtotime($row) < strtotime(date('Y-m-d 23:59:59', $nextDay))){
							$repeat = true;
						}
					}
				}
				// is extra work day
				$extraWork = false;
				foreach($adtitional_work as $row){
					$row = trim($row);
					if(strtotime($row)>0){
						if(strtotime($row) >= strtotime(date('Y-m-d 00:00:00', $nextDay)) && strtotime($row) < strtotime(date('Y-m-d 23:59:59', $nextDay))){
							$extraWork = true;
							$repeat = false;
						}
					}
				}
				// Is this work day?
				$dayOfWeek = date('N', $nextDay);
				if(!$extraWork && !$workDays[$dayOfWeek]){
					$repeat = true;
				}
				if($repeat){
					$nextDay = strtotime(date('Y-m-d', $nextDay).' +1 day');
				}
				$itter++;
				if($itter>20){
					$repeat = false;
				}
			}while($repeat);
			
			if($itter<=20){
				$timeDiff = strtotime(date('Y-m-d '.$config->get('work_start').'', $nextDay)) - time();
			}
		}else{
			//echo 'Not here';
		}
		
		$diff = abs($timeDiff);
    	$days = floor($diff/(60*60*24));
    	$diff -= $days*(60*60*24);
    	$hours = floor($diff/(60*60));
    	$diff -= $hours*(60*60);
    	$minutes = floor($diff/(60));
    	
    	//echo  '-->'.$diff.'-->'.$days.'-->'.$hours.'-->'.$minutes;
    	if(REST_FORMAT == 'BG'){
    		$late = ($days?$days.($days>1?' дни ':' ден '):'').($hours?$hours.($hours>0?' часа ':' час '):'').(!$days&&!$hours&&$minutes?' 1 час':'');
    	}else{
    		$late = ($days?$days.($days>1?' days ':' day '):'').($hours?$hours.($hours>0?' hours ':' hour '):'').(!$days&&!$hours&&$minutes?' 1 hour':'');
    	}
		return $late;
	}
	
	
	
	
	
	public static function changeAutoReply(&$obj, &$tpl, &$msg){
		if(!self::isWorkingTime()){
			$msg = $tpl->getMsgTemplate('ticket.autoresp_offline');
			
			$msg = $obj->replaceVars($msg->asArray(), array(
				'resthours'=>self::getRestHours(),
			));
		}
	}
	
	public static function changeReply(&$obj, &$msg){
		global $ost;
		
		$sql ='SELECT * FROM '.TICKET_THREAD_TABLE.' WHERE ticket_id='.db_input($obj->getId()).' AND thread_type=\'M\' ORDER BY created DESC LIMIT 1'; 
		if( ($res=db_query($sql)) && db_num_rows($res)){
			$data=db_fetch_array($res);
			if($data['format'] == 'html'){
				$dataBody = $data['body'];
			}else{
				$dataBody = nl2br($data['body']);
			}
            $quote = '<blockquote type="cite" class="gmail_quote" style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">'.$dataBody.'</blockquote>';
			$msg['body'] = $obj->replaceVars($msg['body'], array(
				'quote'=>$quote,
			));
		}
	}
	
	public static function fixQuote(&$text){
    	$text = preg_replace_callback('/<blockquote(.*?)>/',function ($matches) {
    		$id = uniqid();
            return '<a href="#" onclick="$(\'#quote_'.$id.'\').toggle(); return false;">> Show quoted text</a><blockquote id="quote_'.$id.'" '.$matches[1].' style="display:none; border:1px solid #999; margin:0; padding-left:1ex">';
        },$text);
	}
	
}
