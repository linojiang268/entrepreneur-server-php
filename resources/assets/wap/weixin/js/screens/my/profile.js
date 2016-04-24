import React from 'react';
import {Button, Cells, CellsTitle, Cell, CellHeader, CellBody, Dialog, Toast} from 'react-weui';
import AppState from '../../state';
import UserService from '../../services/user';

class Profile extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            changePassword: false,
            changePasswordError: '',

            error: '',
            errorTitle: '错误',
            loadingText: ''
        };

        this.user = AppState.user;
        this.authed = true;
    }

    logout() {
        this.showLoadingBox('正在退出...');
        UserService.logout({
            success: (resp) => {
                this.closeLoadingBox();
                localStorage.removeItem('user');

                let name = this.user.name;
                AppState.user = null;
                return this.props.history.replaceState({
                    state: { user: name }
                }, '/');
            },
            fail: (errorText) => {
                this.closeLoadingBox();
                this.showMsgBox(errorText);
            }
        });
    }

    changePassword() {
        let oldPassword = this.refs.oldPassword.value.trim(),
            newPassword = this.refs.newPassword.value.trim();
        if (!oldPassword.length) {
            return this.setState({
                changePasswordError: '请输入旧密码'
            });
        }

        if (!newPassword.length) {
            return this.setState({
                changePasswordError: '请输入新密码'
            });
        }

        this.closeChangePasswordBox();
        this.showLoadingBox('正在修改密码...');
        UserService.changePassword(oldPassword, newPassword,{
            success: (resp) => {
                if (resp.code == 0) {
                    return this.showMsgBox('密码修改成功', '成功');
                }

                this.showMsgBox(resp.message);
            },
            fail: (errorText, xhr) => {
                if (xhr.status == 401) {
                    this.authed = false;
                    return this.showMsgBox('您需要先登录');
                }

                this.showMsgBox(errorText);
            },
            always: () => {

                this.closeLoadingBox();
            }
        });
    }

    showMsgBox(error, title) {
        this.setState({
            error,
            errorTitle: title || this.state.errorTitle
        });
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

    showLoadingBox(text) {
        this.setState({ loadingText: text });
    }

    closeLoadingBox() {
        if (this.state.loadingText != '') {
            this.setState({loadingText: ''});
        }
    }

    closeChangePasswordBox() {
        this.setState({
            changePassword: false,
            changePasswordError: ''
        });
    }

    render() {
        return (
            <div className="page">
                <div className="page_title">我的信息</div>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">用户名</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text" readOnly={true}
                                   placeholder="手机号" defaultValue={this.user.mobile}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">企业名称</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text" readOnly={true}
                                   placeholder="企业名称" defaultValue={this.user.business}/>
                        </CellBody>
                    </Cell>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="register_cell_label">联系人</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text" readOnly={true}
                                   placeholder="联系人" defaultValue={this.user.name}/>
                        </CellBody>
                    </Cell>
                </Cells>


                <Button type="primary" className="form_btn" onClick={() => {
                    this.setState({ changePassword: true });
                }}>修改密码</Button>

                {/* login button */}
                <Button type="warn" className="form_btn" onClick={this.logout.bind(this)}>退出</Button>

                {/* change password */}
                <Dialog.Alert show={this.state.changePassword } title="修改密码"
                              buttons={[ {label: '修改', onClick: this.changePassword.bind(this)},
                                         {label: '取消', onClick: this.closeChangePasswordBox.bind(this)} ]}>
                    <Cells form={true} access={true}>
                        <Cell className="list_item_dg">
                            <CellHeader>
                                <label className="register_cell_label">旧密码</label>
                            </CellHeader>
                            <CellBody>
                                <input ref="oldPassword" className="weui_input" type="password" />
                            </CellBody>
                        </Cell>

                        <Cell className="list_item_dg">
                            <CellHeader>
                                <label className="register_cell_label">新密码</label>
                            </CellHeader>
                            <CellBody>
                                <input ref="newPassword" className="weui_input" type="password" />
                            </CellBody>
                        </Cell>
                    </Cells>

                    <div style={{ color: 'red', padding: 8, fontSize: 14}}>
                        { this.state.changePasswordError }
                    </div>
                </Dialog.Alert>

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

export default Profile;