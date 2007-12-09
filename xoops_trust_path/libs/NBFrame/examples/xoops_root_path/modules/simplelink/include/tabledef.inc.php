<?php
    $tableDef['simplelink']['link'] = array(
        'fields' => array(
            'id' =>        array('int(8)',       'NOT NULL', null,    'auto_increment'),
            'name' =>      array('varchar(255)', 'NOT NULL', "",      ''),
            'url' =>       array('varchar(255)', 'NOT NULL', "",      ''),
            'desc' =>      array('text',         'NULL',     null,    ''),
            'weight' =>    array('int(8)',       'NOT NULL', "0",     ''),
        ),
        'primary' => "id",
    );
?>
