import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class MoorlCaptchaToPlugin extends Plugin {
    static options = {
        url: null
    };

    init() {
        this._client = new HttpClient();
        this.sendAjaxFormSubmit();
    }

    sendAjaxFormSubmit() {
        const { _client, options } = this;
        _client.get(options.url, this._handleResponse.bind(this));
    }

    _handleResponse(res) {
        const response = JSON.parse(res);

        this.el.value = response.value;
    }
}
