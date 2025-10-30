import DomAccess from 'src/helper/dom-access.helper';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import HttpClient from 'src/service/http-client.service';

export default class RepeatOrderSaveCustomerInputPlugin extends window.PluginBaseClass {
    init() {
        this.$emitter.publish('beforeInitRepeatOrderSaveCustomerInput');

        const addToCartPlugins = window.PluginManager.getPluginInstances('AddToCart');

        if (addToCartPlugins) {
            addToCartPlugins.forEach(addToCartPlugin => {
                addToCartPlugin.$emitter.subscribe('beforeFormSubmit', (data) => {
                    this.client = new HttpClient();

                    let dataTarget = data.target,
                        dataTargetId = dataTarget.getAttribute('id');

                    if (dataTargetId) {
                        let orderId = dataTargetId.replace("orderDetailForm-", ""),
                            orderItemTmmsCustomerInputForms = DomAccess.querySelectorAll(document, ".order-item-tmms-customer-input-form[data-orderId='" + orderId + "']", false);

                        if (orderItemTmmsCustomerInputForms.length > 0) {
                            orderItemTmmsCustomerInputForms.forEach((elem) => {
                                let requestUrl = elem.getAttribute('action'),
                                    formData = FormSerializeUtil.serialize(elem);

                                this.$emitter.publish('beforeRepeatOrderSaveCustomerInputSendPostRequest', formData);

                                this.client.post(requestUrl.toLowerCase(), formData, callback => {
                                    this.$emitter.publish('afterRepeatOrderSaveCustomerInputSendPostRequest');
                                });
                            });
                        }
                    }
                });
            });
        }
    }
}
