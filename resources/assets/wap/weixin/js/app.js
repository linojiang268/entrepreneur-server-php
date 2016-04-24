import React from 'react';
import { render } from 'react-dom';
import { Router, Route, browserHistory, IndexRoute } from 'react-router';

// // import application pages
import App from './screens/app';
import Home from './screens/home';
import Register from './register';
import Requirements from './screens/requirement/index';
import PublishRequirement from './screens/requirement/publish';
import ApplyRequirement from './screens/requirement/apply';
import MyRequirements from './screens/my/requirements';
import MyApplications from './screens/my/applications';
import Profile from './screens/my/profile';
import AppState from './state';

// setup ftranslation for pickadate
$.extend($.fn.pickadate.defaults, {
    monthsFull: [ '一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月' ],
    monthsShort: [ '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二' ],
    weekdaysFull: [ '星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六' ],
    weekdaysShort: [ '日', '一', '二', '三', '四', '五', '六' ],
    today: '今天',
    clear: '',
    close: '关闭',
    firstDay: 1,
    format: 'yyyy/mm/dd',
    formatSubmit: 'yyyy/mm/dd'
});

// extend jquery date format
$.formatDate = function(dateObject) {
    var d = new Date(dateObject);
    var day = d.getDate();
    var month = d.getMonth() + 1;
    var year = d.getFullYear();
    if (day < 10) {
        day = '0' + day;
    }
    if (month < 10) {
        month = '0' + month;
    }
    return year + '/' + month + '/' + day;
};

function requireAuth(nextState, replaceState) {
    if (AppState.user == null) {
        replaceState({
            state: { nextPathname: nextState.location.pathname }
        }, '/');
    }
}

// load app state from storage
if (localStorage.getItem('user') != null) {
    AppState.user = JSON.parse(localStorage.getItem('user'));
}

render((
    <Router history={browserHistory}>
        <Route path="/" component={App}>
            <IndexRoute component={Home} />
            <Route path="/register" component={Register} />

            <Route path="/requirements" component={Requirements} />
            <Route path="/requirement/publish" component={PublishRequirement} onEnter={requireAuth} />
            <Route path="/requirement/apply/:requirementId" component={ApplyRequirement} onEnter={requireAuth} />

            <Route path="/my/requirements" component={MyRequirements} onEnter={requireAuth} />
            <Route path="/my/applications" component={MyApplications} onEnter={requireAuth} />

            <Route path="/my/profile" component={Profile} onEnter={requireAuth} />

        </Route>
    </Router>
), document.getElementById('app'));

