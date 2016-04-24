<?php

Route::group(['prefix' => 'api', 'namespace' => 'Api', 'middleware' => ['web', 'csrf']], function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::get('requirement/list', 'RequirementController@requirementApiList');
    Route::get('requirement/{id}/detail', 'RequirementController@getApiRequirementDetail');
    Route::post('requirement/search', 'RequirementController@searchApiRequirement');
    Route::get('application/{id}/detail', 'ApplicationController@getApplicationApiDetail');

    Route::group([ 'middleware' => 'auth'], function () {
        Route::post('password/change', 'AuthController@changePassword');
        Route::get('requirement/mylist', 'RequirementController@myRequirementList');
        Route::post('requirement/create', 'RequirementController@createRequirement');
        Route::get('application/mylist', 'ApplicationController@myApplicationsList');
        Route::post('application/create', 'ApplicationController@createApplication');
    });
});

Route::group(['prefix' => 'web', 'namespace' => 'Api', 'middleware' => ['web', 'csrf']], function () {
    Route::get('requirement/view/list', 'RequirementController@requirementBackstageListView');
    Route::get('requirement/list', 'RequirementController@requirementBackstageList');
    Route::get('requirement/auditing/list', 'RequirementController@requirementBackstageAuditingList');

    Route::get('requirement/{id}/detail', 'RequirementController@getBackstageRequirementDetail');
    Route::post('requirement/search', 'RequirementController@searchBackstageRequirement');
    Route::get('requirement/{id}/approve', 'RequirementController@approve');
    Route::get('requirement/{id}/stop', 'RequirementController@stop');
    Route::get('requirement/{id}/close', 'RequirementController@close');
    Route::get('requirement/{id}/delete', 'RequirementController@delete');
    Route::get('requirement/{id}/recovery', 'RequirementController@recovery');

    Route::get('application/list', 'ApplicationController@pendingApplicationsList');
    Route::get('application/auditing/list', 'ApplicationController@applicationBackstageAuditingList');
    Route::get('application/view/list', 'ApplicationController@applicationBackstageListView');

    Route::get('application/{id}/detail', 'ApplicationController@getApplicationBackstageDetail');
    Route::post('application/create', 'ApplicationController@createApplication');
    Route::get('application/{id}/approve', 'ApplicationController@approve');
    Route::get('application/{id}/failure', 'ApplicationController@failure');
    Route::get('application/{id}/success', 'ApplicationController@success');
    Route::get('application/{id}/delete', 'ApplicationController@delete');

    Route::get('/', 'RequirementController@requirementListPage');

    Route::get('user/auditing/list', 'AuthController@pendingList');
    Route::get('user/list', 'AuthController@backstageList');
    Route::get('user/view/list', 'AuthController@userBackstageListView');

    Route::get('user/{id}/approve', 'AuthController@approve');
    Route::get('user/{id}/delete', 'AuthController@delete');
    Route::get('user/{id}/reset', 'AuthController@resetPasswordBackstage');
});



Route::group(['prefix' => 'wap', 'namespace' => 'Wap' ], function () {
    // notification from wx
    Route::post('weixin/msg', 'WeixinController@msg');
    Route::get('weixin/msg', 'WeixinController@msg');

    Route::group(['prefix' => 'weixin', 'middleware' => ['csrf']], function () {
        Route::get('/', 'WeixinController@index');
        Route::get('oauth/go', 'WeixinController@goToOauth');
        Route::get('oauth', 'WeixinController@doOauth');
    });
});
