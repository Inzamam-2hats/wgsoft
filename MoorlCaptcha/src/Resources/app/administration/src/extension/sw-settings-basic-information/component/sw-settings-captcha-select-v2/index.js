import template from './sw-settings-captcha-select-v2.html.twig';

const {Component} = Shopware;

Component.override('sw-settings-captcha-select-v2', {
    template,

    data() {
        return {
            moorlCaptchaBwRegexModalIndex: null,
        };
    },

    computed: {
        moorlCaptchaBwGridColumns() {
            return [
                {
                    label: this.$tc('sw-settings-basic-information.captcha.moorlCaptchaBw.properties.active'),
                    property: 'active',
                    dataIndex: 'active',
                    align: 'center',
                    inlineEdit: 'boolean',
                },
                {
                    label: this.$tc('sw-settings-basic-information.captcha.moorlCaptchaBw.properties.name'),
                    property: 'name',
                    dataIndex: 'name',
                    primary: true
                },
                {
                    label: this.$tc('sw-settings-basic-information.captcha.moorlCaptchaBw.properties.description'),
                    property: 'description',
                    dataIndex: 'description',
                    primary: true
                }
            ];
        },
    },

    methods: {
        moorlCaptchaBwAddItem() {
            this.currentValue.moorlCaptchaBw.config.rules.push({name: '*', active: false, description: '', regex: ''});

            this.moorlCaptchaBwRegexModalIndex = this.currentValue.moorlCaptchaBw.config.rules.length - 1;
        },

        moorlCaptchaBwDeleteItem(itemIndex) {
            this.currentValue.moorlCaptchaBw.config.rules.splice(itemIndex, 1);

            this.moorlCaptchaBwRegexModalIndex = null;
        }
    },
});
