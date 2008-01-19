<?php
/**
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
    $tableDef['simple']['table'] = array(
        'fields' => array(
            'id' =>        array('int(8)',       'NOT NULL', null,    'auto_increment'),
            'name' =>      array('varchar(255)', 'NOT NULL', "",      ''),
            'tel_num' =>   array('varchar(16)',  'NOT NULL', "",      ''),
            'desc' =>    array('text',         'NULL',     null,    ''),
        ),
        'primary' => 'id',
    );
?>
