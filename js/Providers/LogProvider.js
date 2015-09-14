const TYPE_SHARED = 1;
const TYPE_EXCLUSIVE = 2;

export class LogProvider {
	async getEntries () {
		return $.get(OC.generateUrl('/apps/locktools/log'));
	}

	calculateState (entries, afterKey) {
		console.log(afterKey);
		const reversedEntries = entries.slice().reverse();
		const filteredEntries = reversedEntries.filter(entry=>entry.key <= afterKey);
		return filteredEntries.reduce(this.addLockEvent, {});
	}

	addLockEvent (state, entry) {
		function initForPath (path) {
			if (!state[path]) {
				state[path] = {
					trace: [],
					state: 0
				};
			}
		}

		switch (entry.event) {
			case 'acquire':
				initForPath(entry.path);
				state[entry.path].trace.push(entry.key);
				if (entry.params.type === TYPE_SHARED) {
					state[entry.path].state++;
				} else {
					state[entry.path].state = -1;
				}
				return state;
			case 'release':
				if (state[entry.path]) {
					if (entry.params.type === TYPE_SHARED) {
						state[entry.path].state--;
					} else {
						state[entry.path].state = 0;
					}
					if (state[entry.path].state === 0) {
						delete state[entry.path];
					}
				}
				return state;
			case 'change':
				if (state[entry.path]) {
					state[entry.path].trace.push(entry.key);
					if (entry.params.type === TYPE_SHARED) {
						state[entry.path].state = 1;
					} else {
						state[entry.path].state = -1;
					}
				}
				return state;
		}
	}
}
