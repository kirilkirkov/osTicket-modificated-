<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'jira:ticket',
    'version' =>        '0.1',
    'name' =>           'JIRA tickets plugin',
    'author' =>         'Plamen Vasilev',
    'description' =>    'Provides ability to create JIRA tickets',
    'url' =>            '#',
    'plugin' =>         'jira.php:JIRAPlugin',
    'requires' => 		array( ),
);

?>
