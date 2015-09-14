import React, {Component} from 'react/addons';

export class LockType extends Component {
	render () {
		const names = {
			1: 'Shared',
			2: 'Exclusive'
		};
		return (
			<span>{names[this.props.type]}</span>
		);
	}
}
