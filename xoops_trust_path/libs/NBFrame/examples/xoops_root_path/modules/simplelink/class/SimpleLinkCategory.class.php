<?php
if( ! class_exists( 'SimpleLinkCategoryHandler' ) ) {
    NBFrame::using('TreeObjectHandler');

    class SimpleLinkCategory extends NBFrameTreeObject
    {
    }

    class SimpleLinkCategoryHandler extends NBFrameTreeObjectHandler
    {
        var $mTableName = 'category';
    }
}
?>
