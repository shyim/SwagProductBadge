import template from './swag-product-badge-list.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

type CriteriaType = InstanceType<typeof Criteria>;

interface GridColumn {
    property: string;
    label: string;
    routerLink?: string;
    inlineEdit?: string;
    allowResize?: boolean;
    primary?: boolean;
}

export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory'],

    mixins: [Mixin.getByName('listing')],

    data(): {
        badges: EntityCollection<'swag_product_badge'> | null;
        isLoading: boolean;
        sortBy: string;
        sortDirection: 'ASC' | 'DESC';
        total: number;
    } {
        return {
            badges: null,
            isLoading: true,
            sortBy: 'position',
            sortDirection: 'ASC',
            total: 0,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        badgeRepository() {
            return this.repositoryFactory.create('swag_product_badge');
        },

        columns(): GridColumn[] {
            return [
                {
                    property: 'name',
                    label: 'swag-product-badge.list.columnName',
                    routerLink: 'swag.product.badge.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'color',
                    label: 'swag-product-badge.list.columnColor',
                    allowResize: true,
                },
                {
                    property: 'position',
                    label: 'swag-product-badge.list.columnPosition',
                    inlineEdit: 'number',
                    allowResize: true,
                },
            ];
        },

        badgeCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);

            if (this.term) {
                criteria.setTerm(this.term);
            }

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        },
    },

    methods: {
        async getList(): Promise<void> {
            this.isLoading = true;

            const result = await this.badgeRepository.search(this.badgeCriteria);

            this.badges = result;
            this.total = result.total ?? 0;
            this.isLoading = false;
        },

        onChangeLanguage(): void {
            void this.getList();
        },

        updateTotal({ total }: { total: number }): void {
            this.total = total;
        },
    },
});
