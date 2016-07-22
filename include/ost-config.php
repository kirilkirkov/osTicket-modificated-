<?php
/*********************************************************************
    ost-config.php

    Static osTicket configuration file. Mainly useful for mysql login info.
    Created during installation process and shouldn't change even on upgrades.
   
    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/

#Disable direct access.
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__)) || !defined('ROOT_PATH')) die('kwaheri rafiki!');

#Install flag
define('OSTINSTALLED',TRUE);
if(OSTINSTALLED!=TRUE){
    if(!file_exists(ROOT_PATH.'setup/install.php')) die('Error: Contact system admin.'); //Something is really wrong!
    //Invoke the installer.
    header('Location: '.ROOT_PATH.'setup/install.php');
    exit;
}

$domain = $_SERVER['SERVER_NAME'];
if(!$domain){
	// cron script ..
	// the domain must be defined !!!! 
	$domain = $_SERVER['argv'][1];
}

ini_set('memory_limit', '256M');

// Multidomain configuration !!!
switch($domain){
	case 'support.inv.bg':
		# Encrypt/Decrypt secret key - randomly generated during installation.
		define('SECRET_SALT','3DFA3FB35A4115E');
		
		#Default admin email. Used only on db connection issues and related alerts.
		define('ADMIN_EMAIL','anatoli@inv.bg');
		
		#Mysql Login info
		define('DBTYPE','mysql');
		define('DBHOST','localhost'); 
		define('DBNAME','osticket');
		define('DBUSER','dbosticket');
		define('DBPASS','oip23lkaj89');
		
		define('LOGO','images/inv_logo.png');
		define('SYS_PREFIX','inv');
		define('REST_FORMAT','BG');
	break;
	case 'support.penkov-markov.eu':
		# LEGA OsTicket
		
		# Encrypt/Decrypt secret key - randomly generated during installation.
		define('SECRET_SALT','2A1A061D0FA47B3');
		#Default admin email. Used only on db connection issues and related alerts.
		define('ADMIN_EMAIL','wdc-webteam@inv.bg');
		#Mysql Login info
		define('DBTYPE','mysql');
		define('DBHOST','localhost'); 
		define('DBNAME','legasupport');
		define('DBUSER','legasupport');
		define('DBPASS','legasupport#pass');
		
		define('LOGO','images/lega_logo.png');
		define('SYS_PREFIX','lega');
		define('REST_FORMAT','BG');
	break;
	case 'webteam.dev.avn.com':
		# AVN DEVVV OsTicket
		
		# Encrypt/Decrypt secret key - randomly generated during installation.
		define('SECRET_SALT','2A1A061D0FA47B3');
		#Default admin email. Used only on db connection issues and related alerts.
		define('ADMIN_EMAIL','plamen@inv.bg');
		#Mysql Login info
		define('DBTYPE','mysql');
		define('DBHOST','localhost'); 
		define('DBNAME','avn_tickets_dev');
		define('DBUSER','avn_tickets');
		define('DBPASS','BUizdKo5Lr5r');
		
		define('LOGO','images/avn_logo.png');
		define('SYS_PREFIX','avn');
		define('REST_FORMAT','EN');
	break;
	case 'webteam.avn.com':
		# AVN OsTicket
		
		# Encrypt/Decrypt secret key - randomly generated during installation.
		define('SECRET_SALT','2A1A061D0FA47B3');
		#Default admin email. Used only on db connection issues and related alerts.
		define('ADMIN_EMAIL','wdc-webteam@inv.bg');
		#Mysql Login info
		define('DBTYPE','mysql');
		define('DBHOST','localhost'); 
		define('DBNAME','avn_tickets');
		define('DBUSER','avn_tickets');
		define('DBPASS','BUizdKo5Lr5r');
		
		define('LOGO','images/avn_logo.png');
		define('SYS_PREFIX','avn');
		define('REST_FORMAT','EN');
	break;
	
	case 'support.ucdn.com':
		# LEGA OsTicket
		
		# Encrypt/Decrypt secret key - randomly generated during installation.
		define('SECRET_SALT','2A1A061D0FA47B3');
		#Default admin email. Used only on db connection issues and related alerts.
		define('ADMIN_EMAIL','dev@ucdn.com');
		#Mysql Login info
		define('DBTYPE','mysql');
		define('DBHOST','localhost'); 
		define('DBNAME','support');
		define('DBUSER','support');
		define('DBPASS','E1o4DavwKz');
		
		define('LOGO','images/ucdn_logo.png');
		define('SYS_PREFIX','cdn');
		define('REST_FORMAT','EN');
	break;
	
	default :
		echo 'The OsTicket configuration cannot be loaded !!!!!';
		die;
	break;
}

mb_internal_encoding('UTF-8');
#error_reporting(E_ALL & ~(E_NOTICE | E_WARNING | E_STRICT | E_DEPRECATED));

#Table prefix
define('TABLE_PREFIX','ost_');
?>
