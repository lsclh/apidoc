#Api文档自动生成

>注释功能说明
```
类注释
@ApiSector          类名称(必须)
@ApiRoute           类访问路径(必须)           如:  /api/index (/模块/控制器)


方法注释
@ApiTitle	    API接口的标题(必须)	     如:  登入授权接口
@ApiSummary         API接口的简介(选填)        如:  用户登入授权使用,获取token,用来调取其他接口口令
@ApiRoute           API方法名(必须)           如:  /login
@ApiMethod          API请求方式(选填)         如:  POST (默认GET)
@ApiParams          API请求参数(选填)         如:  {"name":"username","description":"用户账号","required":"是","type":"string"} 多个参数写多@ApiParams即可  type类型支持 int float 生成number input file生成文件input 其他生成text
@ApiReturn          API返回结果示例(选填)      如:  {"code":1,"msg":"订单创建成功","time":"1547608948","data":{"order_num":"201901161122291547608949100241"}}
@ApiReturnParams    API返回参数说明(选填)      如:   {"name":"token","description":"返回口令","required":"是","type":"string"} 多个参数写多个@ApiParams即可
@ApiInternal	    此方法或类将不加入文档(选填)	    无
@Update             此方法正在开发中                   无
```


>登录接口示例
```
/**
 * @ApiSector 用户信息模块
 * @ApiRoute /api/user
 */
class User
{

    /**
     * @ApiTitle 用户登入
     * @ApiSummary 用户登入授权使用,获取token,用来调取其他接口口令
     * @ApiRoute /login
     * @ApiMethod POST
     * @ApiParams {"name":"username","description":"用户账号/手机号","required":"是","type":"int"}
     * @ApiParams {"name":"password","description":"用户密码","required":"是","type":"string"}
     * @ApiReturn {"code":200,"msg":"登入成功","data":{"token":"c9374c04-07be-4bd2-9e66-267eae37b35f"}}
     * @ApiReturnParams {"name":"token","description":"接口调用口令,登入成功必定返回","required":"是","type":"string"}
     */
    public function login(){

    }
}
```


###创建的文件
```php
//在easyswoole启动目录下创建文件ApiDoc


#!/usr/bin/env php
<?php
/**
 * 使用方法
 * php ApiDoc
 */


use Lsclh\Apidoc\ApiDoc;
define('EASYSWOOLE_ROOT', realpath(getcwd()));
$file = EASYSWOOLE_ROOT.'/vendor/autoload.php';
if (file_exists($file)) {
    require $file;
}else{
    die("include composer autoload.php fail".PHP_EOL);
}

//参数1表示接口地址目录
//参数2表示文档名称
//参数3表示保存到那个目录下
$res = (new ApiDoc('EasySwoole','You Name'))->getApiDoc('App/HttpController/Api','Api.html',EASYSWOOLE_ROOT.'/Temp/');
if($res) die('生成成功'.PHP_EOL);
die('生成失败'.PHP_EOL);

?>


```
