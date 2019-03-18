<?php
// +----------------------------------------------------------------------
// | Created by PhpStorm.©️
// +----------------------------------------------------------------------
// | User: 程立弘©️
// +----------------------------------------------------------------------
// | Date: 2019-03-02 16:56
// +----------------------------------------------------------------------
// | Author: 程立弘 <1019759208@qq.com>©️
// +----------------------------------------------------------------------

namespace Lsclh\Apidoc;

use EasySwoole\Utility\File;

/**
 * 获取Api文档
 * Class CreatApiDos
 * @package App\Apilog
 */
class ApiDoc
{

    public function __construct($objName = 'EasySwoole',$apiWriteUser="You Name"){
        $this->objName = $objName;
        $this->apiWriteUser = $apiWriteUser;
    }

    private $objName = '';
    private $apiWriteUser = '';
    /**
     * 创建Api文档
     * @param $dir 传入Api控制器目录
     */
    public function getApiDoc($dir = 'App/HttpController/Api',$apiName="Api.html",$filePutPath = EASYSWOOLE_ROOT.'/Temp/'){

        $file = File::scanDirectory(EASYSWOOLE_ROOT.'/'.$dir);
        $fileArr = [];
        foreach ($file['files'] as $k=>$v) $fileArr[] = explode($dir.'/',$v)[1];
        $docArr = [];

        foreach ($fileArr as $filename){ //循环获取文件名
            $filename = explode('.',$filename); //拆掉.php
            $reflection = new \ReflectionClass ( strtr('\\'.$dir.'\\'.$filename[0],'/','\\') ); //拼接命名空间 并把路径替换为命名空间格式 通过反射获取类信息和对象
            $doc = $reflection->getDocComment (); //获取类注释
            $docArr[$filename[0]] = DocParser::getInstance()->parse($doc); //注释格式化为数组
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC); //获取类内所有公开的方法
            foreach ($methods as $method) { //循环方法
                $funName = $method->name; //获取方法名
                if(substr($funName,0,1) == '_') continue; // 过滤掉本类魔术方法 与继承的魔术方法等
                $docArr[$filename[0]]['funDoc'][$funName] = DocParser::getInstance()->parse( $method->getDocComment()); //获取方法注释文档并格式化

            }
        }


