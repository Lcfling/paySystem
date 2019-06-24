<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/4
 * Time: 10:34
 */
class QRcodeModel extends Model{


    /**固码第一次进去
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @return string
     */
    public function getQRurl($url,$gqtime,$orderid,$user_id){

        $html ="<!doctype html>
                
                <html lang=\"en\">
                
                <head>
                
                    <meta charset=\"UTF-8\">
                
                    <meta name=\"viewport\"
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                    <title>Document</title>
                
                    <style>
                
                        * {
                            margin: 0;
                            padding: 0;
                        }
                        .color-red{
                            color: red;
                        }
                
                        .zhifu {
                            padding: 20px;
                            text-align: center;
                        }
                
                        .zhifu img {
                            margin: 20px auto;
                        }
                
                        .button span {
                            display: inline-block;
                            padding: 10px 30px;
                            background: #3668c8;
                            border-radius: 5px;
                            color: white;
                            margin: 20px 5px;
                        }
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                        .none{display: none;}
                    </style>
                
                </head>
                
                <body>
                
                <div class=\"zhifu\">
                
                    <h2>微信支付</h2>
                
                    <p style=\"padding-top: 30px\">请将二维码保存相册</p>
                    <p>登录微信扫码并微信支付</p>
                
                    <img src=\"#\" alt=\"\" id=\"url\" width=\"80%\">
                
                    <p class=\"color-red\">二维码仅本次使用</p>
                    <p class=\"color-red\">重复支付不会到账</p>
                    <p class=\"color-red\">超时支付不会到账</p>
                
                    <p style=\"padding-top: 10px;\">订单超时时间： <span class=\"color-red\" id=\"time\"></span></p>
                    <div class=\"button\">
                
                        <span id=\"paycheck\">已支付</span>
                
                        <span id=\"cancel\">取消订单</span>
                    </div>
                
                
                
                </div>
                
                <div class=\"mask none\"></div>
                <div class=\"mask-box none\">
                    <p>如您已支付，请联系客服</p>
                    <p class=\"button\">
                        <span id=\"close\" style=\"background: gray\">取消</span>
                        <span id=\"cancelsec\">确定</span>
                    </p>
                </div>
                </body>
                
                </html>
                
                <script type=\"text/javascript\">
                
                    var order_id, user_id;
                
                  
                    document.getElementById('url').src = 'http://{$url}';
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
                    var gptime = getLocalTime('{$gqtime}');//过期时间
                    $('#time').text(gptime);
                    console.log( gptime);
                    
                
                
                    function getLocalTime(nS) {
                        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
                    }
                
                
                    auto();
                
                    function auto() {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcheckfirst',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 0){
                                    alert(res.info);
                                }
                
                            }
                        })
                    }
                
                    $('#paycheck').click(function () {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/paycheck',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 1){
                                    window.location.href = 'paydone.html';
                                }else{
                                    alert(res.info);
                                }
                
                            }
                        })
                    });
                
                    $('#cancel').click(function () {
                        $('.mask').show();
                        $('.mask-box').show();
                
                    });
                    $('#close').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                
                    });
                    $('#cancelsec').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                            success: function (res) {
                                console.log(res);
                
                                alert(res.info);
                
                            }
                        })
                    })
                
                </script>";

        return $html;exit();
    }

    /**固码第二次进来
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @return string
     */
    public function getQRurlsecond($url,$gqtime,$orderid,$user_id){

        $html ="<!doctype html>
                
                <html lang=\"en\">
                
                <head>
                
                    <meta charset=\"UTF-8\">
                
                    <meta name=\"viewport\"
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                    <title>Document</title>
                
                    <style>
                
                        * {
                            margin: 0;
                            padding: 0;
                        }
                        .color-red{
                            color: red;
                        }
                
                        .zhifu {
                            padding: 20px;
                            text-align: center;
                        }
                
                        .zhifu img {
                            margin: 20px auto;
                        }
                
                        .button span {
                            display: inline-block;
                            padding: 10px 30px;
                            background: #3668c8;
                            border-radius: 5px;
                            color: white;
                            margin: 20px 5px;
                        }
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                        .none{display: none;}
                    </style>
                
                </head>
                
                <body>
                
                <div class=\"zhifu\">
                
                    <h2>微信支付</h2>
                
                    <p style=\"padding-top: 30px\">请将二维码保存相册</p>
                    <p>登录微信扫码并微信支付</p>
                
                    <img src=\"#\" alt=\"\" id=\"url\" width=\"80%\">
                
                    <p class=\"color-red\">二维码仅本次使用</p>
                    <p class=\"color-red\">重复支付不会到账</p>
                    <p class=\"color-red\">超时支付不会到账</p>
                
                    <p style=\"padding-top: 10px;\">订单超时时间： <span class=\"color-red\" id=\"time\"></span></p>
                    <div class=\"button\">
                
                        <span id=\"paycheck\">已支付</span>
                
                        <span id=\"cancel\">取消订单</span>
                    </div>
                
                
                
                </div>
                
                <div class=\"mask none\"></div>
                <div class=\"mask-box none\">
                    <p>如您已支付，请联系客服</p>
                    <p class=\"button\">
                        <span id=\"close\" style=\"background: gray\">取消</span>
                        <span id=\"cancelsec\">确定</span>
                    </p>
                </div>
                </body>
                
                </html>
                
                <script type=\"text/javascript\">
                
                    var order_id, user_id;
                
                  
                    document.getElementById('url').src = 'http://{$url}';
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
                    var gptime = getLocalTime('{$gqtime}');//过期时间
                    $('#time').text(gptime);
                    
                
                
                    function getLocalTime(nS) {
                        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
                    }
                
                
                    auto();
                
                    function auto() {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddchecksecond',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 0){
                                    alert(res.info);
                                }
                
                            }
                        })
                    }
                
                    $('#paycheck').click(function () {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/paycheck',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 1){
                                    window.location.href = 'paydone.html';
                                }else{
                                    alert(res.info);
                                }
                
                            }
                        })
                    });
                
                    $('#cancel').click(function () {
                        $('.mask').show();
                        $('.mask-box').show();
                
                    });
                    $('#close').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                
                    });
                    $('#cancelsec').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                            success: function (res) {
                                console.log(res);
                
                                alert(res.info);
                
                            }
                        })
                    })
                
                </script>";

        return $html;exit();
    }

    /**固码第三次进来
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @return string
     */
    public function getQRurlthird($url,$gqtime,$orderid,$user_id){

        $html ="<!doctype html>

                
                
                <html lang=\"en\">
                
                
                
                <head>
                
                
                
                    <meta charset=\"UTF-8\">
                
                
                
                    <meta name=\"viewport\"
                
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                
                
                    <title>Document</title>
                
                
                
                    <style>
                
                
                
                        * {
                
                            margin: 0;
                
                            padding: 0;
                
                        }
                
                        .color-red{
                
                            color: red;
                
                        }
                
                
                
                        .zhifu {
                
                            padding: 20px;
                
                            text-align: center;
                
                        }
                
                
                
                        .zhifu img {
                
                            margin: 20px auto;
                
                        }
                
                
                
                        .button span {
                
                            display: inline-block;
                
                            padding: 10px 30px;
                
                            background: #3668c8;
                
                            border-radius: 5px;
                
                            color: white;
                
                            margin: 20px 5px;
                
                        }
                
                
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                
                        .none{display: none;}
                
                    </style>
                
                
                
                </head>
                
                
                
                <body>
                
                
                
                <p style=\"padding: 20px;font-size: 18px;\">您的订单已过期，请取消订单</p>
                
                <div class=\"button\" style=\"text-align: center\">
                
                    <span id=\"cancel\">取消订单</span>
                
                </div>
                
                
                
                
                
                
                
                <div class=\"mask none\"></div>
                
                <div class=\"mask-box none\">
                
                    <p>如您已支付，请联系客服</p>
                
                    <p class=\"button\">
                
                        <span id=\"close\" style=\"background: gray\">取消</span>
                
                        <span id=\"cancelsec\">确定</span>
                
                    </p>
                
                </div>
                
                </body>
                
                
                
                </html>
                
                
                
                <script type=\"text/javascript\">
                
                
                
                    var order_id, user_id;
               
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
               
                             
                    $('#cancel').click(function () {
                
                        $('.mask').show();
                
                        $('.mask-box').show();
                
                
                
                    });
                
                    $('#close').click(function () {
                
                        $('.mask').hide();
                
                        $('.mask-box').hide();
                
                
                
                    });
                
                    $('#cancelsec').click(function () {
                
                        $('.mask').hide();
                
                        $('.mask-box').hide();
                
                        var data = {
                
                            user_id: user_id,
                
                            order_id: order_id,
                
                        };
                
                        $.ajax({
                
                            type: 'post',
                
                            data: data,
                
                            dataType: 'json',
                
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                
                            success: function (res) {
                
                                console.log(res);
                
                
                
                                alert(res.info);
                
                
                
                            }
                
                        })
                
                    })
                
                
                
                </script>";

        return $html;exit();
    }


    /**通用码第一次进来页面
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @param $tradeMoney
     * @return string
     */
    public function getcommonQRurl($url,$gqtime,$orderid,$user_id,$tradeMoney){

        $html ="<!doctype html>
                
                <html lang=\"en\">
                
                <head>
                
                    <meta charset=\"UTF-8\">
                
                    <meta name=\"viewport\"
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                    <title>Document</title>
                
                    <style>
                
                        * {
                            margin: 0;
                            padding: 0;
                        }
                        .color-red{
                            color: red;
                        }
                
                        .zhifu {
                            padding: 20px;
                            text-align: center;
                        }
                
                        .zhifu img {
                            margin: 20px auto;
                        }
                
                        .button span {
                            display: inline-block;
                            padding: 10px 30px;
                            background: #3668c8;
                            border-radius: 5px;
                            color: white;
                            margin: 20px 5px;
                        }
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                        .none{display: none;}
                    </style>
                
                </head>
                
                <body>
                
                <div class=\"zhifu\">
                
                    <h2>微信支付</h2>
                    <h1>{$tradeMoney} ￥</h1>
                
                    <p style=\"padding-top: 30px\">请将二维码保存相册</p>
                    <p>登录微信扫码并微信支付</p>
                
                    <img src=\"#\" alt=\"\" id=\"url\" width=\"80%\">
                
                    <p class=\"color-red\">二维码仅本次使用</p>
                    <p class=\"color-red\">重复支付不会到账</p>
                    <p class=\"color-red\">超时支付不会到账</p>
                
                    <p style=\"padding-top: 10px;\">订单超时时间： <span class=\"color-red\" id=\"time\"></span></p>
                    <div class=\"button\">
                
                        <span id=\"paycheck\">已支付</span>
                
                        <span id=\"cancel\">取消订单</span>
                    </div>
                
                
                
                </div>
                
                <div class=\"mask none\"></div>
                <div class=\"mask-box none\">
                    <p>如您已支付，请联系客服</p>
                    <p class=\"button\">
                        <span id=\"close\" style=\"background: gray\">取消</span>
                        <span id=\"cancelsec\">确定</span>
                    </p>
                </div>
                </body>
                
                </html>
                
                <script type=\"text/javascript\">
                
                    var order_id, user_id;
                
                  
                    document.getElementById('url').src = 'http://{$url}';
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
                    var gptime = getLocalTime('{$gqtime}');//过期时间
                    $('#time').text(gptime);
                    console.log( gptime);
                    
                
                
                    function getLocalTime(nS) {
                        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
                    }
                
                
                    auto();
                
                    function auto() {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcheckfirst',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 0){
                                    alert(res.info);
                                }
                
                            }
                        })
                    }
                
                    $('#paycheck').click(function () {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/paycheck',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 1){
                                    window.location.href = 'paydone.html';
                                }else{
                                    alert(res.info);
                                }
                
                            }
                        })
                    });
                
                    $('#cancel').click(function () {
                        $('.mask').show();
                        $('.mask-box').show();
                
                    });
                    $('#close').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                
                    });
                    $('#cancelsec').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                            success: function (res) {
                                console.log(res);
                
                                alert(res.info);
                
                            }
                        })
                    })
                
                </script>";

        return $html;exit();
    }

    /**通用码第二次进来
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @param $tradeMoney
     * @return string
     */
    public function getcomonQRurlsecond($url,$gqtime,$orderid,$user_id,$tradeMoney){

        $html ="<!doctype html>
                
                <html lang=\"en\">
                
                <head>
                
                    <meta charset=\"UTF-8\">
                
                    <meta name=\"viewport\"
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                    <title>Document</title>
                
                    <style>
                
                        * {
                            margin: 0;
                            padding: 0;
                        }
                        .color-red{
                            color: red;
                        }
                
                        .zhifu {
                            padding: 20px;
                            text-align: center;
                        }
                
                        .zhifu img {
                            margin: 20px auto;
                        }
                
                        .button span {
                            display: inline-block;
                            padding: 10px 30px;
                            background: #3668c8;
                            border-radius: 5px;
                            color: white;
                            margin: 20px 5px;
                        }
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                        .none{display: none;}
                    </style>
                
                </head>
                
                <body>
                
                <div class=\"zhifu\">
                
                    <h2>微信支付</h2>
                    <h1>{$tradeMoney} ￥</h1>
                
                    <p style=\"padding-top: 30px\">请将二维码保存相册</p>
                    <p>登录微信扫码并微信支付</p>
                
                    <img src=\"#\" alt=\"\" id=\"url\" width=\"80%\">
                
                    <p class=\"color-red\">二维码仅本次使用</p>
                    <p class=\"color-red\">重复支付不会到账</p>
                    <p class=\"color-red\">超时支付不会到账</p>
                
                    <p style=\"padding-top: 10px;\">订单超时时间： <span class=\"color-red\" id=\"time\"></span></p>
                    <div class=\"button\">
                
                        <span id=\"paycheck\">已支付</span>
                
                        <span id=\"cancel\">取消订单</span>
                    </div>
                
                
                
                </div>
                
                <div class=\"mask none\"></div>
                <div class=\"mask-box none\">
                    <p>如您已支付，请联系客服</p>
                    <p class=\"button\">
                        <span id=\"close\" style=\"background: gray\">取消</span>
                        <span id=\"cancelsec\">确定</span>
                    </p>
                </div>
                </body>
                
                </html>
                
                <script type=\"text/javascript\">
                
                    var order_id, user_id;
                
                  
                    document.getElementById('url').src = 'http://{$url}';
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
                    var gptime = getLocalTime('{$gqtime}');//过期时间
                    $('#time').text(gptime);
                    
                
                
                    function getLocalTime(nS) {
                        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
                    }
                
                
                    auto();
                
                    function auto() {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddchecksecond',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 0){
                                    alert(res.info);
                                }
                
                            }
                        })
                    }
                
                    $('#paycheck').click(function () {
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/paycheck',
                            success: function (res) {
                                console.log(res);
                                if(res.status == 1){
                                    window.location.href = 'paydone.html';
                                }else{
                                    alert(res.info);
                                }
                
                            }
                        })
                    });
                
                    $('#cancel').click(function () {
                        $('.mask').show();
                        $('.mask-box').show();
                
                    });
                    $('#close').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                
                    });
                    $('#cancelsec').click(function () {
                        $('.mask').hide();
                        $('.mask-box').hide();
                        var data = {
                            user_id: user_id,
                            order_id: order_id,
                        };
                        $.ajax({
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                            success: function (res) {
                                console.log(res);
                
                                alert(res.info);
                
                            }
                        })
                    })
                
                </script>";

        return $html;exit();
    }

    /**通用码第三次进来
     * @param $url
     * @param $gqtime
     * @param $orderid
     * @param $user_id
     * @param $tradeMoney
     * @return string
     */
    public function getcomonQRurlthird($url,$gqtime,$orderid,$user_id){

        $html ="<!doctype html>

                
                
                <html lang=\"en\">
                
                
                
                <head>
                
                
                
                    <meta charset=\"UTF-8\">
                
                
                
                    <meta name=\"viewport\"
                
                          content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
                
                
                
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                
                
                
                    <script type=\"text/javascript\" src=\"http://sky.562555.cn/wxzfqr/jquery-3.3.1.min.js\"></script>
                
                
                
                    <title>Document</title>
                
                
                
                    <style>
                
                
                
                        * {
                
                            margin: 0;
                
                            padding: 0;
                
                        }
                
                        .color-red{
                
                            color: red;
                
                        }
                
                
                
                        .zhifu {
                
                            padding: 20px;
                
                            text-align: center;
                
                        }
                
                
                
                        .zhifu img {
                
                            margin: 20px auto;
                
                        }
                
                
                
                        .button span {
                
                            display: inline-block;
                
                            padding: 10px 30px;
                
                            background: #3668c8;
                
                            border-radius: 5px;
                
                            color: white;
                
                            margin: 20px 5px;
                
                        }
                
                
                
                        .mask{position: fixed;width: 100%;height:100%;left:0;top:0;background: rgba(0,0,0,.5)}
                
                        .mask-box{position: fixed;width: 80%;padding: 20px;background: white;border-radius: 5px;left:10%;top:40%;box-sizing: border-box;text-align: center;}
                
                        .none{display: none;}
                
                    </style>
                
                
                
                </head>
                
                
                
                <body>
                
                
                
                <p style=\"padding: 20px;font-size: 18px;\">您的订单已过期，请取消订单</p>
                
                <div class=\"button\" style=\"text-align: center\">
                
                    <span id=\"cancel\">取消订单</span>
                
                </div>
                
                
                
                
                
                
                
                <div class=\"mask none\"></div>
                
                <div class=\"mask-box none\">
                
                    <p>如您已支付，请联系客服</p>
                
                    <p class=\"button\">
                
                        <span id=\"close\" style=\"background: gray\">取消</span>
                
                        <span id=\"cancelsec\">确定</span>
                
                    </p>
                
                </div>
                
                </body>
                
                
                
                </html>
                
                
                
                <script type=\"text/javascript\">
                
                
                
                    var order_id, user_id;
               
                    order_id = '{$orderid}';//订单id
                    user_id = '{$user_id}';//码商id
               
                             
                    $('#cancel').click(function () {
                
                        $('.mask').show();
                
                        $('.mask-box').show();
                
                
                
                    });
                
                    $('#close').click(function () {
                
                        $('.mask').hide();
                
                        $('.mask-box').hide();
                
                
                
                    });
                
                    $('#cancelsec').click(function () {
                
                        $('.mask').hide();
                
                        $('.mask-box').hide();
                
                        var data = {
                
                            user_id: user_id,
                
                            order_id: order_id,
                
                        };
                
                        $.ajax({
                
                            type: 'post',
                
                            data: data,
                
                            dataType: 'json',
                
                            url: 'http://sky.562555.cn/app/Orderym/ddcancel',
                
                            success: function (res) {
                
                                console.log(res);
                
                
                
                                alert(res.info);
                
                
                
                            }
                
                        })
                
                    })
                
                
                
                </script>";

        return $html;exit();
    }


}