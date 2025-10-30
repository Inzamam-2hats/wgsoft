import template from './serials-detail.html.twig';

const {Mixin, Component, Context} = Shopware;
const {mapState, mapGetters, mapPropertyErrors} = Component.getComponentHelper();

Shopware.Component.register('lws-esd-serials-detail', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification')
    ],

    props: {
        entity: {
            type: Object,
            required: true,
            default: false,
        }
    },

    data() {
        return {
            isLoading: false
        };
    },

    computed: {

        ...mapPropertyErrors('entity',
            ['name']),

        repository() {
            return this.repositoryFactory.create('lws_esd_serials');
        }

    },

    watch: {},

    created() {
        this.createdComponent();
    },

    methods: {

        createdComponent() {

        },

        save() {
            this.isLoading = true;

            let entityByLines = this.splitByLines(this.entity);

            let index = 0;
            entityByLines.forEach((entityLine) => {
                this.repository.save(entityLine).then(() => {
                    this.isLoading = false;

                    if (index == entityByLines.length - 1) {
                        this.createNotificationSuccess({title: '', message: this.$tc('lws-esd-serials.serials-detail.successMessage')})
                        this.$emit('afterSave', entityLine);
                    }

                    index++;
                }).catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        message: this.$tc(
                            'serials-detail.notificationSaveErrorMessageRequiredFieldsInvalid',
                        ),
                    });

                    throw exception;
                });


            });

        },

        splitByLines(entity) {
            let entityByLines = [];
            if (entity && entity.serialNumber) {
                let lines = entity.serialNumber.split(/\r?\n|\r|\n/g);

                lines.forEach((element) => {
                    let lwsEsdSerial = this.repository.create();
                    lwsEsdSerial.productId = entity.productId;
                    lwsEsdSerial.serialNumber = element;
                    entityByLines.push(lwsEsdSerial);
                });
            }

            console.log(entityByLines);
            return entityByLines;
        }

    },
});
