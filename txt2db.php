<?php
error_reporting(E_ALL);
#数据库配置#
$conf = [
	'driver'=>'mysql',
	'host'=>'localhost',
	'port'=>'3306',
	'name'=>'test',
	'user'=>'root',
	'pass'=>'phpts',
	'char'=>'utf8',
];
date_default_timezone_set('PRC');
$db = new db($conf);
if ($_GET['table']??null) {
	foreach ($db->getFields($_GET['table']) as $column) {
		echo '<li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><label for="' . $column . '"><input type="checkbox" name="' . $column . '" id="' . $column . '"/>' . $column . '</label></li>';
	}
	exit;
}
if ($_FILES["txtfile"]??null) {
	$type = strtolower(strstr($_FILES['txtfile']['name'], '.'));
	if ($type != ".txt") {
		exit('格式不对！');
	}
	$file = 'data.txt';
	move_uploaded_file($_FILES["txtfile"]["tmp_name"], $file) or die('上传出错');
	echo file_get_contents($file);
	exit;
}


if ($_GET['dotable']??null && $_GET['dofields']??null) {
	$fields = '`' . str_replace(',', '`,`', trim($_GET['dofields'])) . '`';
	$file = 'data.txt';
	$rows = file($file);
	foreach ($rows as $i => $row) {
		$values = "'" . str_replace('|', "','", trim($row)) . "'";
		// $name = iconv('gbk','utf-8',$arr[1]);
		$sql = "INSERT INTO {$_GET['dotable']} ({$fields}) values ({$values})";
		$ok = $db->exec($sql);
		$msg = $ok ? '<font color="#0f0">succ</font>' : '<font color="#f00">fail when exec SQL: ' . $db->errorInfo() . ', ' . $sql . '</font>';
		echo '<br>', ($i + 1), '.', $msg;
	}
	exit;
}


class db {
	private $dbh;
	function __construct(array $conf){
		$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;',$conf['driver'], $conf['host'], $conf['port'],$conf['name'],);
		$this->dbh = new PDO($dsn, $conf['user'], $conf['pass']);
		$this->dbh->query("set names {$conf['char']}");
	}
	
	function fetchAll($sql = null, $mode=PDO::FETCH_ASSOC){
		if($sql){
			$sth = $this->dbh->prepare($sql);
		}
		$sth->execute();
		return $sth->fetchAll($mode);
	}
	
	function exec($sql){
		return $this->dbh->exec($sql);
	}
	
	function getTables(){
		$sql = "SHOW TABLES";
		return $this->fetchAll($sql, PDO::FETCH_NUM);
	}
	
	function getFields($table){
		$sql = "DESCRIBE $table";
		return $this->fetchAll($sql, PDO::FETCH_COLUMN);
	}
	
	function errorInfo(){
		return $this->dbh->errorInfo();
	}
	
	//beginTransaction,commit,rollBack
	function __call($method, $param){
		return $this->dbh->$method($param);
	}
	
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
							foreach ($db->getTables() as $row) {
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
        <footer><?=date('Y-m-d H:i:s')?></footer>
    </body>
</html>