        foreach ($docArr as $k => $v){ //循环class
            if (isset($v['ApiInternal'])){
                unset($docArr[$k]); //过滤类
                continue;
            }
            //适配类注释填写不报错
            $docArr[$k]['ApiSector'] = $v['ApiSector'] ?? '未填写类名';
            $docArr[$k]['ApiRoute'] = $v['ApiRoute'] ?? '未填写路由';

            foreach($v['funDoc'] as $kk => $vv){
                if(isset($vv['ApiInternal'])){
                    unset($docArr[$k]['funDoc'][$kk]); //过滤方法
                    continue ;
                }
                //适配类内方法填写不报错
                $docArr[$k]['funDoc'][$kk]['ApiTitle'] = $vv['ApiTitle'] ?? '未填写标题';
                $docArr[$k]['funDoc'][$kk]['ApiSummary'] = $vv['ApiSummary'] ?? '未填写简介';
                $docArr[$k]['funDoc'][$kk]['ApiRoute'] = $vv['ApiRoute'] ?? '未填写路由';
                $docArr[$k]['funDoc'][$kk]['ApiMethod'] = $vv['ApiMethod'] ?? 'GET';
                $docArr[$k]['funDoc'][$kk]['ApiReturn'] = $vv['ApiReturn'] ?? '未填写返回示例';
                $docArr[$k]['funDoc'][$kk]['ApiReturnParams'] = $vv['ApiReturnParams'] ?? '未填写返回信息参数说明';
                if(isset($vv['ApiReturnParams'])){ //适配格式 字符串转为数组
                    if(!is_array($vv['ApiReturnParams'])){
                        $docArr[$k]['funDoc'][$kk]['ApiReturnParams'] = [$vv['ApiReturnParams']];
                    }
                    foreach ($docArr[$k]['funDoc'][$kk]['ApiReturnParams'] as $kkk => $vvv){
                        $docArr[$k]['funDoc'][$kk]['ApiReturnParams'][$kkk]= json_decode($vvv,true);
                    }

                }else{
                    $docArr[$k]['funDoc'][$kk]['ApiReturnParams']= [];
                }
                if(isset($vv['ApiParams'])){ //适配格式 字符串转为数组
                    if(!is_array($vv['ApiParams'])){
                        $docArr[$k]['funDoc'][$kk]['ApiParams'] = [$vv['ApiParams']];
                    }
                    foreach ($docArr[$k]['funDoc'][$kk]['ApiParams'] as $kkk => $vvv){
                        $docArr[$k]['funDoc'][$kk]['ApiParams'][$kkk]= json_decode($vvv,true);
                    }

                }else{
                    $docArr[$k]['funDoc'][$kk]['ApiParams']= [];
                }


            }
        }
        return $this->file_put($docArr,$apiName,$filePutPath); //适配好的数据 进行套模版
    }


    /**
     * 套模版 保存文件
     * @param $docArr
     */
    private function file_put($docArr,$apiName,$filePutPath){
        $objName = $this->objName;
        $apiWriteUser = $this->apiWriteUser;
$header = <<<"Content"
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="FastAdmin">
    <title>Api Doc By $apiWriteUser</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <!-- Plugin CSS -->
    <link href="https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
        body {
            padding-top: 70px; margin-bottom: 15px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-family: "Roboto", "SF Pro SC", "SF Pro Display", "SF Pro Icons", "PingFang SC", BlinkMacSystemFont, -apple-system, "Segoe UI", "Microsoft Yahei", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", "Helvetica", "Arial", sans-serif;
            font-weight: 400;
        }
        h2        { font-size: 1.6em; }
        hr        { margin-top: 10px; }
        .tab-pane { padding-top: 10px; }
        .mt0      { margin-top: 0px; }
        .footer   { font-size: 12px; color: #666; }
        .label    { display: inline-block; min-width: 65px; padding: 0.3em 0.6em 0.3em; }
        .string   { color: green; }
        .number   { color: darkorange; }
        .boolean  { color: blue; }
        .null     { color: magenta; }
        .key      { color: red; }
        .popover  { max-width: 400px; max-height: 400px; overflow-y: auto;}
        .list-group.panel > .list-group-item {
        }
        .list-group-item:last-child {
            border-radius:0;
        }
        h4.panel-title a {
            font-weight:normal;
            font-size:14px;
        }
        h4.panel-title a .text-muted {
            font-size:12px;
            font-weight:normal;
            font-family: 'Verdana';
        }
        #sidebar {
            width: 220px;
            position: fixed;
            margin-left: -240px;
            overflow-y:auto;
        }
        #sidebar > .list-group {
            margin-bottom:0;
        }
        #sidebar > .list-group > a{
            text-indent:0;
        }
        #sidebar .child {
            border:1px solid #ddd;
            border-bottom:none;
        }
        #sidebar .child > a {
            border:0;
        }
        #sidebar .list-group a.current {
            background:#f5f5f5;
        }
        @media (max-width: 1620px){
            #sidebar {
                margin:0;
            }
            #accordion {
                padding-left:235px;
            }
        }
        @media (max-width: 768px){
            #sidebar {
                display: none;
            }
            #accordion {
                padding-left:0px;
            }
        }

    </style>
</head>
<body>
<!-- Fixed navbar -->
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="https://www.easyswoole.com" target="_blank">{$objName} Api Doc By $apiWriteUser</a>
        </div>
        <div class="navbar-collapse collapse">
            <form class="navbar-form navbar-right">
                <div class="form-group">
                    Token:
                </div>
                <div class="form-group">
                    <input type="text" class="form-control input-sm" data-toggle="tooltip" title="口令保存处" placeholder="token" id="token" />
                </div>
                <div class="form-group">
                    <a href="javascript:void(0);" class="btn btn-success btn-sm" onclick="myCopy()" >复制token</a>
                </div>
                <div class="form-group">
                    接口地址:
                </div>
                <div class="form-group">
                    <input id="apiUrl" type="text" class="form-control input-sm" data-toggle="tooltip" title="API接口URL" placeholder="https://api.mydomain.com" value="" />
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="点击保存后Token和Api url都将保存在本地Localstorage中" id="save_data">
                        <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div><!--/.nav-collapse -->
    </div>
