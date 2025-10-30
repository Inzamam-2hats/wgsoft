/**
 * Allow max purchase to be edited in bulk edit
 */
Shopware.Component.override('sw-bulk-edit-product', {

    computed: {

        restrictedFields() {

            const restrictedFields = this.$super('restrictedFields');
            const index = restrictedFields.indexOf('maxPurchase');
            if (index > -1) { //max purchase is not permitted for bulk edit
                return restrictedFields.toSpliced(index, 1);
            }
            return restrictedFields;
        }
    }
})

