<?php
#数据库配置#
$config = array(
    'host' => 'localhost',
    'port' => '3306', #数据库端口
    'user' => 'root',
    'pass' => 'vertrigo',
    'name' => 'test',
);

mysql_connect($config['host'] . ':' . $config['port'], $config['user'], $config['pass']) or die('Mysql Connect error');
mysql_select_db($config['name']);
mysql_query("set names 'utf8'");
if ($_GET['table']) {
    $fields = mysql_list_fields("test", $_GET['table']);
    $columns = mysql_num_fields($fields);
    for ($i = 0; $i < $columns; $i++) {
        $field_name = mysql_field_name($fields, $i);
        echo '<label for="' . $field_name . '"><input type="checkbox" name="' . $field_name . '" id="' . $field_name . '"/>' . $field_name . '</label>' . '<br />';
    }
    exit;
}

if ($_FILES["txtfile"]) {
    $type = strtolower(strstr($_FILES['txtfile']['name'], '.'));
    if ($type != ".txt") {
        exit('格式不对！');
    }
    $file = 'data.txt';
    move_uploaded_file($_FILES["txtfile"]["tmp_name"], $file) or die('上传出错');
    echo file_get_contents($file);
    exit;
}


if ($_GET['dotable'] && $_GET['dofields']) {
    $table = $_GET['dotable'];
    $fields = $_GET['dofields'];
    $fields = '`' . str_replace(',', '`,`', $fields) . '`';
    $file = 'data.txt';
    $rows = file($file);
    foreach ($rows as $i => $row) {
        $values = "'" . str_replace('|', "','", $row) . "'";
        // $name = iconv('gbk','utf-8',$arr[1]);
        $sql = "INSERT INTO {$table} ({$fields}) values ({$values})";
        $ok = mysql_query($sql);
        $msg = $ok ? '<font color="#0f0">succ</font>' : '<font color="#f00">fail when exec SQL: ' . mysql_error() . ', ' . $sql . '</font>';
        echo '<br>', ($i + 1), '.', $msg;
    }
    exit;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <title>txt2db</title>	
        <script type="text/javascript" src="http://www.helloweba.com/demo/js/jquery-1.7.2.min.js"></script>
        <style type="text/css">
            .left{width:auto; margin:50px; float: left;}
            .right{ margin:50px 0; float:left;}
            .footer{ margin:50px auto; clear:both;}
            #drop_area{width:100%; height:100px; border:3px dashed silver; line-height:100px; text-align:center; font-size:36px; color:#d3d3d3}
            #preview{width:500px; overflow:hidden}
        </style>
        <script type="text/javascript">
            $(function() {
                $("#table").change(function() {
                    var table = $("#table option:selected").val();
                    console.log($("#table option:selected").val());
                    $.get("txt2db.php", {table: table}, function(html) {
                        $("#fields").html(html);
                    });
                });

                $("#import").click(function() {
                    var table = $("#table option:selected").val();
                    var txt = $("#preview").text();
                    var fields = $("input:checkbox:checked").map(function() {
                        return $(this).attr('id');
                    }).get().join(',');
                    $.get("txt2db.php", {dotable: table, dotxt: txt, dofields: fields}, function(html) {
                        $(".footer").append(html);
                    });
                });

                //阻止浏览器默认行。
                $(document).on({
                    dragleave: function(e) {		//拖离
                        e.preventDefault();
                    },
                    drop: function(e) {			//拖后放
                        e.preventDefault();
                    },
                    dragenter: function(e) {		//拖进
                        e.preventDefault();
                    },
                    dragover: function(e) {		//拖来拖去
                        e.preventDefault();
                    }
                });

                //上传的实现
                var box = document.getElementById('drop_area'); //拖拽区域

                box.addEventListener("drop", function(e) {
                    e.preventDefault(); //取消默认浏览器拖拽效果
                    var fileList = e.dataTransfer.files; //获取文件对象
                    //检测是否是拖拽文件到页面的操作
                    if (fileList.length === 0) {
                        return false;
                    }
                    //检测文件是不是图片
                    if (fileList[0].type.indexOf('text/plain') === -1) {
                        alert("您拖的不是txt文本！");
                        return false;
                    }

                    //上传
                    xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            $("#preview").text(xhr.responseText);
                        }
                    };
                    xhr.open("post", location.href, true);
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    console.log(fileList);
                    var fd = new FormData();
                    fd.append('txtfile', fileList[0]);
                    xhr.send(fd);

                }, false);
            });
        </script>
    </head>
    <body>

        <div class="left">
            <div id="drop_area">将txt文件拖拽到此区域</div>
            <textarea id="preview"></textarea>
        </div>
        <div class="right">
            <label for="table">Table:
                <select name="table" id="table">
                    <option value="0">=select a table=</option>
                    <?php
                        $result = mysql_list_tables($config['name']);
                        while ($row = mysql_fetch_row($result)) {
                            echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
                        }
                    ?>
                </select>
            </label>
            <div id="fields">
            </div>
            <input id="import"  name="import" type="button" value="import" />
        </div>    
        <div class="footer">
            <h2>执行结果：</h2>
            <hr>
        </div>
    </body>
</html>
