import React from 'react';

import {Button, Dialog, Toast} from 'react-weui';
import ApplicationService from '../../services/application';

class MyApplications extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            applications: [],
            hasMore: false,

            error: '',
            errorTitle: '错误',
            loadingText: ''
        };

        this.page = 1;
        this.size = 10;
    }

    componentDidMount() {
        this.fetchApplications(this.page);
    }

    closeMsgBox() {
        if (this.state.error != '') {
            this.setState({error: ''});
        }

        if (!this.authed) {
            this.props.history.replaceState({
                state: { nextPathname: this.props.location.pathname }
            }, '/');
        }
    }

    showMsgBox(error, title) {
        this.setState({
            error,
            errorTitle: title || this.state.errorTitle
        });
    }

    showLoadingBox(text) {
        this.setState({ loadingText: text });
    }

    closeLoadingBox() {
        if (this.state.loadingText != '') {
            this.setState({loadingText: ''});
        }
    }

    fetchApplications(page, cb) {
        this.setState({
            loadingText: '正在加载...',
            error: ''
        });
        ApplicationService.mylist(page, this.size, {
            success: (resp) => {
                if (resp.code != 0) {
                    cb && cb(false);
                    return this.showMsgBox(resp.message);
                }

                var applications = resp.applications || [];
                if (!applications.length) {
                    cb && cb(false);
                    return;
                }

                applications = this.state.applications.concat(applications);
                let hasMore = applications.length < resp.total;

                this.setState({
                    applications,
                    hasMore
                });

                cb && cb(true);
            },
            fail: (errorText, xhr) => {
                cb && cb(false);
                if (xhr.status == 401) {
                    this.authed = false;
                    return this.showMsgBox('您需要登录系统');
                }

                this.showMsgBox(errorText);
            },
            always: () => {
                this.closeLoadingBox();
            }
        });
    }

    fetchMoreApplications() {
        if (!this.state.hasMore) {
            return;
        }

        this.fetchApplications(this.page + 1, (success) => {
            if (success) {
                this.page++;
            }
        });
    }

    _applicationStatusToText(status) {
        switch (status) {
            case 0:
                return '审核中';
            case 1:
                return '对接中';
            case 2:
                return '已达成';
            case 3:
                return '未达成';
        }

        return '';
    }

    _renderApplications() {
        return (
            this.state.applications.map((application, index) => {
                let requirement = application.requirement;
                return (
                    <div key={'req.' + index} className="req_item">
                        <div className="req_name">
                            { requirement.title }
                            <div style={{ position: 'absolute', right: 0, top: 0, fontSize: 14, color: '#60b044', paddingRight: 8, paddingTop: 8 }}>{ this._applicationStatusToText(requirement.status) }</div>
                        </div>
                        <div className="req_line">
                            <span className="req_publisher">{ requirement.contacts }</span>
                            { requirement.begin_time } ~ { requirement.end_time }

                            <div className="req_elapsed">{ requirement.created_at }</div>
                        </div>
                        <div className="req_desc">{ requirement.intro }</div>
                        <div style={{borderTop: '1px solid rgba(218, 214, 214,0.6)', height: 1}}></div>
                        <div className="req_desc" style={{ marginTop: 8 }}>{ application.intro }</div>
                    </div>
                );
            })
        );
    }

    _renderEmptyApplications() {
        if (this.state.loadingText != '') {
            return null;
        }

        return (
            <div className="big_info">
                暂无申请
            </div>
        );
    }

    _renderHasMore() {
        return (
            <div style={{ marginTop: 16, paddingLeft: 8, paddingRight: 8 }}>
                <Button type="primary" onClick={this.fetchMoreApplications.bind(this)}>加载更多申请</Button>
            </div>
        );
    }

    render() {
        let applications = this.state.applications.length ? this._renderApplications()
            : this._renderEmptyApplications();
        let more = this.state.hasMore ? this._renderHasMore() : null;

        return (
            <div className="page">
                <div className="page_title">我的申请</div>

                { applications }
                { more }

                {/* error message dialog */}
                <Dialog.Alert show={this.state.error != ''} title={this.state.errorTitle}
                              buttons={[ {label: '确定', onClick: this.closeMsgBox.bind(this)} ]}>
                    { this.state.error }
                </Dialog.Alert>

                {/* the loading dialog/toast */}
                <Toast show={this.state.loadingText != ''} icon="loading">
                    { this.state.loadingText }
                </Toast>
            </div>
        );
    }
}

export default MyApplications;