import React, {Component} from 'react/addons';

import {SideBar, Entry, Separator, App as AppContainer, Content, ControlBar, Settings} from 'oc-react-components';
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

					<Settings>
						<h2>
							<label htmlFor="log-timeout">Save entries
								for</label>
						</h2>
						<input id="log-timeout" type="number"
							   onChange={this.setTimeout}
							   value={this.state.timeout/60}
							></input><span>Minutes</span>
					</Settings>
				</SideBar>

				<Content>
					{page}
				</Content>
			</AppContainer>
		);
	}
}
