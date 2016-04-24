<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sponsorship - @yield('title')</title>
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  @yield('styles')
    <!--[if lt IE 9]>
  <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="container" style="border-bottom: 1px solid #e5e5e5;">
  <div class="navbar-header">
    <a href="/" class="navbar-brand">企业家</a>
  </div>
  <ul class="nav navbar-nav navbar-right">

  </ul>
</div>

<div class="container">
  @yield('content')
</div>
<script src="/scripts/jquery.min.js"></script>
<script src="/scripts/bootstrap.min.js"></script>
<script src="/scripts/lib.js"></script>
@yield('scripts')
</body>
</html>