<?php
    $tableDef['simple']['simple'] = array(
        'fields' => array(
            'id' =>        array('int(8)',       'NOT NULL', null,    'auto_increment'),
            'name' =>      array('varchar(255)', 'NOT NULL', "",      ''),
            'tel_num' =>   array('varchar(16)',  'NOT NULL', "",      ''),
            'desc' =>    array('text',         'NULL',     null,    ''),
        ),
        'primary' => "id",
    );
?>
