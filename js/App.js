import React, {Component} from 'react/addons';

import {SideBar, Entry, Separator, App as AppContainer, Content, ControlBar} from 'oc-react-components';
import {LockLog} from './Components/LockLog';

import {LogProvider} from './Providers/LogProvider';

import style from '../css/app.less';

export class App extends Component {
	state = {
		page: 'log'
	};

	constructor () {
		super();
		this.logProvider = new LogProvider();
	}

	onClick (page) {
		this.setState({
			page: page
		});
	}

	render () {
		let page = null;
		if (this.state.page === 'log') {
			page = (
				<LockLog provider={this.logProvider}/>
			);
		}

		return (
			<AppContainer appId="react_oc_boilerplate">
				<SideBar withIcon={true}>
					<Entry icon='home' onClick={this.onClick.bind(this,'log')}>
						Log
					</Entry>
				</SideBar>

				<Content>
					{page}
				</Content>
			</AppContainer>
		);
	}
}
