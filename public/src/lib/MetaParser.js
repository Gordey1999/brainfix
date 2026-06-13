

export class MetaParser {
	static getHeaderValue(code, header, defaultValue) {
		const headers = this.parseHeaders(code);

		return headers[header] ?? defaultValue;
	}

	static parseHeaders(code) {
		const lines = code.split("\n");
		const result = {};

		for (let line of lines) {
			if (line.trim() && line.trim()[0] !== "#") {
				break;
			}

			// # @title: 123
			// # @set=12
			// # @key value
			const matches = line.match(/^\s*#\s*@([a-z][^\s=:]*)\s*[=:]?\s*(.*)$/)
			if (matches) {
				result[matches[1]] = matches[2];
			}
		}

		return result;
	}
}