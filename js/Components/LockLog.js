import React, {Component} from 'react/addons';
import Timestamp from 'react-time';

import {LockType} from './LockType';
import {LockState} from './LockState'

import style from './LockLog.less'

export class LockLog extends Component {
	state = {
		entries: [],
		showStates: {}
	};

	componentDidMount = async() => {
		const provider = this.props.provider;
		const entries = await provider.getEntries();
		console.log(entries);
		this.setState({entries});
	};

	toggleShowState (key) {
		const showStates = this.state.showStates;
		showStates[key] = (showStates[key]) ? false : true;
		this.setState({showStates});
	}

	render () {
		const rows = this.state.entries.map(entry => {
			const onClick = (entry.event === 'error') ? function () {
			} : this.toggleShowState.bind(this, entry.key);
			const body = (this.state.showStates[entry.key])
				? (<LockState
				state={this.props.provider.calculateState(this.state.entries, entry.key)}/>)
				: '';
			const className = (entry.event === 'error') ? style.error : '';
			const event = (entry.event === 'error') ? 'Error on ' + entry.params.operation : entry.event;
			return (
				<tr key={entry.key} className={className}
					onClick={onClick}>
					<td className={style.time}><Timestamp
						value={entry.time * 1000}
						relative
						titleFormat="HH:mm:ss.SSS"/>
					</td>
					<td className={style.event}>{event}</td>
					<td className={style.path}>{entry.path} {body}</td>
					<td className={style.type}>
						<LockType type={entry.params.type}/>
					</td>
				</tr>
			)
		});
		if (rows.length === 0) {
			rows.push((
				<tr className={style.empty}>
					<td colSpan={4}>No log entries found</td>
				</tr>
			));
		}
		//rows.unshift((
		//	<tr className={style.new}>
		//		<td colSpan={4}>New lock</td>
		//	</tr>
		//))
		return (
			<table className={style.locklog}>
				<thead>
				<tr>
					<th className={style.time}>Time</th>
					<th className={style.event}>Event</th>
					<th className={style.path}>Path</th>
					<th className={style.type}>Type</th>
				</tr>
				</thead>
				<tbody>
				{rows}
				</tbody>
			</table>
		);
	}
}
