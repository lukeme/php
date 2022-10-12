<?php

if (!empty($_POST)) {
    $left = explode(',', trim($_POST['left']));
    $right = explode(',', trim($_POST['right']));
    $more = implode("\n", array_unique(array_diff($left, $right)));
    $less = implode("\n", array_unique(array_diff($right, $left)));
    echo json_encode(compact('more', 'less'));
    exit;
//     echo sprintf("left more %s, less %s\n", count($more), count($less));
//     echo sprintf("more:'%s'\n", implode("','", $more));
//     echo sprintf("less:'%s'\n", implode("','", $less));
//     echo sprintf("more:'%s'\n", implode("\n", $more));
//     echo sprintf("less:'%s'\n", implode("\n", $less));
}
?>
<html>
<head>
    <title>compare data</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script>
        function comp() {
            try {
                let url = ""
                // 传输数据 为object
                let left = document.getElementById('left').value.split("\n");
                let right = document.getElementById('right').value.split("\n");
                let data = {"left": left, "right": right}
                tools.ajaxPost(url, data, function (res) {
                    console.log('返回的数据:', res)
                    document.getElementById('left2').value = res.more
                    document.getElementById('right2').value = res.less
                });

            } catch (e) {
                alert(e);
            }
        }

        function copy(idx){
            if(idx===1){
                tools.copyToClipBoard(document.getElementById('left2').value, 'left2')
            }else{
                tools.copyToClipBoard(document.getElementById('right2').value, 'right2')
            }
        }

        // 常用工具函数
        var tools = {


            /* ajax请求get
             * @param url     string   请求的路径
             * @param query   object   请求的参数query
             * @param succCb  function 请求成功之后的回调
             * @param failCb  function 请求失败的回调
             * @param isJson  boolean  true： 解析json  false：文本请求  默认值true
             */
            ajaxGet: function (url, query, succCb, failCb, isJson) {
                // 拼接url加query
                if (query) {
                    var parms = tools.formatParams(query);
                    url += '?' + parms;
                    // console.log('-------------',url);
                }

                // 1、创建对象
                var ajax = new XMLHttpRequest();
                // 2、建立连接
                // true:请求为异步  false:同步
                ajax.open("GET", url, true);
                // ajax.setRequestHeader("Origin",STATIC_PATH);

                // ajax.setRequestHeader("Access-Control-Allow-Origin","*");
                // // 响应类型
                // ajax.setRequestHeader('Access-Control-Allow-Methods', '*');
                // // 响应头设置
                // ajax.setRequestHeader('Access-Control-Allow-Headers', 'x-requested-with,content-type');
                // ajax.withCredentials = true;
                // 3、发送请求
                ajax.send(null);

                // 4、监听状态的改变
                ajax.onreadystatechange = function () {
                    if (ajax.readyState === 4) {
                        if (ajax.status === 200) {
                            // 用户传了回调才执行
                            // isJson默认值为true，要解析json
                            if (isJson === undefined) {
                                isJson = true;
                            }
                            var res = isJson ? JSON.parse(ajax.responseText == "" ? '{}' : ajax.responseText) : ajax.responseText;
                            succCb && succCb(res);
                        } else {
                            // 请求失败
                            failCb && failCb();
                        }
                    }
                }

            },


            /* ajax请求post
         * @param url     string   请求的路径
         * @param data   object   请求的参数query
         * @param succCb  function 请求成功之后的回调
         * @param failCb  function 请求失败的回调
         * @param isJson  boolean  true： 解析json  false：文本请求  默认值true
         */
            ajaxPost: function (url, data, succCb, failCb, isJson) {
                var formData = new FormData();
                for (var i in data) {
                    formData.append(i, data[i]);
                }
                //得到xhr对象
                var xhr = null;
                if (XMLHttpRequest) {
                    xhr = new XMLHttpRequest();
                } else {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");

                }

                xhr.open("post", url, true);
                // 设置请求头  需在open后send前
                // 这里的CSRF需自己取后端获取，下面给出获取代码
                // xhr.setRequestHeader("X-CSRFToken", CSRF);
                xhr.send(formData);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            // 判断isJson是否传进来了
                            isJson = isJson === undefined ? true : isJson;
                            succCb && succCb(isJson ? JSON.parse(xhr.responseText) : xhr.responseText);
                        }
                    }
                }

            },

            copyToClipBoard:function(s,id){ //复制到剪切板
                if(document.execCommand){
                    let e = document.getElementById(id);
                    e.select();
                    document.execCommand("Copy");
                    return true;
                }
                if(window.clipboardData){
                    window.clipboardData.setData("Text", s);
                    return true;
                }
                return false;
            }

        }



    </script>
    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 10px;
        }

        .grid2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 10px;
        }


        .grid3 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /*grid-gap: 10px;*/
        }

        .button {
            color: white;
            align-items: center;
            display: flex;
            flex-direction: row;
            justify-content: center;
            border: 1px solid dodgerblue;
            background-color: darkgreen;
        }
    </style>
</head>
<body>
<h1>数据核对</h1>
<div class="grid">
    <div><textarea style="width:100%;" rows="50" id="left" placeholder="左侧数据"></textarea></div>
    <div><textarea style="width:100%;" rows="50" id="right" placeholder="右侧数据"></textarea></div>
    <div><textarea style="width:100%;" rows="50" id="left2" placeholder="左多数据"></textarea></div>
    <div><textarea style="width:100%;" rows="50" id="right2" placeholder="右多数据"></textarea></div>
</div>
<div class="grid2">
    <div class="button" onclick="comp();">compare</div>
    <div class="grid3">
        <div class="button clip" onclick="copy(1);">copy</div>
        <div class="button clip" onclick="copy(2);">copy</div>
    </div>
</div>
</body>
</html>
