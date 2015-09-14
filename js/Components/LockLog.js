import React, {Component} from 'react/addons';
import Timestamp from 'react-time';

import {LockType} from './LockType';

import style from './LockLog.less'

export class LockLog extends Component {
	state = {
		entries: []
	};

	componentDidMount = async() => {
		const provider = this.props.provider;
		const entries = await provider.getEntries();
		console.log(entries);
		this.setState({entries});
	};

	render () {
		const rows = this.state.entries.map(entry => (
			<tr key={entry.key}>
				<td className={style.time}><Timestamp value={entry.time * 1000} relative titleFormat="HH:mm:ss.SSS"/></td>
				<td className={style.event}>{entry.event}</td>
				<td className={style.path}>{entry.path}</td>
				<td className={style.type}>
					<LockType type={entry.params.type}/>
				</td>
			</tr>
		));
		if (rows.length === 0) {
			rows.push((
				<tr className={style.empty}>
					<td colSpan={4}>No log entries found</td>
				</tr>
			));
		}
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
