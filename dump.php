<?php

//调试打印php变量
function dump() {
    static $i = 1;
    $args = func_get_args();
    echo '<pre>';
    echo '<b>' . $i++ . '、 </b>' . print_r($args[0], true);
    echo '</pre>';
}

?>
