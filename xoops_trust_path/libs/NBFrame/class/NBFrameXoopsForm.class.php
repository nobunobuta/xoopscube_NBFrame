<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameXoopsForm')) {
    class NBFrameXoopsForm extends XoopsThemeForm
    {
        function assign(&$tpl){
            $i = 0;
            $elements = array();
            foreach ( $this->getElements() as $ele ) {
                $n = ($ele->getName() != "") ? $ele->getName() : $i;
                $elements[$n]['name']     = $ele->getName();
                $elements[$n]['caption']  = $ele->getCaption();
                $elements[$n]['body']     = $ele->render();
                $elements[$n]['hidden']   = $ele->isHidden();
                if ($ele->getDescription() != '') {
                    $elements[$n]['description']  = $ele->getDescription();
                }
                if (method_exists($ele,'getOptions')) {
                    $elements[$n]['options']   = $ele->getOptions();
                }
                $elements[$n]['object']   = $ele;
                $i++;
            }
            $js = $this->renderValidationJS();
            $tpl->assign($this->getName(), array('title' => $this->getTitle(), 'name' => $this->getName(), 'action' => $this->getAction(),  'method' => $this->getMethod(), 'extra' => 'onsubmit="return xoopsFormValidate_'.$this->getName().'();"'.$this->getExtra(), 'javascript' => $js, 'elements' => $elements));
        }
    }
}
?>
