import React, {Component} from 'react/addons';

import {SideBar, Entry, Separator, App as AppContainer, Content, ControlBar, Settings} from 'oc-react-components';
import Timestamp from 'react-time';
import ReactList from 'react-list';

import {LockType} from './Components/LockType';
import {LockState} from './Components/LockState'
import {ToggleEntry} from './Components/ToggleEntry';

import {LogProvider} from './Providers/LogProvider';

import style from '../css/app.less';

export class App extends Component {
	state = {
		entries: [],
		showState: 0,
		timeout: 600,
		live: true
	};

	constructor () {
		super();
		this.logProvider = new LogProvider();
		this.setTimeoutOnServer = _.debounce(this.logProvider.setTimeout, 500);
	}

	componentDidMount = async() => {
		const entries = await this.logProvider.getEntries();
		const timeout = await this.logProvider.getTimeout();
		this.setState({entries, timeout});
		this.listen();
	};

	listen () {
		this.logProvider.listen(this.state.entries[0].key, (lock) => {
			const entries = this.state.entries;
			entries.unshift(lock);
			this.setState({entries});
		});
	}

	toggleShowState (showState) {
		this.setState({showState});
	}

	renderRow = (index, key) => {
		const entry = this.state.entries[index];
		const onClick = (entry.event === 'error') ? function () {
		} : this.toggleShowState.bind(this, entry.key);
		const className = (entry.event === 'error') ? style.error : style.event;
		const event = (entry.event === 'error') ? 'Error on ' + entry.params.operation : entry.event;
		return (
			<tr key={key} className={className}
				onClick={onClick}>
				<td className={style.time}><Timestamp
					value={entry.time * 1000}
					relative
					titleFormat="HH:mm:ss.SSS"/>
				</td>
				<td className={style.event}>{event}</td>
				<td className={style.path}>{entry.path}</td>
				<td className={style.type}>
					<LockType type={entry.params.type}/>
				</td>
			</tr>
		)
	};

	renderer = (items, ref) => {
		return (<table className={style.locklog}>
			<thead>
			<tr>
				<th className={style.time}>Time</th>
				<th className={style.event}>Event</th>
				<th className={style.path}>Path</th>
				<th className={style.type}>Type</th>
			</tr>
			</thead>
			<tbody ref={ref}>
			{items}
			</tbody>
		</table>);
	};

	setTimeout = (event) => {
		const timeout = event.target.value * 60;
		this.setState({timeout});
		this.setTimeoutOnServer(timeout);
	};

	toggleLive = (live) => {
		this.logProvider.listening = live;
	};

	render () {
		return (
			<AppContainer appId="react_oc_boilerplate">
				<SideBar>
					<ToggleEntry active={this.state.live}
								 onChange={this.toggleLive}>Live
						Update</ToggleEntry>

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
					<ReactList
						itemRenderer={this.renderRow}
						itemsRenderer={this.renderer}
						length={this.state.entries.length}
						type='uniform'
						/>
				</Content>
			</AppContainer>
		);
	}
}
