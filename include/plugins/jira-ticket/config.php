<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class JiraConfig extends PluginConfig {
    function getOptions() {
        return array(
            'msad' => new SectionBreakField(array(
                'label' => 'JIRA Plugin Settings',
                'hint' => '',
            )),
            'url' => new TextboxField(array(
                'label' => 'JIRA base URL',
                'hint' => 'with http(s)://',
                'configuration' => array('size'=>40, 'length'=>200),
                'validators' => array(function($self, $val) {
                	if(!preg_match('!^https?://!', $val)){
	                    $self->addError('Enter valid URL!');
                    }
                }),
            )),
            'username' => new TextboxField(array(
                'label' => 'API username',
                'hint' => '',
                'configuration' => array('size'=>20),
                'validators' => array(function($self, $val) {
                    if(empty($val)){
	                    $self->addError('Enter username!');
                    }
                }),
            )),
            
            'password' => new TextboxField(array(
                'label' => 'API password',
                'hint' => '',
                'configuration' => array('size'=>20),
                'validators' => array(function($self, $val) {
                    if(empty($val)){
	                    $self->addError('Enter password!');
                    }
                }),
            )),
        );
    }

    function pre_save($config, &$errors) {
        global $ost;
		
		$plug = new JIRAPlugin();
		$plug->bootstrap();
		
        return !$errors;
    }
}

?>
