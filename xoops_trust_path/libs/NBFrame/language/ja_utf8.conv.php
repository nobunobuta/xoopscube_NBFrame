<?php
if (!class_exists('NBFrame_ja_utf8_LanguageAdapter')) {
    class NBFrame_ja_utf8_LanguageAdapter extends NBFrameLanguageAdapter
    {
        function altLang() {
            return "japanese";
        }
        function convert($str) {
            return mb_convert_encoding($str, 'UTF-8', 'EUC-JP');
        }
    }
}
?>
