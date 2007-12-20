<?php
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
