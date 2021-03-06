import EventSource from 'event-source';

const TYPE_SHARED = 1;
const TYPE_EXCLUSIVE = 2;

export class LogProvider {
	listening = false;
	source = null;

	getEntries () {
		return $.get(OC.generateUrl('/apps/locktools/log'));
	}

	getTimeout () {
		return $.get(OC.generateUrl('/apps/locktools/timeout'));
	}

	setTimeout (timeout) {
		return $.ajax({
			'url': OC.generateUrl('/apps/locktools/timeout'),
			'type': 'PUT',
			'data': {
				timeout: timeout
			}
		});
	}

	calculateState (entries, afterKey) {
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
		return state;
	}

	listen (lastId, cb) {
		this.listening = true;

		const source = new EventSource(OC.generateUrl(`/apps/locktools/listen?lastLock=${lastId}`));
		source.addEventListener('__internal__', (data) => {
			if (data === 'close') {
				console.log('closed from remote');
				source.close();
				setTimeout(this.listen.bind(this, lastId, cb), 100);
			}
		});

		source.addEventListener('lock', (e) => {
			const lock = JSON.parse(e.data);
			lastId = lock.key;
			if (this.listening) {
				cb(lock);
			}
		});
		this.source = source;
		return source;
	}

	stopListening () {
		this.listening = false;
	}
}
