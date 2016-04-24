import React from 'react';
import {Button, Cells, CellsTitle, Cell, CellHeader, CellBody, Dialog, Toast} from 'react-weui';

import UserService from './services/user';
import isValidMobile from './validations/mobile';

class Register extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            account: '',
            password: '',
            name: '',
            business: '',

            message: '',
            title: '',

            loadingText: ''
        };

        this.success = false;
    }

    handleRegisterTapped() {
        let account = this.state.account.trim(),
            password = this.state.password.trim(),
            business = this.state.business.trim(),
            name = this.state.name.trim();

        this.closeMsgBox();  // clear previous error message box if any

        if (!isValidMobile(account)) {
            return this.showMsgBox('请填写合法的用户名');
        }
        if (password.length < 6) {
            return this.showMsgBox('请填写合法的密码');
        }
        if (!business.length) {
            return this.showMsgBox('请填写企业名称');
        }
        if (!name.length) {
            return this.showMsgBox('请填写企业联系人');
        }

        this.showLoadingBox('提交加盟申请...');
        UserService.register(account, password, business, name, {
            success: (resp) => {
                if (resp.code != 0) {
                    return this.showMsgBox(resp.message);
                }

                this.success = true;
                this.showMsgBox('加盟申请已提交,我们将尽快与您取得联系');
            },
            fail: (errorText) => {
                this.showMsgBox(errorText);
            },
            always: () => {
                this.closeLoadingBox();
            }
        });
    }

    showMsgBox(message, title) {
        this.setState({
            message,
            title: title || this.state.title
        });
    }

    closeMsgBox() {
        this.setState({ message: '' });

        if (this.success) {
            window.location.href = '#/';
        }
    }

    showLoadingBox(text) {
        this.setState({ loadingText: text });
    }

    closeLoadingBox() {
        this.setState({ loadingText: '' });
    }


    render() {
        return (
            <div className="page">
                <div className="page_title">申请加盟</div>
                <CellsTitle>帐号信息</CellsTitle>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">用户名</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="tel"
                                   placeholder="手机号" defaultValue={this.state.account}
                                   onChange={(event) => this.setState({ account: event.target.value })}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">密码</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="password"
                                   placeholder="密码(至少6位)" defaultValue={this.state.password}
                                   onChange={(event) => this.setState({ password: event.target.value })}/>
                        </CellBody>
                    </Cell>
                </Cells>

                <CellsTitle>企业信息</CellsTitle>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">企业名称</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text"
                                   placeholder="企业名称" defaultValue={this.state.business}
                                   onChange={(event) => this.setState({ business: event.target.value })}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">联系人</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text"
                                   placeholder="联系人" defaultValue={this.state.name}
                                   onChange={(event) => this.setState({ name: event.target.value })}/>
                        </CellBody>
                    </Cell>
                </Cells>


                {/* login button */}
                <Button type="primary" className="form_btn" onClick={this.handleRegisterTapped.bind(this)}>申请加盟</Button>

                {/* error message dialog */}
                <Dialog.Alert show={this.state.message != ''} title={this.state.title}
                              buttons={[ {label: '确定', onClick: this.closeMsgBox.bind(this)} ]}>
                    { this.state.message }
                </Dialog.Alert>

                {/* the loading dialog/toast */}
                <Toast show={this.state.loadingText != ''} icon="loading">
                    { this.state.loadingText }
                </Toast>
            </div>
        );
    }
}

export default Register;