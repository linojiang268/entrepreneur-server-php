import React from 'react';
import {Link} from 'react-router';
import UserService from '../services/user';
import AppState from '../state';
import {Dialog, Toast} from 'react-weui';

class App extends React.Component {
    constructor(props) {
        super(props);

        this.ignoreUserInfos = [ ];
        this.state = {
            loadingText: '',
            error: '',
            errorTile: ''
        };
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
        this.setState({ loadingText: '' });
    }


    render() {
        let pathname = this.props.children.props.location.pathname;

        let user = (this.ignoreUserInfos.indexOf(pathname) != -1) ? null : (
            <div className="menu">
                {
                    pathname == '/' ? null : (
                        <span className={ pathname == '/my/profile' ? 'menu_item menu_item_active' : 'menu_item'}>
                            { AppState.user != null ? <a href="#/my/profile" style={{color: 'rgba(0, 0, 0, 0.8)'}}>{ AppState.user.name } </a> :
                                (pathname == '/' ? null : <a href="#/">登录</a>)
                            }
                        </span>
                    )
                }

                <div className="user_categories">
                     <span className={ pathname == '/requirements' ? 'menu_item menu_item_active' : 'menu_item'}>
                        <Link to="/requirements">企业需求</Link>
                    </span>

                    {
                        AppState.user != null ? (
                            <span className={ pathname == '/my/requirements' ? 'menu_item menu_item_active' : 'menu_item'}>
                                 <Link to="/my/requirements">我的需求</Link>
                            </span>
                        ) : null
                    }

                    {
                        AppState.user != null ? (
                        <span className={ pathname == '/my/applications' ? 'menu_item menu_item_active' : 'menu_item'}>
                                 <Link to="/my/applications">我的申请</Link>
                            </span>
                        ) : null
                    }
                </div>
            </div>
        );

        return (
            <div>
                { user }
                {this.props.children}

                {/* error message dialog */}
                <Dialog.Alert show={this.state.error != ''} title={this.state.title}
                              buttons={[ {label: '确定', onClick: this.closeMsgBox.bind(this)} ]}>
                    { this.state.error }
                </Dialog.Alert>

                <Toast show={this.state.loadingText != ''} icon="loading">
                    { this.state.loadingText }
                </Toast>
            </div>
        );
    }
}

export default App;