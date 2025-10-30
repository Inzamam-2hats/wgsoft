import template from './serials-listing.html.twig';

const {Mixin, Component, Context} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;
const {mapState, mapGetters} = Component.getComponentHelper();

Shopware.Component.register('lws-esd-serials-listing', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
    ],

    props: {
        isAssociation: {
            type: Boolean,
            required: false,
            default: true,
        }

    },

    data() {
        return {
            dataEntries: [],
            newLayer: null,
            isLoading: false,
            newProperties: [],
            showDetailModal: false,
            selectedEntry: null
        };
    },

    computed: {

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },

        repository() {
            return this.repositoryFactory.create('lws_esd_serials');
        },

        columns() {
            return [
                {
                    property: 'serialNumber',
                    allowResize: true,
                    inlineEdit: 'string',
                    label: this.$tc('lws-esd-serials.serials-listing.serialNumber'),
                    sortable: true
                },
                {
                    property: 'assignDate',
                    allowResize: true,
                    label: this.$tc('lws-esd-serials.serials-listing.assignDate'),
                    sortable: true
                }
            ];
        },

        ...mapState('swProductDetail', [
            'product'
        ])
    },

    created() {
        this.createdComponent();
    },

    methods: {

        createdComponent() {
            this.loadDataEntries();
        },

        onDetailModalCancel() {
            this.showDetailModal = false;
            this.loadDataEntries();
        },

        onAfterSave() {
            this.showDetailModal = false;
            this.loadDataEntries();
        },

        onOpen() {
            this.selectedEntry = this.repository.create();
            this.selectedEntry.productId = this.product.id;
            this.showDetailModal = true;
        },

        onDetailSave() {
            this.$refs.detail.save();
        },

        emitDetailClicked(item) {
            this.selectedEntry = item;
            this.showDetailModal = true;
        },

        loadDataEntries() {

            this.isLoading = true;

            const criteria = new Criteria(1, 10);

            criteria.addSorting(
                Criteria.sort('serialNumber', 'ASC', false),
            );
            criteria.addFilter( Criteria.equals('productId', this.product.id));


            return this.repository.search(criteria, Context.api)
                .then((dataEntries) => {
                    this.dataEntries = dataEntries;
                })
                .catch(() => {
                    this.dataEntries = [];
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }

    }
});
