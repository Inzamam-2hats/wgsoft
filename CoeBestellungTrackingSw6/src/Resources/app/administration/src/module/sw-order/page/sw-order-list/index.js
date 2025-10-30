const { Component } = Shopware;
import template from './sw-order-list.html.twig';


import deDE from '../../../../snippet/de-DE.json';
import enGB from '../../../../snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);

Shopware.Component.override('sw-order-list', {
    template,

    methods : {

        getOrderColumns() {
            let columns = this.$super('getOrderColumns');
            columns.push({
                property: 'deliveries',
                label: 'sw-order.list.trackingnumber',
                align: 'left'
            });

            return columns;
        },

        getTrackingNumbers(order){
            var trackingNumbers = "";
            order.deliveries.forEach(function(item){
                trackingNumbers += item.trackingCodes.join(", ");
            });

            return trackingNumbers;
        }
    }
});