import React from 'react';
import {Button, Cells, CellsTitle, Cell, CellHeader, CellBody, Dialog, Toast} from 'react-weui';

import isValidMobile from '../../validations/mobile';
import RequirementService from '../../services/requirement';
import AppState from '../../state';

class PublishRequirement extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            title: '',
            description: '',
            startDate: $.formatDate(new Date()),
            endDate: $.formatDate(new Date((new Date()).valueOf() + 7 * 86400000)),
            mobile: (AppState.user && AppState.user.mobile) || '',
            contact: (AppState.user && AppState.user.name) || '',

            error: '',
            errorTitle: '错误',
            loadingText: ''
        };

        this.endDatePicker = null;
        this.success = false;
        this.authed = true;
    }

    componentDidMount() {
        let startDate = new Date(this.state.startDate),
            self = this;

        $(this.refs.startDate).pickadate({
            container: '#app',
            min: startDate,
            max: new Date(startDate.valueOf() + 90 * 86400000),
        }).pickadate('picker').set('select', startDate).on({
            close: function () {
                let newStartDate = this.get();
                if (newStartDate != self.state.startDate) { // changed
                    self.setState({ startDate: newStartDate });
                    self.setupEndDatePickerRange(Date.parse(newStartDate));
                }
            }
        });

        this.endDatePicker = $(this.refs.endDate).pickadate({
            today: '',
            container: '#app'
        }).pickadate('picker').on({
            close: function () {
                let newEndDate = this.get();
                if (newEndDate != self.state.endDate) { // changed
                    self.setState({ endDate: newEndDate });
                }
            }
        }).set('select', Date.parse(this.state.endDate));
        this.setupEndDatePickerRange(startDate);
    }

    setupEndDatePickerRange(date) {
        let minEndDate = new Date(date.valueOf() + 86400000),
            minEndDateText = $.formatDate(minEndDate),
            maxEndDate = new Date(minEndDate.valueOf() + 90 * 86400000),
            maxEndDateText = $.formatDate(maxEndDate);

        this.endDatePicker.set('min', minEndDate).set('max', maxEndDate);
        if (this.state.endDate < minEndDateText) {
            this.endDatePicker.set('select', minEndDate);
            this.setState({ endDate: minEndDateText });
        } else if (this.state.endDate > maxEndDateText) {
            this.endDatePicker.set('select', maxEndDate);
            this.setState({ endDate: maxEndDateText });
        }
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

        if (!this.authed) {
            return this.props.history.replaceState({
                state: { nextPathname: this.props.location.pathname }
            }, '/');
        }

        if (this.success) {
            this.props.history.push('/my/requirements');
        }
    }

    showLoadingBox(text) {
        this.setState({ loadingText: text });
    }

    closeLoadingBox() {
        this.setState({ loadingText: '' });
    }

    handlePublishTapped() {
        let title = this.state.title.trim(),
            description = this.state.description.trim(),
            startDate = this.state.startDate.trim(),
            endDate = this.state.endDate.trim(),
            mobile = this.state.mobile.trim(),
            contact = this.state.contact.trim();

        if (!title.length) {
            return this.showMsgBox('请填写项目名称');
        }
        if (!description.length) {
            return this.showMsgBox('请填写需求描述');
        }
        if (!startDate.length) {
            return this.showMsgBox('请填写开始日期');
        }
        if (!endDate.length) {
            return this.showMsgBox('请填写截止日期');
        }
        if (endDate < startDate) {
            return this.showMsgBox('请填写正确的项目日期');
        }
        if (!isValidMobile(mobile)) {
            return this.showMsgBox('请填写正确的联系电话');
        }
        if (!contact.length) {
            return this.showMsgBox('请填写联系人');
        }

        this.showLoadingBox('正在提交需求...');
        RequirementService.publish(title, description,
                                   startDate.replace(/\//g, '-'), endDate.replace(/\//g, '-'),
                                   mobile, contact, {
            success: (resp) => {
                if (resp.code != 0) {
                    return this.showMsgBox(resp.message);
                }

                this.success = true;
                this.showMsgBox('您的需求已接收,我们将尽快与您取得联系', '发布成功');
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
                <div className="page_title">发布项目需求</div>
                <Cells form={true} access={true}>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="pubreq_cell_label">项目名称</label>
                        </CellHeader>
                        <CellBody>
                            <input className="weui_input" type="text"
                                   placeholder="项目名称" defaultValue={this.state.title}
                                   onChange={(event) => this.setState({ title: event.target.value })}/>
                        </CellBody>
                    </Cell>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="pubreq_cell_label">开始日期</label>
                        </CellHeader>
                        <CellBody>
                            <input ref="startDate" className="weui_input" type="text"
                                   readOnly={true} defaultValue={this.state.startDate} />
                        </CellBody>
                    </Cell>
                    <Cell className="list_item">
                        <CellHeader>
                            <label className="pubreq_cell_label">截止日期</label>
                        </CellHeader>
                        <CellBody>
                            <input ref="endDate" className="weui_input" type="text"
                                   readOnly={true} defaultValue={this.state.endDate} />
                        </CellBody>
                    </Cell>
                    <Cell className="list_item textarea_weui_cell">
                        <CellHeader>
                            <label className="pubreq_cell_label">需求描述</label>
                        </CellHeader>
                        <CellBody>
                            <textarea className="weui_input" rows="2"
                                      placeholder="描述您的项目需求" defaultValue={this.state.description}
                                      onChange={(event) => this.setState({ description: event.target.value })}/>
                        </CellBody>
                    </Cell>
                </Cells>

                <CellsTitle>联系方式</CellsTitle>
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
                </Cells>

                {/* login button */}
                <Button type="primary" className="form_btn" onClick={this.handlePublishTapped.bind(this)}>发布</Button>

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

export default PublishRequirement;