type CriteriaType = InstanceType<typeof Shopware.Data.Criteria>;

Shopware.Component.override('sw-product-detail', {
    computed: {
        productCriteria(this: { $super: (name: 'productCriteria') => CriteriaType }): CriteriaType {
            const criteria = this.$super('productCriteria');

            criteria.getAssociation('swagProductBadges').addSorting(Shopware.Data.Criteria.sort('position', 'ASC'));

            return criteria;
        },
    },
});
