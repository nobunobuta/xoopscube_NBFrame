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
    $tableDef['simplelink']['link'] = array(
        'fields' => array(
            'link_id' =>          array('int(8)',       'NOT NULL', null,    'auto_increment'),
            'link_name' =>        array('varchar(255)', 'NOT NULL', "",      ''),
            'link_url' =>         array('varchar(255)', 'NOT NULL', "",      ''),
            'link_desc' =>        array('text',         'NULL',     null,    ''),
            'link_category_id' => array('int(8)',       'NOT NULL', null,    ''),
            'link_weight' =>      array('int(8)',       'NOT NULL', "0",     ''),
        ),
        'primary' => 'link_id',
    );
    $tableDef['simplelink']['category'] = array(
        'fields' => array(
            'category_id' =>        array('int(8)',       'NOT NULL', null, 'auto_increment'),
            'category_name' =>      array('varchar(255)', 'NOT NULL', "",   ''),
            'category_desc' =>      array('text',         'NULL',     null, ''),
            'category_parent_id' => array('int(8)',       'NOT NULL', "0",  ''),
            'category_weight' =>    array('int(8)',       'NOT NULL', "0",     ''),
        ),
        'primary' => 'category_id',
    );
?>
