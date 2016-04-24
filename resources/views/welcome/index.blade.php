@extends('layouts.main')
@section('title', '企业家')

@section('scripts')
  <script type="text/javascript" src="/scripts/jquery.twbsPagination.min.js"></script>
  <script type="text/javascript">
    var requirementLoad=0;
    var applicationLoad=0;
    var userLoad=0;
    function requestUrl(url, type)
    {
      $.get(url, function(resp) {
        if(resp.code != 0){
          return null;
        }
        if(type == 'requirement'){
          loadRequirements(1);
        }else if(type == 'application'){
          loadApplications(1);
        }else if(type == 'user') {
          loadUsers(1);
        }
      }, 'json');
    }

    function handlePass(id, type) {
      if(!confirm('确认通过？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/approve', type);
    }

    function handleDelete(id, type) {
      if(!confirm('确认删除？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/delete',type);
    }

    function loadRequirements(page) {
      $.get('/web/requirement/auditing/list/?' + 'page=' + page, function(resp) {
        if (resp.code != 0) {
          // notify user that error happens
          return;
        }

        var $table = $('#tblRequirements'),
                 requirements = resp.requirements;
        var $container = $('tbody', $table);
        $container.html(''); // clear old data
        if (requirements && requirements.length) {
          for (var i = 0, n = requirements.length; i < n; i++) {
            var requirement = requirements[i];
            var html = '<tr>';
            html += '<td><a href="/web/requirement/' + requirement.id + '/detail">' + requirement.title + '</a></td>';
            html += '<td>' + requirement.begin_time + ' - ' + requirement.end_time + '</td>';
            html += '<td>' + requirement.contacts + '</td>';
            html += '<td>' + requirement.mobile + '</td>';
            html += '<td>' + requirement.intro + '</td>';
            html += '<td><a onclick="handlePass('+requirement.id+', \'requirement\')" href="javascript:{}">通过</a> <a onclick="handleDelete('+requirement.id+', \'requirement\')" href="javascript:{}">拒绝</a></td>';
            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核需求</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
            var $pagination = $('#rPager').twbsPagination({
              totalPages: resp.total,
              visiblePages: 5,
              initiateStartPageClick:false,
              onPageClick: function (event, page) {
              loadRequirements(page);
            }
          });
          if(requirementLoad == 0 ){
            requirementLoad++;
            $('li', $pagination).removeClass('active');
            $('li:nth-child(1)', $pagination).addClass('disabled');
            $('li:nth-child(2)', $pagination).addClass('disabled');
            $('li:nth-child(3)', $pagination).addClass('active');
          }
        }
      }, 'json');
    }

    function loadApplications(page) {
      $.get('/web/application/auditing/list/?' + 'page=' + page, function(resp) {
        if (resp.code != 0) {
          // notify user that error happens
          return;
        }
        var $table = $('#tblApplications'),
                applications = resp.applications;
        var $container = $('tbody', $table);
        $container.html(''); // clear old data
        if (applications && applications.length) {
          for (var i = 0, n = applications.length; i < n; i++) {
            var application = applications[i];
            var html = '<tr>';
            html += '<td>' + application.requirement.title + '</td>';
            html += '<td>' + application.requirement.contacts + '</td>';
            html += '<td>' + application.requirement.mobile + '</td>';
            html += '<td>' + application.contacts + '</td>';
            html += '<td>' + application.mobile + '</td>';
            html += '<td>' + application.requirement.intro + '</td>';
            html += '<td>' + application.intro + '</td>';
            html += '<td><a onclick="handlePass('+application.id+', \'application\')" href="javascript:{}">通过</a> <a onclick="handleDelete('+application.id+', \'application\')" href="javascript:{}">拒绝</a></td>';
            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核申请</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
            var $pagination = $('#aPager').twbsPagination({
            totalPages: resp.total,
            visiblePages: 5,
            initiateStartPageClick:false,
            onPageClick: function (event, page) {
              loadApplications(page);
            }
          });
          if(applicationLoad == 0 ){
            applicationLoad++;
            $('li', $pagination).removeClass('active');
            $('li:nth-child(1)', $pagination).addClass('disabled');
            $('li:nth-child(2)', $pagination).addClass('disabled');
            $('li:nth-child(3)', $pagination).addClass('active');
          }
        }
      }, 'json');
    }

    function loadUsers(page) {
      $.get('/web/user/auditing/list/?' + 'page=' + page, function(resp) {
        if (resp.code != 0) {
          // notify user that error happens
          return;
        }
        var $table = $('#tblUsers'),
                users = resp.users;
        var $container = $('tbody', $table);
        $container.html(''); // clear old data
        if (users && users.length) {
          for (var i = 0, n = users.length; i < n; i++) {
            var user = users[i];
            var html = '<tr>';
            html += '<td>' + user.name + '</td>';
            html += '<td>' + user.mobile + '</td>';
            html += '<td>' + user.business + '</td>';
            html += '<td><a onclick="handlePass('+user.id+', \'user\')" href="javascript:{}">通过</a> <a onclick="handleDelete('+user.id+', \'user\')" href="javascript:{}">拒绝</a></td>';
            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核用户</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
          var $pagination =  $('#uPager').twbsPagination({
            totalPages: resp.total,
            visiblePages: 5,
            initiateStartPageClick:false,
            onPageClick: function (event, page) {
                loadUsers(page);
            }
          });
          if(userLoad == 0 ){
            userLoad++;
            $('li', $pagination).removeClass('active');
            $('li:nth-child(1)', $pagination).addClass('disabled');
            $('li:nth-child(2)', $pagination).addClass('disabled');
            $('li:nth-child(3)', $pagination).addClass('active');
          }
        }
      }, 'json');
    }

    $('#btnRequirementList').click(function(){
      $('#btnRequirement').show();
      $('#btnApplication').hide();
      $('#btnUser').hide();
      $('#btnRequirementList').attr('class', 'active');
      $('#btnApplicationList').attr('class', '');
      $('#btnUserList').attr('class', '');
      userLoad = 0;
      applicationLoad = 0;
      loadRequirements(1);
    });

    $('#btnApplicationList').click(function(){
      $('#btnRequirement').hide();
      $('#btnApplication').show();
      $('#btnUser').hide();
      $('#btnRequirementList').attr('class', '');
      $('#btnApplicationList').attr('class', 'active');
      $('#btnUserList').attr('class', '');
      userLoad = 0;
      requirementLoad = 0;
      loadApplications(1);
    });

    $('#btnUserList').click(function(){
      $('#btnRequirement').hide();
      $('#btnApplication').hide();
      $('#btnUser').show();
      $('#btnRequirementList').attr('class', '');
      $('#btnApplicationList').attr('class', '');
      $('#btnUserList').attr('class', 'active');
      applicationLoad = 0;
      requirementLoad = 0;
      loadUsers(1);
    });
    loadRequirements(1);
  </script>
@endsection

@section('content')
  <div class="page-header">
    <h3>待审核信息
      <div class="pull-right small" style="margin-left: 20px;">
        <a href="/web/requirement/view/list" class="btn btn-primary">
          <span class="glyphicon" aria-hidden="true"> 需求信息 </span>
        </a>
      </div>

      <div class="pull-right small" style="margin-left: 20px;">
        <a href="/web/application/view/list" class="btn btn-primary">
          <span class="glyphicon" aria-hidden="true"> 申请信息 </span>
        </a>
      </div>

      <div class="pull-right small" style="margin-left: 20px;">
        <a href="/web/user/view/list" class="btn btn-primary">
          <span class="glyphicon" aria-hidden="true"> 用户信息 </span>
        </a>
      </div>
    </h3>
  </div>

  <div>
    <ul class="nav nav-tabs">
      <li role="presentation" class="active" id="btnRequirementList" ><a href="javascript:{}">待审需求</a></li>
      <li role="presentation" id="btnApplicationList" ><a href="javascript:{}" >待审申请</a></li>
      <li role="presentation" id="btnUserList"><a href="javascript:{}" >待审用户</a></li>
    </ul>
  </div>

  <div class="modal fade"  id="divStoreError" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">错误</h4>
        </div>
        <div class="modal-body text-danger">
          <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
          <span class="message"></span>                </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <div id="btnRequirement">
    <table class="table table-striped" id="tblRequirements">
      <thead>
      <tr>
        <th>需求标题</th>
        <th>有效日期</th>
        <th>联系人</th>
        <th>手机号</th>
        <th>描述</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="rPager"></div>
  </div>

  <div id="btnApplication" style="display: none;">
    <table class="table table-striped" id="tblApplications">
      <thead>
      <tr>
        <th>需求标题</th>
        <th>需求联系人</th>
        <th>需求方手机</th>
        <th>申请联系人</th>
        <th>申请人手机号</th>
        <th>需求描述</th>
        <th>申请描述</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="aPager"></div>
  </div>

  <div id="btnUser" style="display: none;">
    <table class="table table-striped" id="tblUsers">
      <thead>
      <tr>
        <th>姓名</th>
        <th>手机号</th>
        <th>业务领域</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="uPager"></div>
  </div>







@endsection