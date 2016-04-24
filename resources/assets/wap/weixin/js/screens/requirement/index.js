import React from 'react';

import {Button, Dialog, Toast} from 'react-weui';
import {Link} from 'react-router';

import RequirementService from '../../services/requirement';
import TimeUtil from '../../utils/time';
import AppState from '../../state';

class Requirements extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            requirements: [],
            hasMore: false,

            error: '',
            errorTitle: '错误',
            loadingText: ''
        };

        this.page = 1;
        this.size = 10;
    }

    componentDidMount() {
        this.fetchRequirements(this.page);
    }

    closeMsgBox() {
        if (this.state.error != '') {
            this.setState({error: ''});
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

    fetchRequirements(page, cb) {
        this.setState({
            loadingText: '正在加载...',
            error: ''
        });
        RequirementService.list(page, this.size, {
            success: (resp) => {
                if (resp.code != 0) {
                    cb && cb(false);
                    return this.showMsgBox(resp.message);
                }

                var requirements = resp.requirements || [];
                if (!requirements.length) {
                    cb && cb(false);
                    return;
                }

                requirements = this.state.requirements.concat(requirements);
                let hasMore = requirements.length < resp.total;

                this.setState({
                    requirements,
                    hasMore
                });

                cb && cb(true);
            },
            fail: (errorText) => {
                cb && cb(false);
                this.showMsgBox(errorText);
            },
            always: () => {
                this.closeLoadingBox();
            }
        });
    }

    fetchMoreRequirements() {
        if (!this.state.hasMore) {
            return;
        }

        this.fetchRequirements(this.page + 1, (success) => {
            if (success) {
                this.page++;
            }
        });
    }

    _renderRequirements() {
        return (
            this.state.requirements.map((requirement, index) => {
                return (
                    <div key={'req.' + index} className="req_item">
                        <div className="req_name">
                            { requirement.title }
                        </div>
                        <div className="req_actions">
                            { AppState.user != null ? (
                                <Link to={`/requirement/apply/${requirement.id}`}>申请</Link>
                            ) : (
                                <a href='#/'>申请</a>
                            )}
                        </div>
                        <div className="req_line">
                            <span className="req_publisher">{ requirement.contacts }</span>
                            { requirement.begin_time } ~ { requirement.end_time }

                            <div className="req_elapsed">{ TimeUtil.elapsed(requirement.created_at) }</div>
                        </div>
                        <div className="req_desc">{ requirement.intro }</div>
                    </div>
                );
            })
        );

    }

    _renderEmptyRequirements() {
        if (this.state.loadingText != '') {
            return null;
        }

        return (
            <div className="big_info">
                暂无企业需求信息
            </div>
        );
    }

    _renderHasMore() {
        return (
            <div style={{ marginTop: 16, paddingLeft: 8, paddingRight: 8 }}>
                <Button type="primary" onClick={this.fetchMoreRequirements.bind(this)}>加载更多需求信息</Button>
            </div>
        );
    }

    render() {
        let requirements = this.state.requirements.length ? this._renderRequirements()
                                                          : this._renderEmptyRequirements();
        let more = this.state.hasMore ? this._renderHasMore() : null;


        return (
            <div className="page">
                <div className="page_title">企业需求</div>

                { requirements }
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

export default Requirements;