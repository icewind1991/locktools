import React, {Component} from 'react/addons';

import {SideBar, Entry, Separator, App as AppContainer, Content, ControlBar} from 'oc-react-components';
import {LockLog} from './Components/LockLog';

import {LogProvider} from './Providers/LogProvider';

import style from '../css/app.less';

export class App extends Component {
	state = {
		page: 'log',
		showSettings: false,
		timeout: 600
	};

	constructor () {
		super();
		this.logProvider = new LogProvider();
		this.setTimeoutOnServer = _.debounce(this.logProvider.setTimeout, 500);
	}

	componentDidMount = async() => {
		const timeout = await this.logProvider.getTimeout();
		console.log(timeout);
		this.setState({timeout});
	};

	onClick (page) {
		this.setState({
			page: page
		});
	}

	toggleSettings = ()=> {
		const showSettings = !this.state.showSettings;
		this.setState({showSettings});
	};

	setTimeout = (event) => {
		const timeout = event.target.value * 60;
		this.setState({timeout});
		this.setTimeoutOnServer(timeout);
	};

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

					<div id="app-settings">
						<div id="app-settings-header">
							<button className="settings-button"
									onClick={this.toggleSettings}>Settings
							</button>
						</div>
						<div id="app-settings-content"
							 style={this.state.showSettings?{display:'block'}:{}}>
							<h2>
								<label for="log-timeout">Save entries
									for</label>
							</h2>
							<input id="log-timeout" type="number"
								   onChange={this.setTimeout}
								   value={this.state.timeout/60}
								></input><span>Minutes</span>
						</div>
					</div>
				</SideBar>

				<Content>
					{page}
				</Content>
			</AppContainer>
		);
	}
}
