@extends('layouts.main')
@section('title', '企业家')

@section('scripts')
  <script type="text/javascript" src="/scripts/jquery.twbsPagination.min.js"></script>
  <script type="text/javascript">
    function requestUrl(url, type) {
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

    function handleSuccess(id, type) {
      if(!confirm('确认成功？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/success', type);
    }

    function handleFailure(id, type) {
      if(!confirm('确认失败？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/failure',type);
    }
    function loadApplications(page) {
      $.get('/web/application/list/?' + 'page=' + page, function(resp) {
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

            if(application.status == '0'){
              html += '<td>未审核</td>';
              html += '<td>' +
                      ' <a onclick="handlePass('+application.id+', \'application\')" href="javascript:{}">通过</a>' +
                      ' <a onclick="handleDelete('+application.id+', \'application\')" href="javascript:{}">拒绝</a>' +
                      '</td>';
            }
            if(application.status == '1'){
              html += '<td>对接中</td>';
              html += '<td>' +
                      ' <a onclick="handleSuccess('+application.id+', \'application\')" href="javascript:{}">成功</a>' +
                      ' <a onclick="handleFailure('+application.id+', \'application\')" href="javascript:{}">失败</a>' +
                      '</td>';
            }
            if(application.status == '2') {
              html += '<td>对接成功</td>';
              html += '<td></td>';
            }
            if(application.status == '3') {
              html += '<td>对接失败</td>';
              html += '<td></td>';
            }
            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核申请</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
            $('#aPager').twbsPagination({
              totalPages: resp.total,
              visiblePages: 5,
              initiateStartPageClick:false,
              onPageClick: function (event, page) {
                loadApplications(page);
            }
          });
        }
      }, 'json');
    }

    loadApplications(1);
  </script>
@endsection

@section('content')
  <div class="page-header">
    <h3>申请信息列表
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

  <div id="btnApplication">
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
        <th>状态</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="aPager"></div>
  </div>


@endsection