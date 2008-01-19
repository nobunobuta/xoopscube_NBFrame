<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('SimpleBlock')) {
    class SimpleBlock {
        function edit($environment, $option) {
        }
        
        function show($environment, $option){
            $block['content'] = 'Hello World';
            return $block;
        }
    }
}
