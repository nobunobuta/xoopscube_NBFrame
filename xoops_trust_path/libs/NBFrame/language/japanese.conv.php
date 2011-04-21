<?php
if (!class_exists('NBFrame_japanese_LanguageAdapter')) {
    class NBFrame_japanese_LanguageAdapter extends NBFrameLanguageAdapter
    {
        function altLang() {
            return "ja-utf8";
        }
        function convert($str) {
            return mb_convert_encoding($str, 'EUC-JP', 'UTF-8');
        }
    }
}
?>
