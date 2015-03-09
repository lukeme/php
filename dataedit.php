<?php

header('Content-type:text/html;charset="utf-8"');
if (isset($_GET['pass']) && '777' == trim($_GET['pass'])) {
    $pattern = '/(return array\(\s*.*\);)/isU';
    $content = file_get_contents(__FILE__);
    $data = isset($_POST['c']) ? trim($_POST['c']) : '';
    $data = get_magic_quotes_gpc() ? stripslashes($data) : $data;

    if ($data) {
        $r = preg_replace($pattern, $data, $content);
        if ($new = preg_replace('/(return array\(\s*.*\);)/isU', $data, $content)) {
            file_put_contents(__FILE__, $new);
            die('<script>alert("ok");</script>');
        }
    }else if(preg_match($pattern, $content, $matches)) {
        if ($matches[0]) {
            $config = $matches[0];
            $html = '<form action="?pass=' . $_GET['pass'] . '" method="post"><textarea name="c" cols="100" rows="10">' . $config . '</textarea><br/><button type="submit">submit</button><form>';
            die($html);
        }
    }
}else{
    die('<a href="?pass=">请输入密码</a>');
}
//  下面的数组是要编辑的内容
return array('2015-02-09,2015-02-15' => array('aaa', 'ddd'));
