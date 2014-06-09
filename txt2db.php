<?php
#数据库配置#
$config = array(
	'host' => 'localhost',
	'port' => '3306', #数据库端口
	'user' => 'root',
	'pass' => 'vertrigo',
	'name' => 'test',
);
date_default_timezone_set('PRC');
mysql_connect($config['host'] . ':' . $config['port'], $config['user'], $config['pass']) or die('Mysql Connect error');
mysql_select_db($config['name']);
mysql_query("set names 'utf8'");
if ($_GET['table']) {
	$fields = mysql_list_fields("test", $_GET['table']);
	$columns = mysql_num_fields($fields);
	for ($i = 0; $i < $columns; $i++) {
		$field_name = mysql_field_name($fields, $i);
		echo '<li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><label for="' . $field_name . '"><input type="checkbox" name="' . $field_name . '" id="' . $field_name . '"/>' . $field_name . '</label></li>';
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
		//var_dump($row);exit;
		$values = "'" . str_replace('|', "','", trim($row)) . "'";
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
        <title>导入txt文本到mysql数据库</title>	
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
        <style type="text/css">
            section{margin: auto 0px;}
            .left{width:500px; margin:50px; float: left;}
            .right{ margin:50px 0; float:left;}
            .result{ margin:50px auto; clear:both;}
            #drop_area{width:100%; height:100px; border:3px dashed silver; line-height:100px; text-align:center; font-size:36px; color:#d3d3d3}
            #preview{width:100%; overflow:hidden}
            #fields { list-style-type: none; margin: 5px 0; padding: 0;}
            #fields li {  padding: 0.4em; padding-left: 1.5em; }
            #fields li span { position: absolute; margin-left: -1.3em; }
        </style>
        <script type="text/javascript">
			$(function() {
				$("#fields").sortable();
				$("#fields").disableSelection();
				$("#table").change(function() {
					var table = $("#table option:selected").val();
					console.log($("#table option:selected").val());
					$.get(location.href, {table: table}, function(html) {
						$("#fields").html(html);
					});
				});

				$("#import").click(function() {
					var table = $("#table option:selected").val();
					var fields = $("input:checkbox:checked").map(function() {
						return $(this).attr('id');
					}).get().join(',');
					if (table && fields) {
						$.get(location.href, {dotable: table, dofields: fields}, function(html) {
							$("footer").html(html);
						});
					}
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
		<header>
			<h1>导入txt文本到mysql数据库</h1>
			<em>注：导入的txt文本字段以｜分隔</em>
		</header>
        <section>
            <form method="post" >
				<div class="left">
					<div id="drop_area">将txt文件拖拽到此区域</div>
					<textarea id="preview" required="required"></textarea>
				</div>
				<div class="right">
					<label for="table">Table:
						<select name="table" id="table" required="required">
							<option>=select a table=</option>
							<?php
							$result = mysql_list_tables($config['name']);
							while ($row = mysql_fetch_row($result)) {
								echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
							}
							?>
						</select>
					</label>
					<ul id="fields">
					</ul>
					<input id="import" type="button" value="Import!" />
				</div>
            </form>
        </section>
        <section>
            <div class="result">
                <h2>执行结果：</h2>
                <hr>
            </div>
        </section>
        <footer><?php echo date('Y-m-d H:i:s'); ?></footer>
    </body>
</html>
