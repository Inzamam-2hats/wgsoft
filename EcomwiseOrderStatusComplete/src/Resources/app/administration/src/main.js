const { Component, Mixin } = Shopware;
import template from './extension/sw-order-list/sw-order-list.html.twig';
import deDE from './extension/snippet/de-DE.json';
import enGB from './extension/snippet/en-GB.json';
import nlNL from './extension/snippet/nl-NL.json';

Component.override('sw-order-list', {
    template,

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
        'nl-NL': nlNL
    },

    data() {
        return {
            syncService: null,
            httpClient: null,
            showBulkDoneModal: false,
            isBulkLoading: false
        };
    },
    mixins: [
        Mixin.getByName('notification')
    ],

    methods: {
        // on click call this function
        doneOrders() {
            this.isBulkLoading = true;
            const selectedIds = Object.values(this.selection).map(selectedProxy => selectedProxy.id);

            this.syncService = Shopware.Service('syncService');
            this.httpClient = this.syncService.httpClient;
            const headers = this.syncService.getBasicHeaders();  

            return this.httpClient
            .post('/ecomwise/mark-done-api-action',selectedIds,{headers})
            .then((response) => {
                this.createNotificationInfo({
                    title: this.$tc(response.data.type),
                    message: this.$tc(response.data.message)
                });
                this.isBulkLoading = false;
                this.showBulkDoneModal = false;
                this.getList();
                return;
            })
        },
    },   
});

