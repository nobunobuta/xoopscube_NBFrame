<?php
if (!class_exists('SimpleBlock')) {
    class SimpleBlock {
        function edit($dirname, $option) {
        }
        
        function show($dirname, $option){
            $block['content'] = 'Hello World';
            return $block;
        }
    }
}
