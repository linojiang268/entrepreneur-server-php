function dateCompare(startDate, endDate) {
    var arr = startDate.split("-");
    var startTime = new Date(arr[0], arr[1], arr[2]);
    var startTimes = startTime.getTime();
    var arrs = endDate.split("-");
    var endTime = new Date(arrs[0], arrs[1], arrs[2]);
    var endTimes = endTime.getTime();
    if ( startTimes < endTimes ) {
        return true;
    }else{
        return false;
    }
}

function approveSponsorshipApplication(sponsorshipId, applicationId, page){
    var token = $('input[name=_token]').val();
    $.post('/sponsorships/'+ sponsorshipId +'/applications/'+ applicationId +'/approve',
        {'memo' : '', '_token' : token},
        function(resp){
            if (resp.code != 0) {
                showStoreError(resp.message);
            }else{
                loadSponsorships(page);
            }
        },
        'json');
}

function rejectSponsorshipApplication(sponsorshipId, applicationId, page){
    var token = $('input[name=_token]').val();
    $.post('/sponsorships/'+ sponsorshipId +'/applications/'+ applicationId +'/reject',
        {'memo' : '', '_token' : token},
        function(resp){
            if (resp.code != 0) {
                showStoreError(resp.message);
            }else{
                loadSponsorships(page);
            }
        },
        'json');
}

function loadSponsorships(page) {
    var id = $('#sponsorshipId').val();
    $.get('/sponsorships/'+ id +'/applications?page=' + page, function(resp) {
        if (resp.code != 0) {
            // notify user that error happens
            return;
        }
        var $table = $('#tblSponsorships'),
            sponsorshipApplications = resp.applications;
        var $container = $('tbody', $table);
        $container.html(''); // clear old data
        if (sponsorshipApplications && sponsorshipApplications.length) {
            for (var i = 0, n = sponsorshipApplications.length; i < n; i++) {
                var application = sponsorshipApplications[i];
                var html = '<tr>';
                html += '<td>' + application.team_name + '</td>';
                html += '<td>' + application.contact_user + '</td>';
                html += '<td>' + application.mobile + '</td>';
                html += '<td>' + application.application_reason + '</td>';
                if (application.status == 0){
                    html += '<td>未操作</td>';
                    html += '<td><a href="javascript:{}" onclick="approveSponsorshipApplication('+ application.sponsorship_id +', '+ application.id +', '+ page +')">通过</a> <a href="javascript:{}" onclick="rejectSponsorshipApplication('+ application.sponsorship_id +', '+ application.id +', '+ page +')">拒绝</a></td>';
                }else if(application.status == 1){
                    html += '<td>已通过</td>';
                    html += '<td>线下沟通</td>';
                }else{
                    html += '<td>已拒绝</td>';
                    html += '<td><a href="javascript:{}" onclick="approveSponsorshipApplication('+ application.sponsorship_id +', '+ application.id +', '+ page +')">通过</a></td>';
                }
                html += '</tr>';
                $(html).appendTo($container);
            }
        } else {
            $('<tr><td colspan="6"><span class="text-info">您还没有接收到赞助申请</span></td></tr>').appendTo($container);
        }

        if (resp.totalPages > 0) {
            $('#pager').twbsPagination({
                totalPages: resp.totalPages,
                visiblePages: 5,
                onPageClick: function (event, page) {
                    loadSponsorships(page);
                }
            });
        }
    }, 'json');
}


