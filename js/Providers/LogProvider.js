export class LogProvider {
	async getEntries () {
		return $.get(OC.generateUrl('/apps/locktools/log'));
	}
}
