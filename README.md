# SwagProductBadge

Custom product badges for Shopware 6 — create badges like *Vegan*, *Bestseller* or *Gift idea* in the Administration, assign several of them to any product, and let customers filter the listing by badge.

Built live in the talk [**“Back to Basics — Creating Shopware Plugins from scratch”**](docs/Back-To-Basics.pdf) (Doraemon & Shizuka’s store). Part 1 of the talk used a single custom-field select; this plugin is Part 2: the badge outgrew the custom field and became its own entity.

## Why an own entity (and not a custom field)?

| Requirement | Custom field (`custom-fields.xml`) | This plugin (own entity) |
|---|---|---|
| Free-text badge per product | ✅ | ✅ |
| Merchant creates own badges (name, color, position) | ❌ fixed options in XML | ✅ admin module |
| Several badges per product | ❌ select stores one value | ✅ many-to-many |
| Filterable / aggregatable in the listing | ❌ JSON column, no index | ✅ `EntityAggregation` + filter |

If you only need one fixed label per product, a custom field is cheaper. As soon as badges are reusable, sortable and filterable, they deserve a table.

## Features

- **Badge entity** `swag_product_badge`: translatable `name`, `color` (hex), `position` for sorting
- **Many-to-many to products** via `swag_product_badge_product` (`ProductExtension::EXTENSION_NAME = swagProductBadges`)
- **Administration module** under *Catalogue → Product badges*
  - List with search, sorting, inline edit (name, position) and color preview
  - Detail page with name, color picker, position and live preview, incl. language switch
  - Assignment on the product detail page via multi-select (sorted by `position`)
- **Storefront**
  - Renders all assigned badges on product cards (`component_product_badges_swag_product_badge`, after the stock *New* badge), colored via inline `background-color`
  - Listing filter “Badge” (multi-select) powered by an `EntityAggregation`, sorted by `position`
- **Tests**: integration test for listing with/without badge filter
- **Clean uninstall**: tables are dropped unless `keepUserData` is set


## Installation

The plugin lives in `custom/static-plugins/` (project extension, committed to the repo, pulled in via a Composer path repository) — not in `custom/plugins/` and not from the Store.

```bash
# from the Shopware root
bin/console plugin:refresh
bin/console plugin:install --activate SwagProductBadge
bin/console cache:clear
```

Then rebuild the Administration assets if needed (build output goes to `src/Resources/public/`, which is gitignored — run a build after checkout):

```bash
# from the plugin directory: build only this extension
shopware-cli extension build

# or from the project root: rebuild the whole Administration
shopware-cli project admin-build
```

## Usage (merchant)

1. Go to **Catalogue → Product badges → Add badge**.
2. Set **name** (translatable), **color** and **position** (lower = shown/first first). The preview shows the result.
3. Open a **product → Badges**, select one or more badges, save.
4. In the Storefront the badges appear on the product card; in the category listing customers can narrow results via the **Badge** filter (`?swag-product-badge=<uuid>|<uuid>`).

## How it works (developer)

- **DAL**: `Swag\ProductBadge\Entity\ProductBadgeEntity` (`#[Entity('swag_product_badge')]`, attributes, no handwritten definition/collection needed), `color` max 7 chars, default `#0b539b`, default `position` 1.
- **Migration**: `Migration1791373708CreateProductBadge` creates `swag_product_badge`, `swag_product_badge_translation` and the mapping table `swag_product_badge_product` (version-aware FK to `product`).
- **Mapping**: `Swag\ProductBadge\Extension\ProductExtension` (`BulkEntityExtension`) adds a `ManyToManyAssociationField` to `product` as `swagProductBadges` (`ApiAware`, `CascadeDelete`).
- **Listing**: `Swag\ProductBadge\Subscriber\ProductBadgeSubscriber`
  - `ProductListingCriteriaEvent`: adds the `swagProductBadges` association,
  - `ProductListingCollectFilterEvent`: registers filter/aggregation `swag-product-badge` (`EqualsAnyFilter` on `product.swagProductBadges.id`), reading UUIDs from `?swag-product-badge=`.
- **Twig** (both `sw_extends`, so the plugin chain stays intact):
  - `views/storefront/component/product/card/badges.html.twig` — loops `product.extensions.swagProductBadges` in block `component_product_badges_swag_product_badge`,
  - `views/storefront/component/listing/filter-panel.html.twig` — renders `filter-multi-select.html.twig` from the `swag-product-badge` aggregation in block `component_filter_panel_item_swag_product_badge`.
- **Admin**: Vue module `swag-product-badge` (`entity: swag_product_badge`, routes `list`/`detail`/`create`), list + detail pages, `sw-product-detail` criteria extension (sorted badges) and a `sw-product-category-form` multi-select override.
- **Snippets**: `storefront.{en-GB,de-DE}.json` (`swagProductBadge.filterDisplayName/AriaLabel`), admin module snippets `de-DE`/`en-GB` under `Resources/app/administration`.
- **Lifecycle**: `SwagProductBadge::uninstall()` drops all three tables unless `--keep-user-data` was used. `install`/`update`/`activate` use defaults (services, templates and routes are registered via config, not the plugin class).

## Project structure

```
src/
├── SwagProductBadge.php            # plugin class (uninstall cleanup only)
├── Entity/ProductBadgeEntity.php   # DAL entity (attribute-based)
├── Extension/ProductExtension.php  # M2M product → badges
├── Migration/                      # CREATE TABLE × 3
├── Subscriber/ProductBadgeSubscriber.php  # listing association + filter
└── Resources/
    ├── config/services.yaml
    ├── snippet/storefront.{en-GB,de-DE}.json
    ├── views/storefront/...        # card badges + filter panel
    └── app/administration/src/module/swag-product-badge/
        ├── index.ts                # module, routes, navigation
        ├── extension/              # product assignment
        ├── page/swag-product-badge-{list,detail}/
        └── snippet/{en-GB,de-DE}.json
tests/Integration/Subscriber/ProductListingRouteTest.php
```

## Testing

The plugin ships its own `phpunit.xml` with `tests/TestBootstrap.php`. The bootstrap boots the Shopware test kernel, installs and activates `SwagProductBadge` automatically (`setForceInstallPlugins(true)`), and derives the test database from your `DATABASE_URL` by appending `_test` (e.g. `shopware` → `shopware_test`, created and migrated on first run). So all you need is a running MySQL matching the `.env` of your Shopware checkout.

```bash
# from the Shopware root (not the plugin dir)
vendor/bin/phpunit -c custom/static-plugins/SwagProductBadge/phpunit.xml

# single test file
vendor/bin/phpunit -c custom/static-plugins/SwagProductBadge/phpunit.xml \
    tests/Integration/Subscriber/ProductListingRouteTest.php

# single test method
vendor/bin/phpunit -c custom/static-plugins/SwagProductBadge/phpunit.xml \
    --filter testListingFiltersReally
```

`ProductListingRouteTest` creates three products (both badges / one badge / none) and asserts the listing returns all three unfiltered and two when filtered by `?swag-product-badge=<sale-id>`.

## Uninstall

```bash
bin/console plugin:uninstall SwagProductBadge
# keep data:
bin/console plugin:uninstall --keep-user-data SwagProductBadge
```

## License

MIT
