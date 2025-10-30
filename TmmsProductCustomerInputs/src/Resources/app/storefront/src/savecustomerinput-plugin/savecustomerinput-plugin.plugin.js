import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import HttpClient from 'src/service/http-client.service';

function addSaveCustomerInputEventListener(element, eventType, handler) {
    if (element.addEventListener) {
        element.addEventListener(eventType, handler, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + eventType, handler);
    }
}

export default class SaveCustomerInputPlugin extends window.PluginBaseClass {
    init() {
        this.$emitter.publish('beforeInitSaveCustomerInput');

        addSaveCustomerInputEventListener(this.el, 'change', function () {
            this.client = new HttpClient();

            if (this.getAttribute('type') === "number" && (!this.hasAttribute('required'))) {
                this.removeAttribute('form');
            }

            let formElement = this.closest('form'),
                sendPostRequest = true,
                isRequiredField = false;

            if (this.hasAttribute('required')) {
                isRequiredField = true;

                if (this.getAttribute('type') === "checkbox") {
                    if (this.checked) {
                        this.removeAttribute('form');
                    } else {
                        if (!(this.hasAttribute('form'))) {
                            this.setAttribute('form', this.getAttribute('data-form'));
                        }
                    }
                } else {
                    if (this.value !== "") {
                        this.removeAttribute('form');
                    } else {
                        if (!(this.hasAttribute('form'))) {
                            this.setAttribute('form', this.getAttribute('data-form'));
                        }
                    }
                }
            }

            if (this.classList.contains('tmms-customer-input-is-required')) {
                if (this.getAttribute('type') === "checkbox") {
                    if (this.checked) {
                        this.classList.remove('tmms-customer-input-is-empty');
                        formElement.classList.remove('tmms-customer-input-form-input-is-empty');
                    } else {
                        this.classList.add('tmms-customer-input-is-empty');
                        formElement.classList.add('tmms-customer-input-form-input-is-empty');

                        if(this.classList.contains('tmms-customer-input-do-not-save-empty-required-field')) {
                            sendPostRequest = false;
                        }
                    }
                } else {
                    if (this.value !== "") {
                        this.classList.remove('tmms-customer-input-is-empty');
                        formElement.classList.remove('tmms-customer-input-form-input-is-empty');
                    } else {
                        this.classList.add('tmms-customer-input-is-empty');
                        formElement.classList.add('tmms-customer-input-form-input-is-empty');

                        if (this.classList.contains('tmms-customer-input-do-not-save-empty-required-field')) {
                            sendPostRequest = false;
                        }
                    }
                }
            }

            const form = this.closest('form'),
                requestUrl = this.getAttribute('data-path'),
                formData = FormSerializeUtil.serialize(form);

            if (this.getAttribute('type') === "number") {
                let inputValueAttribute = this.value,
                    minValueAttribute = this.getAttribute('min'),
                    maxValueAttribute = this.getAttribute('max'),
                    stepValueAttribute = this.getAttribute('step'),
                    inputValue = 0,
                    minValue = 0,
                    maxValue = 0,
                    stepValue = 0,
                    hasStepValue = false,
                    parseValuesAsInteger = 0,
                    numberDecimalsString = "",
                    numberDecimals = 0,
                    numberDecimalsFactor = 0;

                if (stepValueAttribute) {
                    hasStepValue = true;
                }

                if (hasStepValue === true) {
                    if (stepValueAttribute.indexOf(".") !== -1) {
                        numberDecimalsString = stepValueAttribute.substring(stepValueAttribute.indexOf(".")+1);
                        numberDecimals = numberDecimalsString.length;
                        numberDecimalsFactor = Math.pow(10, numberDecimals);
                        inputValue = parseFloat(inputValueAttribute) * numberDecimalsFactor;
                        minValue = parseFloat(minValueAttribute) * numberDecimalsFactor;
                        maxValue = parseFloat(maxValueAttribute) * numberDecimalsFactor;
                        stepValue = (stepValueAttribute) * numberDecimalsFactor;
                    } else {
                        parseValuesAsInteger = 1;

                        stepValue = parseInt(stepValueAttribute, 10);
                    }
                } else {
                    parseValuesAsInteger = 1;
                }

                if (parseValuesAsInteger === 1) {
                    inputValue = parseInt(inputValueAttribute, 10);
                    minValue = parseInt(minValueAttribute, 10);
                    maxValue = parseInt(maxValueAttribute, 10);
                }

                if (inputValue >= minValue && inputValue <= maxValue) {
                    if (hasStepValue === true) {
                        if (((((inputValue - minValue).toFixed(numberDecimals)) % stepValue !== 0) && parseValuesAsInteger == 0) || (((inputValue - minValue) % stepValue !== 0) && parseValuesAsInteger == 1)) {
                            sendPostRequest = false;
                        }
                    }
                } else {
                    sendPostRequest = false;

                    if (inputValue > maxValue) {
                        if (!(this.hasAttribute('form'))) {
                            this.setAttribute('form', this.getAttribute('data-form'));
                        }
                    } else if (inputValue < minValue) {
                        if (!(this.hasAttribute('form'))) {
                            this.setAttribute('form', this.getAttribute('data-form'));
                        }
                    }
                }

                if (isRequiredField) {
                    if (inputValueAttribute === "") {
                        sendPostRequest = false;
                    }
                } else {
                    if (inputValueAttribute === "") {
                        sendPostRequest = true;
                    }
                }
            }

            if (sendPostRequest) {
                this.$emitter.publish('beforeSaveCustomerInputSendPostRequest', formData);

                this.client.post(requestUrl.toLowerCase(), formData, callback => {
                    this.$emitter.publish('afterSaveCustomerInputSendPostRequest');
                });
            }
        });

        addSaveCustomerInputEventListener(this.el, 'keydown', (event) => {
            let element = this.el;

            if (((element.getAttribute('type') === 'text') || (element.getAttribute('type') === 'number')) && element.classList.contains('block-enter-key')) {
                if (event.which === 13 || event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            }
        });
    }
}
