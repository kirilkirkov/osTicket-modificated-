<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class TracConfig extends PluginConfig {
    function getOptions() {
        return array(
            'msad' => new SectionBreakField(array(
                'label' => 'Trac Plugin Settings',
                'hint' => '',
            )),
            'url' => new TextboxField(array(
                'label' => 'Trac URL',
                'hint' => 'with http(s)://',
                'configuration' => array('size'=>40, 'length'=>200),
                'validators' => array(
                function($self, $val) {
                	if(!preg_match('!^https?://!', $val)){
	                    $self->addError('Enter valid URL!');
                    }
                }),
            )),
            'project' => new TextboxField(array(
                'label' => 'Trac project',
                'hint' => '',
                'configuration' => array('size'=>20),
                'validators' => array(
                function($self, $val) {
                    if(empty($val)){
	                    $self->addError('Enter project!');
                    }
                }),
            )),
            
            'comunicator' => new TextboxField(array(
                'label' => 'Comunicator URL',
                'hint' => 'api access',
                'configuration' => array('size'=>40, 'length'=>200),
                'validators' => array(
                function($self, $val) {
                    if(!preg_match('!^https?://!', $val)){
	                    $self->addError('Enter valid URL!');
                    }
                }),
            )),
        );
    }

    function pre_save($config, &$errors) {
        global $ost;
		//$config['installed'] = true;
		
        return !$errors;
    }
}

?>
