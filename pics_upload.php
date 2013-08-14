<?php
if ($_FILES["pics"]) {
//    print_r($_FILES);
    $ret = '';
    for($i = 0; $i< count($_FILES['pics']['name']); $i++) {
        $ext = strtolower(strstr($_FILES['pics']['name'][$i], '.'));
        if (!in_array($ext, array('.jpg', '.png', '.gif'))) {
            exit('格式不对！');
        }
        $file = date('Ymd-His') . ($i + 1) . $ext;
        move_uploaded_file($_FILES["pics"]["tmp_name"][$i], $file) or die('上传出错');
        $ret .= '<img src="'.$file.'" />';
    }
    echo $ret;
    exit;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <title>图片上传</title>	
        <script type="text/javascript" src="http://www.helloweba.com/demo/js/jquery-1.7.2.min.js"></script>
        <style type="text/css">
            .demo{width:500px; margin:50px auto;}
            .footer{ margin:50px auto; clear:both;}
            #drop_area{width:100%; height:100px; border:3px dashed silver; line-height:100px; text-align:center; font-size:36px; color:#d3d3d3}
            #preview{width:500px; overflow:hidden}
        </style>
        <script type="text/javascript">
            $(function() {
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
                    console.log(fileList);
                    //检测是否是拖拽文件到页面的操作
                    if (fileList.length === 0) {
                        return false;
                    }
                    
                    var fd = new FormData();
                    for (var i = 0; i < fileList.length; i++) {
                        if (fileList[i].type.indexOf('image') === -1) {
                            alert("您拖的不是图片！");
                            return false;
                        }else{
                            fd.append('pics[]', fileList[i]);
                        }
                    }

                    //上传
                    xhr = new XMLHttpRequest();
                    xhr.open("post", location.href, true);
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xhr.send(fd);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            $("#preview").html(xhr.responseText);
                        }
                    };

                }, false);
            });
        </script>
    </head>
    <body>

        <div class="demo">
            <div id="drop_area">将图片拖拽到此区域</div>
            <div id="preview"></div>
        </div>
        <div class="footer">
            <hr>
        </div>
    </body>
</html>
