# BigFileUploader

通过文件分片解决大文件上传的问题

## 用法

- 引入 JS 文件

````html
<script type="text/javascript" src="//code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="../src/BigFileUploader.js"></script>
````

- 与 file 控件绑定

````javascript
$('#button').click(function () {
    (new BigFileUploader($('#file').get(0), {
        url: './upload.php',
        error: function (o) {
            alert(o.message);
        },
        success: function (o) {
            alert(o.message);
        },
        progress: function (o) {
            console.log(o.percent);
            $('#progressBar').css('width', o.percent + '%');
        }
    })).start();
})
````

### 选项
### blockSize
文件块大小，单位 MB。默认值为 10
### url
服务端接口地址
### error
发生错误时的回调
### success
文件成功上传时的回调
### progress
文件上传进度发生变化时的回调