$(function () {
    function showStoreError(msg) {
        if (msg == '') {
            //$('#divStoreError').hide();
            $('#divStoreError').modal('hide');
        } else {
            $('.message', '#divStoreError').html(msg);
            //$('#divStoreError').show();
            $('#divStoreError').modal('show');
        }
    }

    function checkParam(obj){
        var $form = obj.closest("form"),
            $name = $('input[name=name]', $form),
            $fromDate = $('input[name=application_start_date]', $form),
            $toDate = $('input[name=application_end_date]', $form),
            $sponsorIntro = $('textarea[name=intro]', $form),
            $applicantIntro = $('textarea[name=application_condition]', $form);

        if ($name.val().trim() == '') {
            showStoreError('赞助标题必须填写');
            return;
        }
        if ($fromDate.val() == '' || $toDate.val() == '') {
            showStoreError('赞助申请周期必须填写');
            return;
        }
        if ($sponsorIntro.val().trim() == '') {
            showStoreError('赞助介绍必须填写');
            return;
        }
        if ($applicantIntro.val().trim() == '') {
            showStoreError('赞助申请要求必须填写');
            return;
        }
        if (!dateCompare($fromDate.val(), $toDate.val())) {
            showStoreError('赞助申请周期填写错误');
            return;
        }
        return $form;
    }

    function submitSponsorship($form){
        ajaxSubmit($form, {
            beforeSend: function (xhr) {
                $('#divStoreIndicator').show();
            },
            success: function (resp) {
                $('#divStoreIndicator').hide();
                if (resp.code != 0) {
                    showStoreError(resp.message);
                } else { // login success
                    if(resp.redirect){
                        window.location.href = resp.redirect;
                    }else{
                        window.location.href = '/sponsorships';
                    }
                }
            },
            statusCode: {
                500: function () {
                    showStoreError('服务器异常，请稍后重试。');
                }
            },
            error: function () {
                $('#divStoreIndicator').hide();
            }
        });
    }

    $('#btnStore').click(function () {
        $("#status").val(0);
        var $form = checkParam($(this));
        if(!$form){
            return;
        }
        submitSponsorship($form);
    });

    $('#btnStorePublish').click(function () {
        $("#status").val(1);
        var $form = checkParam($(this));
        if(!$form){
            return;
        }
        submitSponsorship($form);
    });

    $('#btnUpdate').click(function () {
        $("#status").val(0);
        var $form = checkParam($(this));
        if(!$form){
            return;
        }
        var id = $('input[name=id]', $form);
        $form.attr('method', 'PUT');
        $form.attr('action', '/sponsorships/'+id.val());
        submitSponsorship($form);
    });

    $('#btnPublish').click(function () {
        $("#status").val(1);
        var $form = checkParam($(this));
        if(!$form){
            return;
        }
        var id = $('input[name=id]', $form);
        $form.attr('method', 'PUT');
        $form.attr('action', '/sponsorships/'+id.val());
        submitSponsorship($form);
    });

    $('#btnDelete').click(function () {
        var $form = $(this).closest("form");
        var id = $('input[name=id]', $form);
        $form.attr('method', 'DELETE');
        $form.attr('action', '/sponsorships/'+id.val());
        submitSponsorship($form);
    });

    $('#btnDelay').click(function () {
        var $form = $(this).closest("form");
        var id = $('input[name=id]', $form);
        $form.attr('action', '/sponsorships/'+id.val()+'/postpone');
        submitSponsorship($form);
    });

    $('#btnClose').click(function () {
        var $form = $(this).closest("form");
        var id = $('input[name=id]', $form);
        $form.attr('action', '/sponsorships/'+id.val()+'/close');
        submitSponsorship($form);
    });

    $('#btnCancel').click(function () {
        $("#name").val("");
        $("#from_date").val("");
        $("#to_date").val("");
        $("#sponsor_intro").val("");
        $("#applicant_intro").val("");
    });

    $('#goBack').click(function(){
        window.location.href = '/sponsorships';
    });

    $('#btnDetail').click(function(){
        $('#detail').show();
        $('#applicationList').hide();
        $('#dashboard').hide();
        $('#btnDetail').attr('class', 'active');
        $('#btnApplicationList').attr('class', '');
        $('#btnDashboard').attr('class', '');
    });

    $('#btnApplicationList').click(function(){
        $('#detail').hide();
        $('#applicationList').show();
        $('#dashboard').hide();
        console.log();
        $('#btnDetail').attr('class', '');
        $('#btnApplicationList').attr('class', 'active');
        $('#btnDashboard').attr('class', '');
        loadSponsorships(1);
    });

    $('#btnDashboard').click(function(){
        $('#detail').hide();
        $('#applicationList').hide();
        $('#dashboard').show();
        $('#btnDetail').attr('class', '');
        $('#btnApplicationList').attr('class', '');
        $('#btnDashboard').attr('class', 'active');
    });
});