</div>
Content;
        $i = 0;
        $tabdoc = '';
        $body = '';
        foreach ($docArr as $key => $value){
            $tabdoc .='<a href="#'.$value['ApiSector'].'" class="list-group-item" data-toggle="collapse" data-parent="#sidebar">'.$value['ApiSector'].'  <i class="fa fa-caret-down"></i></a>';
            $tabdoc .='<div class="child collapse" id="'.$value['ApiSector'].'">';

            $body .='<h2>'.$value['ApiSector'].'</h2><hr>';


            foreach ($value['funDoc'] as $funKey => $funValue){
                $tabdoc .= '<a href="javascript:;" data-id="'.$i.'" class="list-group-item">'.$funValue['ApiTitle'].'</a>';

                $body .= '<div class="panel panel-default">
            <div class="panel-heading" id="heading-'.$i.'">
                <h4 class="panel-title">
                    <span class="label '.(isset($funValue['Update']) ? 'label-default' : 'label-success').'">'.$funValue['ApiMethod'].'</span>
                    <a data-toggle="collapse" data-parent="#accordion'.$i.'" href="#collapseOne'.$i.'"> '.$funValue['ApiTitle'].' <span class="text-muted">'.$value['ApiRoute'].$funValue['ApiRoute'].'</span></a>
                </h4>
            </div>
            <div id="collapseOne'.$i.'" class="panel-collapse collapse">
                <div class="panel-body">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="doctab'.$i.'">
                        <li class="active"><a href="#info'.$i.'" data-toggle="tab">基础信息</a></li>
                        <li><a href="#sandbox'.$i.'" data-toggle="tab">在线测试</a></li>
                        <li><a href="#sample'.$i.'" data-toggle="tab">返回示例</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">

                        <div class="tab-pane active" id="info'.$i.'">
                            <div class="well">
                                '.$funValue['ApiSummary'].'                                    </div>
                            
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>参数</strong></div>
                                <div class="panel-body">';
                                   if($funValue['ApiParams']){
                                       $body.='<table class="table table-hover">
                                        <thead><tr><th>名称</th><th>类型</th><th>必选</th><th>描述</th></tr></thead><tbody>';
                                        foreach ($funValue['ApiParams'] as $parValue){
                                            $body .='<tr>
                                            <td>'.($parValue['name'] ?? '未填写').'</td>
                                            <td>'.($parValue['type'] ?? '未填写').'</td>
                                            <td>'.($parValue['required'] ?? '未知').'</td>
                                            <td>'.($parValue['description'] ?? '未填写').'</td>
                                            </tr>';
                                        }
                                        $body .='</tbody></table>';
                                   }else{
                                       $body.='无';
                                   }
                                $body .= '</div>
                            </div>
                            
                        </div><!-- #info -->

                        <div class="tab-pane" id="sandbox'.$i.'">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>参数</strong></div>
                                        <div class="panel-body">
                                            <form enctype="application/x-www-form-urlencoded" role="form" action="'.$value['ApiRoute'].$funValue['ApiRoute'].'" method="'.$funValue['ApiMethod'].'" name="form'.$i.'" id="form'.$i.'">';

                                                if($funValue['ApiParams']){
                                                    foreach ($funValue['ApiParams'] as $parValue){
                                                        if($parValue['type'] == 'int' || $parValue['type'] == 'float'){
                                                            $body .='<div class="form-group"><label class="control-label">'.$parValue['name'].'</label><input type="number" class="form-control input-sm" id="'.$parValue['name'].'"  placeholder="'.$parValue['description'].'" name="'.$parValue['name'].'"></div>';
                                                        }elseif($parValue['type'] == 'file'){
                                                            $body .='<div class="form-group"><label class="control-label">'.$parValue['name'].'</label><input type="file" class="form-control input-sm" id="'.$parValue['name'].'"  placeholder="'.$parValue['description'].'" name="'.$parValue['name'].'"></div>';
                                                        }else{
                                                            $body .='<div class="form-group"><label class="control-label">'.$parValue['name'].'</label><input type="text" class="form-control input-sm" id="'.$parValue['name'].'"  placeholder="'.$parValue['description'].'" name="'.$parValue['name'].'"></div>';

                                                        }
                                                    }

                                                }else{
                                                    $body .='<div class="form-group">
                                                            无
                                                        </div>';
                                                }

                                                $body.='<div class="form-group">
                                                    <button type="submit" class="btn btn-success send" rel="'.$i.'">提交</button>
                                                    <button type="reset" class="btn btn-info" rel="'.$i.'">重置</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>响应输出</strong></div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-12" style="overflow-x:auto">
                                                    <pre id="response_headers'.$i.'"></pre>
                                                    <pre id="response'.$i.'"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>返回参数</strong></div>
                                        <div class="panel-body">';
                                        if($funValue['ApiReturnParams']){
                                            $body.='<table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>名称</th>
                                                        <th>类型</th>
                                                        <th>描述</th>
                                                        <th>是否定返回</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                                   foreach ($funValue['ApiReturnParams'] as $parretVal){
                                                       $body .='<tr>
                                                        <td>'.($parretVal['name'] ?? '未填写').'</td>
                                                        <td>'.($parretVal['type'] ?? '未填写').'</td>
                                                        <td>'.($parretVal['description'] ?? '未填写').'</td>
                                                        <td>'.($parretVal['required'] ?? '未知').'</td>
                                                    </tr>';
                                                   }
                                                $body.='</tbody>
                                            </table>';
                                        }else{
                                            $body .='无';
                                        }

                                        $body.='</div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- #sandbox -->

                        <div class="tab-pane" id="sample'.$i.'">
                            <div class="row">
                                <div class="col-md-12">
                                    <pre id="sample_response'.$i.'">'.$funValue['ApiReturn'].'</pre>
                                </div>
                            </div>
                        </div><!-- #sample -->

                    </div><!-- .tab-content -->
                </div>
            </div>
        </div>';






                $i++;

            }
            $tabdoc .='</div>';
        }
        $fullContent = $header.'<div class="container"><div id="sidebar"><div class="list-group panel">'.$tabdoc.'</div></div><div class="panel-group" id="accordion">'.$body.'</div><hr><div class="row mt0 footer"><div class="col-md-6" align="left">Generated on '.date('Y-m-d H:i:s',time()).'                模版文件来自于FastAdmin 功能适配者 程立弘</div><div class="col-md-6" align="right"><a href="https://www.easyswoole.com" target="_blank">'.$objName.' Api Doc By '.$apiWriteUser.'</a></div></div></div>';

        $fullContent .=<<<foot
