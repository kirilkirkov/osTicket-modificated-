<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'nagios:check', # notrans
    'version' =>        '0.1',
    'name' =>           'Nagios checker plugin',
    'author' =>         'Plamen Vasilev',
    'description' =>    'Provides a ability Nagios to check for late tickets',
    'url' =>            '#',
    'plugin' =>         'nagios.php:NagiosPlugin',
    'requires' => array( ),
);

?>
