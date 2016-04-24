import React from 'react';
import {Button, Cells, CellsTitle, Cell, CellHeader, CellBody, Dialog, Toast} from 'react-weui';

import isValidMobile from '../validations/mobile';
import UserService from '../services/user';
import AppState from '../state';

class Home extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            account: (AppState.user && AppState.user.mobile) || (this.props.location.state && this.props.location.state.user) || '',
            password: '',

            error: '',  // error message
            title: '错误',   // error  title

            loadingText: '' // text indicator while in the progress of loading
        };
    }

    handleLoginTapped() {
        this.closeMsgBox();  // clear previous error message box if any

        let account = this.state.account.trim(),
            password = this.state.password.trim();

        if (!isValidMobile(account)) {
            return this.showMsgBox('请填写合法的用户名');
        }

        if (!password.length) {
            return this.showMsgBox('请填写密码');
        }

        this.showLoadingBox('正在登录...');
        UserService.login(account, password, openId, {
                success: (resp) => {
                    this.closeLoadingBox();
                    if (resp.code != 0) {
                        return this.showMsgBox(resp.message);
                    }

                    // set the global user
                    let user = { name: resp.name, mobile: resp.mobile, business: resp.business };
                    AppState.user = user;
                    localStorage.setItem('user', JSON.stringify(user));

                    // redirect page
                    let nextPath = (this.props.location.state && this.props.location.state.nextPathname) ||
                                   '/requirements';
                    this.props.history.replace(nextPath);
                },
                fail: (errorText) => {
                    this.closeLoadingBox();
                    this.showMsgBox(errorText);
                }
            }
        )
    }

    closeMsgBox() {
        if (this.state.error != '') {
            this.setState({error: ''});
        }
    }

    showMsgBox(error, title) {
        this.setState({
            error,
            title: title || this.state.title
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

    render() {
        return (
            <div className="page">
                <div className="page_title">登录企业对接服务</div>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="home_cell_label">用户名</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="tel"
                                   placeholder="手机号" defaultValue={this.state.account}
                                   onChange={(event) => this.setState({ account: event.target.value })}/>
                        </CellBody>
                    </Cell>

                    <Cell className="list_item">
                        <CellHeader>
                            <label className="home_cell_label">密码</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="password"
                                   placeholder="密码" defaultValue={this.state.password}
                                   onChange={(event) => this.setState({ password: event.target.value })}/>
                        </CellBody>
                    </Cell>
                </Cells>

                {/* login button */}
                <Button type="primary" className="form_btn" onClick={this.handleLoginTapped.bind(this)}>登录</Button>

                <div style={{ height: 32, fontSize: 12, padding: '32px 8px 8px 16px' }}>
                    <a style={{ color: '#60b044' }} href="#/register">没有加盟?</a>
                </div>

                {/* error message dialog */}
                <Dialog.Alert show={this.state.error != ''} title={this.state.title}
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


export default Home;