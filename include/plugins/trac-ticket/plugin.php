<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'trac:ticket',
    'version' =>        '0.1',
    'name' =>           'Trac tickets plugin',
    'author' =>         'Plamen Vasilev',
    'description' =>    'Provides ability to create trac tickets',
    'url' =>            '#',
    'plugin' =>         'trac.php:TracPlugin',
    'requires' => array( ),
);

?>
