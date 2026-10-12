import './extension/sw-product-detail';
import './extension/sw-product-category-form';

Shopware.Component.register('swag-product-badge-list', () => import('./page/swag-product-badge-list'));
Shopware.Component.register('swag-product-badge-detail', () => import('./page/swag-product-badge-detail'));

Shopware.Module.register('swag-product-badge', {
    type: 'plugin',
    name: 'swag-product-badge',
    title: 'swag-product-badge.general.mainMenuItemGeneral',
    description: 'swag-product-badge.general.descriptionTextModule',
    color: '#57D9A3',
    icon: 'regular-products',
    entity: 'swag_product_badge',

    routes: {
        list: {
            component: 'swag-product-badge-list',
            path: 'list',
        },
        detail: {
            component: 'swag-product-badge-detail',
            path: 'detail/:id',
            props: {
                default: (route: { params: { id?: string } }) => ({ badgeId: route.params.id }),
            },
            meta: {
                parentPath: 'swag.product.badge.list',
            },
        },
        create: {
            component: 'swag-product-badge-detail',
            path: 'create',
            meta: {
                parentPath: 'swag.product.badge.list',
            },
        },
    },

    navigation: [
        {
            id: 'swag-product-badge',
            label: 'swag-product-badge.general.mainMenuItemGeneral',
            color: '#57D9A3',
            path: 'swag.product.badge.list',
            icon: 'regular-products',
            parent: 'sw-catalogue',
            position: 100,
        },
    ],
});
