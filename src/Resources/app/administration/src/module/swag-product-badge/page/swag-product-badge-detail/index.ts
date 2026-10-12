import type { PropType } from 'vue';
import template from './swag-product-badge-detail.html.twig';

const { Mixin } = Shopware;

export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],

    props: {
        badgeId: {
            type: String as unknown as PropType<EntityKey<'swag_product_badge'> | null>,
            required: false,
            default: null,
        },
    },

    data(): {
        badge: Entity<'swag_product_badge'> | null;
        isLoading: boolean;
        isSaveSuccessful: boolean;
    } {
        return {
            badge: null,
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.badge?.translated?.name),
        };
    },

    computed: {
        badgeRepository() {
            return this.repositoryFactory.create('swag_product_badge');
        },

        isCreateMode(): boolean {
            return this.badgeId === null;
        },
    },

    watch: {
        badgeId(): void {
            void this.loadBadge();
        },
    },

    created() {
        void this.loadBadge();
    },

    methods: {
        async loadBadge(): Promise<void> {
            if (this.isCreateMode || this.badgeId === null) {
                Shopware.Store.get('context').resetLanguageToDefault();
                this.badge = this.badgeRepository.create();

                return;
            }

            this.isLoading = true;
            this.badge = await this.badgeRepository.get(this.badgeId);
            this.isLoading = false;
        },

        onChangeLanguage(): void {
            void this.loadBadge();
        },

        abortOnLanguageChange(): boolean {
            if (!this.badge) {
                return false;
            }

            return this.badgeRepository.hasChanges(this.badge);
        },

        saveOnLanguageChange(): Promise<void> {
            return this.onSave();
        },

        async onSave(): Promise<void> {
            if (!this.badge) {
                return;
            }

            this.isLoading = true;
            this.isSaveSuccessful = false;

            try {
                await this.badgeRepository.save(this.badge);
                this.isSaveSuccessful = true;

                if (this.isCreateMode) {
                    void this.$router.push({ name: 'swag.product.badge.detail', params: { id: this.badge.id } });

                    return;
                }

                await this.loadBadge();
            } catch {
                this.createNotificationError({
                    message: this.$t('swag-product-badge.detail.messageSaveError'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        onCancel(): void {
            void this.$router.push({ name: 'swag.product.badge.list' });
        },
    },
});
