@extends('layouts.main')
@section('title', '企业家')

@section('scripts')
  <script type="text/javascript" src="/scripts/jquery.twbsPagination.min.js"></script>
  <script type="text/javascript">
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

    function handleResetPassword(id, type) {
      if(!confirm('确认删除？')) {
        return;
      }
      $.get('/web/'+type+'/'+ id +'/reset', function(resp) {
        if(resp.code != 0){
          return null;
        }
        alert("重置密码成功");
      }, 'json');
    }

    function loadUsers(page) {
      $.get('/web/user/list/?' + 'page=' + page, function(resp) {
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
            if(user.status == '－1'){
              html += '<td>已删除</td>';
              html += '<td></td>';
            }
            if(user.status == '0'){
              html += '<td>未审核</td>';
              html += '<td>' +
                      '<a onclick="handlePass('+user.id+', \'user\')" href="javascript:{}">通过</a> ' +
                      '<a onclick="handleDelete('+user.id+', \'user\')" href="javascript:{}">拒绝</a>' +
                      '</td>';
            }
            if(user.status == '1'){
              html += '<td>已激活</td>';
              html += '<td>' +
                      '<a onclick="handleResetPassword('+user.id+', \'user\')" href="javascript:{}">密码重置</a> ' +
                      '</td>';
            }

            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核用户</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
          $('#uPager').twbsPagination({
            totalPages: resp.total,
            visiblePages: 5,
            initiateStartPageClick:false,
            onPageClick: function (event, page) {
                loadUsers(page);
            }
          });
        }
      }, 'json');
    }

    loadUsers(1);
  </script>
@endsection

@section('content')
  <div class="page-header">
    <h3>用户信息列表
      <div class="pull-right small" style="margin-left: 20px;">
        <a href="/web/" class="btn btn-primary">
          <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"> 返回首页 </span>
        </a>
      </div>
    </h3>
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

  <div id="btnUser" >
    <table class="table table-striped" id="tblUsers">
      <thead>
      <tr>
        <th>姓名</th>
        <th>手机号</th>
        <th>业务领域</th>
        <th>状态</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="uPager"></div>
  </div>







@endsection