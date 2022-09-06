<?php
function assignSet($key, $val) {
    $tpl = getForbizView()->tpl;
    $tpl->assign($key, $val);
}

