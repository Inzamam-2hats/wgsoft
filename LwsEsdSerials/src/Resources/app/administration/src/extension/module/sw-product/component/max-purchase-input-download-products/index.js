import template from './max-purchase-input-download-products.html.twig';

const {Component} = Shopware;
const { Mixin } = Shopware;
const { mapState, mapPropertyErrors, mapGetters } = Shopware.Component.getComponentHelper();

Component.register('max-purchase-input-download-products', {
    template,

    compatConfig: Shopware.compatConfig,

    mixins: [
        Mixin.getByName('placeholder'),
    ],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },
    },

    computed: {
        ...mapState('swProductDetail', [
            'product',
            'parentProduct',
            'loading',
        ]),

        ...mapGetters('swProductDetail', [
            'showModeSetting',
        ]),

        ...mapPropertyErrors('product', [
            'maxPurchase',
        ]),
    }

});
