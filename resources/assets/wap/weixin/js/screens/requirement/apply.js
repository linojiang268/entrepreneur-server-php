import React from 'react';
import {Button, Cells, CellsTitle, Cell, CellHeader, CellBody, Dialog, Toast} from 'react-weui';

import isValidMobile from '../../validations/mobile';
import RequirementService from '../../services/requirement';
import AppState from '../../state';

class ApplyRequirement extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            mobile: (AppState.user && AppState.user.mobile) || '',
            contact: (AppState.user && AppState.user.name) || '',
            description: '',

            error: '',
            errorTitle: '错误',
            loadingText: ''
        };

        this.success = false;
        this.authed = true;
        this.applied = false;  // applied previously or not
    }

    closeMsgBox() {
        if (this.state.error != '') {
            this.setState({error: ''});
        }

        if (this.applied) {
            return this.props.history.replace('/requirements');
        }

        if (!this.authed) {
            return this.props.history.replaceState({
                state: { nextPathname: this.props.location.pathname }
            }, '/');
        }

        if (this.success) {
            this.props.history.replace('/requirements');
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
        this.setState({ loadingText: '' });
    }

    handleApplyTapped() {
        let requirementId = this.props.params.requirementId;
        if (!requirementId) {
            return;
        }

        let mobile = this.state.mobile.trim(),
            contact = this.state.contact.trim(),
            description = this.state.description.trim();

        if (!isValidMobile(mobile)) {
            return this.showMsgBox('请填写正确的手机号');
        }
        if (!contact.length) {
            return this.showMsgBox('请填写联系人');
        }
        if (!description.length) {
            return this.showMsgBox('请填写申请描述');
        }

        this.showLoadingBox('提交申请...');
        RequirementService.apply(requirementId, mobile, contact, description, {
            success: (resp) => {
                console.log(resp);
                if (resp.code != 0) {
                    this.applied = (resp.code == 9000);
                    return this.showMsgBox(resp.message);
                }

                this.success = true;
                this.showMsgBox('您的申请已接收,我们将尽快与您取得联系', '成功');
            },
            fail: (errorText, xhr) => {
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

    render() {
        return (
            <div className="page">
                <div className="page_title">申请项目</div>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="pubreq_cell_label">联系电话</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="tel"
                                   placeholder="手机号" defaultValue={this.state.mobile}
                                   onChange={(event) => this.setState({ mobile: event.target.value })}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item">
                        <CellHeader>
                            <label className="pubreq_cell_label">联系人</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text"
                                   placeholder="联系人" defaultValue={this.state.contact}
                                   onChange={(event) => this.setState({ contact: event.target.value })}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item textarea_weui_cell">
                        <CellHeader>
                            <label className="pubreq_cell_label">申请描述</label>
                        </CellHeader>
                        <CellBody>
                            <textarea className="weui_input" rows="3"
                                      placeholder="描述您的申请" defaultValue={this.state.description}
                                      onChange={(event) => this.setState({ description: event.target.value })}/>
                        </CellBody>
                    </Cell>

                </Cells>

                {/* login button */}
                <Button type="primary" className="form_btn" onClick={this.handleApplyTapped.bind(this)}>申请</Button>

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

export default ApplyRequirement;