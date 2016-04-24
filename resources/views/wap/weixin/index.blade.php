<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>企业对接服务</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="/assets/css/wap/weixin.css" rel="stylesheet" type="text/css">
    <link href="//cdn.bootcss.com/pickadate.js/3.5.6/compressed/themes/default.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/pickadate.js/3.5.6/compressed/themes/default.date.css" rel="stylesheet">
  </head>
  <body>
    <div id="app"></div>

    <script type="text/javascript">
      var openId = '{{$wechat_openid}}';
      var fromWeixin = (window.navigator.userAgent.toLowerCase().match(/MicroMessenger/i))  == 'micromessenger';
      if ((fromWeixin && !openId && '{{$wechat_session}}' == 0)) {
         location.href = '/wap/weixin/oauth/go?redirect_url='+ location.origin +'/wap/weixin#/requirements?is_scope_userinfo=1';
      }

      @if (!empty($user))
        localStorage.removeItem('user');
        localStorage.setItem('user', '{!! json_encode($user)  !!}');
      @endif
    </script>

    <script src="//cdn.bootcss.com/jquery/2.2.0/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/pickadate.js/3.5.6/compressed/picker.js"></script>
    <script src="//cdn.bootcss.com/pickadate.js/3.5.6/compressed/picker.date.js"></script>
    <script type="text/javascript" src="/assets/js/wap/weixin.js"></script>
  </body>
</html>