<!-- jQuery -->
<script src="https://cdn.staticfile.org/jquery/2.1.4/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript">

    function myCopy(){
        var ele = document.getElementById("token");
        ele.select();
        document.execCommand("Copy");
    }
    function syntaxHighlight(json) {
        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

    function prepareStr(str) {
        try {
            return syntaxHighlight(JSON.stringify(JSON.parse(str.replace(/'/g, '"')), null, 2));
        } catch (e) {
            return str;
        }
    }
    var storage = (function () {
        var uid = new Date;
        var storage;
        var result;
        try {
            (storage = window.localStorage).setItem(uid, uid);
            result = storage.getItem(uid) == uid;
            storage.removeItem(uid);
            return result && storage;
        } catch (exception) {
        }
    }());

    $.fn.serializeObject = function ()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (!this.value) {
                return;
            }
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(document).ready(function () {

        
        if (storage) {
            $('#token').val(storage.getItem('token'));
            $('#apiUrl').val(storage.getItem('apiUrl'));
        }

        $('[data-toggle="tooltip"]').tooltip({
            placement: 'bottom'
        });

        $(window).on("resize", function(){
            $("#sidebar").css("max-height", $(window).height()-80);
        });

        $(window).trigger("resize");

        $(document).on("click", "#sidebar .list-group > .list-group-item", function(){
            $("#sidebar .list-group > .list-group-item").removeClass("current");
            $(this).addClass("current");
        });
        $(document).on("click", "#sidebar .child a", function(){
            var heading = $("#heading-"+$(this).data("id"));
            if(!heading.next().hasClass("in")){
                $("a", heading).trigger("click");
            }
            $("html,body").animate({scrollTop:heading.offset().top-70});
        });

        $('code[id^=response]').hide();

        $.each($('pre[id^=sample_response],pre[id^=sample_post_body]'), function () {
            if ($(this).html() == 'NA') {
                return;
            }
            var str = prepareStr($(this).html());
            $(this).html(str);
        });

        $("[data-toggle=popover]").popover({placement: 'right'});

        $('[data-toggle=popover]').on('shown.bs.popover', function () {
            var sample = $(this).parent().find(".popover-content"),
                str = $(this).data('content');
            if (typeof str == "undefined" || str === "") {
                return;
            }
            var str = prepareStr(str);
            sample.html('<pre>' + str + '</pre>');
        });

        $('body').on('click', '#save_data', function (e) {
            if (storage) {
                storage.setItem('token', $('#token').val());
                storage.setItem('apiUrl', $('#apiUrl').val());
            } else {
                alert('Your browser does not support local storage');
            }
        });

        $('body').on('click', '.send', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            //added /g to get all the matched params instead of only first
            var matchedParamsInRoute = $(form).attr('action').match(/[^{]+(?=\})/g);
            var theId = $(this).attr('rel');
            //keep a copy of action attribute in order to modify the copy
            //instead of the initial attribute
            var url = $(form).attr('action');
            var method = $(form).prop('method').toLowerCase() || 'get';

            var formData = new FormData();

            $(form).find('input').each(function (i, input) {
                if ($(input).attr('type').toLowerCase() == 'file') {
                    formData.append($(input).attr('name'), $(input)[0].files[0]);
                    method = 'post';
                } else {
                    formData.append($(input).attr('name'), $(input).val())
                }
            });

            var index, key, value;

            if (matchedParamsInRoute) {
                var params = {};
                formData.forEach(function(value, key){
                    params[key] = value;
                });
                for (index = 0; index < matchedParamsInRoute.length; ++index) {
                    try {
                        key = matchedParamsInRoute[index];
                        value = params[key];
                        if (typeof value == "undefined")
                            value = "";
                        url = url.replace("\{" + key + "\}", value);
                        formData.delete(key);
                    } catch (err) {
                        console.log(err);
                    }
                }
            }

            var headers = {};

            var token = $('#token').val();
            if (token.length > 0) {
                headers['token'] = token;
            }
            

            $("#sandbox" + theId + " .headers input[type=text]").each(function () {
                val = $(this).val();
                if (val.length > 0) {
                    headers[$(this).prop('name')] = val;
                }
            });
            $.ajax({
                url: $('#apiUrl').val() + url,
                data: method == 'get' ? $(form).serialize() : formData,
                type: method,
                dataType: 'json',
                contentType: false,
                processData: false,
                headers: headers,
                success: function (data, textStatus, xhr) {
                    if (typeof data === 'object') {
                        var str = JSON.stringify(data, null, 2);
                        $('#response' + theId).html(syntaxHighlight(str));
                    } else {
                        $('#response' + theId).html(data || '');
                    }
                    $('#response_headers' + theId).html('HTTP ' + xhr.status + ' ' + xhr.statusText + '<br/><br/>' + xhr.getAllResponseHeaders());
                    $('#response' + theId).show();
                },
                error: function (xhr, textStatus, error) {
                    try {
                        var str = JSON.stringify($.parseJSON(xhr.responseText), null, 2);
                    } catch (e) {
                        var str = xhr.responseText;
                    }
                    $('#response_headers' + theId).html('HTTP ' + xhr.status + ' ' + xhr.statusText + '<br/><br/>' + xhr.getAllResponseHeaders());
                    $('#response' + theId).html(syntaxHighlight(str));
                    $('#response' + theId).show();
                }
            });
            return false;
        });
    });
</script>
</body>
</html>
foot;





        if(@file_put_contents($filePutPath.$apiName,$fullContent)){
            return true;
        }else{
            return false;
        }
    }



}