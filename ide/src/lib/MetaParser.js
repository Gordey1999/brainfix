

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
			// #@TITLE=YEA
			const matches = line.match(/^\s*#\s*@([a-zA-Z][^\s=:]*)\s*[=:]?\s*(.*)$/)
			if (matches) {
				result[matches[1].toLowerCase()] = matches[2];
			}
		}

		return result;
	}

	static parseBool(value, defaultValue) {
		const falseValues = ['false', 'no', 'off', 'n', '0'];
		const trueValues = ['true', 'yes', 'on', 'y', '1'];

		if (defaultValue) {
			return !falseValues.includes(value.toLowerCase());
		} else {
			return !trueValues.includes(value.toLowerCase());
		}
	}

	static parseInt(value, defaultValue) {
		let matches = value.match(/^(\d+)([MK])?$/i);
		if (matches) {
			if (!matches[2]) {
				return parseInt(matches[1]);
			} else if (matches[2].toUpperCase() === 'M') {
				return parseInt(matches[1]) * 1000000;
			} else { // K
				return parseInt(matches[1]) * 1000;
			}
		}

		return defaultValue;
	}
}