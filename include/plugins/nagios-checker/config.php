<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class NagiosConfig extends PluginConfig {
    function getOptions() {
        return array(
            'msad' => new SectionBreakField(array(
                'label' => 'Nagios Plugin Settings',
                'hint' => '',
            )),
            'warning' => new TextboxField(array(
                'label' => 'Rise WARNING after',
                'hint' => 'minutes',
                'configuration' => array('size'=>10, 'length'=>10),
                'validators' => array(
                function($self, $val) {
                    if(!is_numeric($val)){
	                    $self->addError('Enter number!');
                    }
                }),
            )),
            'error' => new TextboxField(array(
                'label' => 'Rise ERROR after',
                'hint' => 'minutes',
                'configuration' => array('size'=>10, 'length'=>10),
                'validators' => array(
                function($self, $val) {
                    if(!is_numeric($val)){
	                    $self->addError('Enter number!');
                    }
                }),
            )),
            
            'workdays_1' => new BooleanField(array(
                'label' => 'Working days',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Monday'
                )
            )),
            'workdays_2' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Tuesday'
                )
            )),
            'workdays_3' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Wednesday'
                )
            )),
            'workdays_4' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Thursday'
                )
            )),
            'workdays_5' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Friday'
                )
            )),
            'workdays_6' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Saturday'
                )
            )),
            'workdays_7' => new BooleanField(array(
                'label' => '',
                'default' => false,
                'configuration' => array(
                    'desc' => 'Sunday'
                )
            )),
            'work_start' => new TextboxField(array(
                'label' => 'Working time from',
                'hint' => 'ex. 08:30',
                'configuration' => array('size'=>10, 'length'=>10),
                'validators' => array(
                function($self, $val) {
                    if(!preg_match('/\d?\d:\d\d/', $val)){
	                    $self->addError('Enter time!');
                    }
                }),
            )),
            'work_end' => new TextboxField(array(
                'label' => 'Working time to',
                'hint' => 'ex. 17:00',
                'configuration' => array('size'=>10, 'length'=>10),
                'validators' => array(
                function($self, $val) {
                    if(!preg_match('/\d?\d:\d\d/', $val)){
	                    $self->addError('Enter time!');
                    }
                }),
            )),
            
            'answer_timeout' => new TextboxField(array(
                'label' => 'Tickets ansuer timeout',
                'hint' => '(minutes) allow stuff to ansuer them. ',
                'configuration' => array('size'=>10, 'length'=>10),
                'validators' => array(
                function($self, $val) {
                    if(!is_numeric($val)){
	                    $self->addError('Enter number!');
                    }
                }),
            )),
            'additional_work' => new TextareaField(array(
                'id' => 'additional_work',
                'label' => 'Additional work days',
                'configuration' => array('html'=>false, 'rows'=>5, 'cols'=>40),
                'hint' => 'Enter one date per line. Format MM/DD/YYYY or YYYY/MM/DD or DD.MM.YYYY',
            )),
            'additional_rest' => new TextareaField(array(
                'id' => 'additional_rest',
                'label' => 'Additional rest days',
                'configuration' => array('html'=>false, 'rows'=>5, 'cols'=>40),
                'hint' => 'Enter one date per line. Format MM/DD/YYYY or YYYY/MM/DD or DD.MM.YYYY',
            )),
        );
    }

    function pre_save($config, &$errors) {
        global $ost;

        return !$errors;
    }
}

?>
