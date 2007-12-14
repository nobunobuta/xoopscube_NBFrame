<?php
if (!class_exists('simpleblock')) {
    class simpleblock {
        function edit($dirname, $option) {
        }
        
        function show($dirname, $option){
            $block['content'] = 'Hello World';
            return $block;
        }
    }
}
