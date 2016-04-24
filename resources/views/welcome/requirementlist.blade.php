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

    function handleStop(id, type) {
      if(!confirm('确认暂停？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/stop',type);
    }

    function handleClose(id, type) {
      if(!confirm('确认关闭？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/close',type);
    }

    function handleRecovery(id, type) {
      if(!confirm('确认恢复？')) {
        return;
      }
      requestUrl('/web/'+type+'/'+ id +'/recovery',type);
    }

    function loadRequirements(page) {
      $.get('/web/requirement/list/?' + 'page=' + page, function(resp) {
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
            if(requirement.status == '0'){
              html += '<td>未审核</td>';
              html += '<td>' +
                      ' <a onclick="handlePass('+requirement.id+', \'requirement\')" href="javascript:{}">通过</a>' +
                      ' <a onclick="handleDelete('+requirement.id+', \'requirement\')" href="javascript:{}">拒绝</a>' +
                      '</td>';
            }
            if(requirement.status == '1'){
              html += '<td>对接中</td>';
              html += '<td>' +
                      ' <a onclick="handleStop('+requirement.id+', \'requirement\')" href="javascript:{}">暂停</a>' +
                      ' <a onclick="handleClose('+requirement.id+', \'requirement\')" href="javascript:{}">关闭</a>' +
                      '</td>';
            }
            if(requirement.status == '2') {
              html += '<td>已暂停</td>';
              html += '<td>' +
                      ' <a onclick="handleRecovery('+requirement.id+', \'requirement\')" href="javascript:{}">恢复</a>' +
                      '</td>';
            }
            if(requirement.status == '3') {
              html += '<td>已关闭</td>';
              html += '<td></td>';
            }

            html += '</tr>';
            $(html).appendTo($container);
          }
        } else {
          $('<tr><td colspan="5"><span class="text-info">没有待审核需求</span></td></tr>').appendTo($container);
        }

        if (resp.total > 0) {
          $('#rPager').twbsPagination({
            totalPages: resp.total,
            visiblePages: 5,
            onPageClick: function (event, page) {
              loadRequirements(page);
            }
          });
        }
      }, 'json');
    }

    loadRequirements(1);
  </script>
@endsection

@section('content')
  <div class="page-header">
    <h3>需求信息列表
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

  <div id="btnRequirement">
    <table class="table table-striped" id="tblRequirements">
      <thead>
      <tr>
        <th>需求标题</th>
        <th>有效日期</th>
        <th>联系人</th>
        <th>手机号</th>
        <th>描述</th>
        <th>状态</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <div id="rPager"></div>
  </div>



@endsection