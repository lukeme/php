<?php
$dir = 'd:/test'
$content = file_get_contents('t.html');

preg_match_all('#<img\s* src="([\:/\.\w]+)"#', $content, $matches);
print_r($matches);

foreach ($matches[1] as $match) {
    $name = str_replace('/', '_', substr($match, strpos($match, 'mblogpic') + 9));
    if (!file_exists($dir. $name)) {
        $file = file_get_contents($match);
        file_put_contents($dir . $name, $file);
    }else{
        $content = str_replace($match, $name, $content);
    }
}


file_put_contents('t2.html', $content);
