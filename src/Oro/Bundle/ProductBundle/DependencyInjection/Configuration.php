<?php

namespace Oro\Bundle\ProductBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = OroProductExtension::ALIAS;
    const RELATED_PRODUCTS_ENABLED = 'related_products_enabled';
    const RELATED_PRODUCTS_BIDIRECTIONAL = 'related_products_bidirectional';
    const MAX_NUMBER_OF_RELATED_PRODUCTS = 'max_number_of_related_products';
    const MAX_NUMBER_OF_RELATED_PRODUCTS_COUNT = 25;
    const UPSELL_PRODUCTS_ENABLED = 'upsell_products_enabled';
    const MAX_NUMBER_OF_UPSELL_PRODUCTS = 'max_number_of_upsell_products';
    const MAX_NUMBER_OF_UPSELL_PRODUCTS_COUNT = 25;
    const RELATED_PRODUCTS_MAX_ITEMS = 'related_products_max_items';
    const RELATED_PRODUCTS_MAX_ITEMS_COUNT = 4;
    const RELATED_PRODUCTS_MIN_ITEMS = 'related_products_min_items';
    const RELATED_PRODUCTS_MIN_ITEMS_COUNT = 3;
    const RELATED_PRODUCTS_SHOW_ADD_BUTTON = 'related_products_show_add_button';
    const RELATED_PRODUCTS_USE_SLIDER_ON_MOBILE = 'related_products_use_slider_on_mobile';
    const SINGLE_UNIT_MODE = 'single_unit_mode';
    const SINGLE_UNIT_MODE_SHOW_CODE = 'single_unit_mode_show_code';
    const DEFAULT_UNIT = 'default_unit';
    const PRODUCT_IMAGE_WATERMARK_FILE = 'product_image_watermark_file';
    const PRODUCT_IMAGE_WATERMARK_SIZE = 'product_image_watermark_size';
    const PRODUCT_IMAGE_WATERMARK_POSITION = 'product_image_watermark_position';
    const FEATURED_PRODUCTS_SEGMENT_ID = 'featured_products_segment_id';
    const ENABLE_QUICK_ORDER_FORM = 'enable_quick_order_form';
    const DIRECT_URL_PREFIX = 'product_direct_url_prefix';
    const BRAND_DIRECT_URL_PREFIX = 'brand_direct_url_prefix';
    const PRODUCT_COLLECTIONS_INDEXATION_CRON_SCHEDULE = 'product_collections_indexation_cron_schedule';
    const DEFAULT_CRON_SCHEDULE = '0 * * * *';
    const PRODUCT_PROMOTION_SHOW_ON_VIEW = 'product_promotion_show_on_product_view';
    const PRODUCT_COLLECTION_MASS_ACTION_LIMITATION = 'product_collections_mass_action_limitation';
    const NEW_ARRIVALS_PRODUCT_SEGMENT_ID = 'new_arrivals_products_segment_id';
    const NEW_ARRIVALS_MAX_ITEMS = 'new_arrivals_max_items';
    const NEW_ARRIVALS_MIN_ITEMS = 'new_arrivals_min_items';
    const NEW_ARRIVALS_USE_SLIDER_ON_MOBILE = 'new_arrivals_use_slider_on_mobile';
    const IMAGE_PREVIEW_ON_PRODUCT_LISTING_ENABLED = 'image_preview_on_product_listing_enabled';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root(static::ROOT_NODE);

        SettingsBuilder::append(
            $rootNode,
            [
                static::RELATED_PRODUCTS_ENABLED => ['value' => true],
                static::RELATED_PRODUCTS_BIDIRECTIONAL => ['value' => false],
                static::MAX_NUMBER_OF_RELATED_PRODUCTS => [
                    'value' => static::MAX_NUMBER_OF_RELATED_PRODUCTS_COUNT,
                ],
                static::UPSELL_PRODUCTS_ENABLED => ['value' => true],
                static::MAX_NUMBER_OF_UPSELL_PRODUCTS => [
                    'value' => static::MAX_NUMBER_OF_UPSELL_PRODUCTS_COUNT
                ],
                self::RELATED_PRODUCTS_MAX_ITEMS => [
                    'value' => self::RELATED_PRODUCTS_MAX_ITEMS_COUNT,
                ],
                self::RELATED_PRODUCTS_MIN_ITEMS => [
                    'value' => self::RELATED_PRODUCTS_MIN_ITEMS_COUNT,
                ],
                self::RELATED_PRODUCTS_SHOW_ADD_BUTTON => ['value' => true],
                self::RELATED_PRODUCTS_USE_SLIDER_ON_MOBILE => ['value' => false],
                'unit_rounding_type' => ['value' => RoundingServiceInterface::ROUND_HALF_UP],
                static::SINGLE_UNIT_MODE => ['value' => false, 'type' => 'boolean'],
                static::SINGLE_UNIT_MODE_SHOW_CODE => ['value' => false, 'type' => 'boolean'],
                static::DEFAULT_UNIT => ['value' => 'each'],
                'default_unit_precision' => ['value' => 0],
                'general_frontend_product_visibility' => [
                    'value' => [
                        Product::INVENTORY_STATUS_IN_STOCK,
                        Product::INVENTORY_STATUS_OUT_OF_STOCK
                    ]
                ],
                static::PRODUCT_IMAGE_WATERMARK_FILE => ['value' => null],
                static::PRODUCT_IMAGE_WATERMARK_SIZE => ['value' => 100],
                static::PRODUCT_IMAGE_WATERMARK_POSITION => ['value' => 'center'],
                static::FEATURED_PRODUCTS_SEGMENT_ID => [
                    'value' => '@oro_product.provider.default_value.featured_products'
                ],
                static::ENABLE_QUICK_ORDER_FORM => ['type' => 'boolean', 'value' => true],
                static::DIRECT_URL_PREFIX => ['value' => ''],
                static::PRODUCT_COLLECTIONS_INDEXATION_CRON_SCHEDULE => ['value' => static::DEFAULT_CRON_SCHEDULE],
                static::PRODUCT_PROMOTION_SHOW_ON_VIEW => ['value' => false, 'type' => 'boolean'],
                static::BRAND_DIRECT_URL_PREFIX => ['value' => ''],
                static::PRODUCT_COLLECTION_MASS_ACTION_LIMITATION => ['value' => 500],
                static::NEW_ARRIVALS_PRODUCT_SEGMENT_ID => [
                    'value' => '@oro_product.provider.default_value.new_arrivals'
                ],
                static::NEW_ARRIVALS_MAX_ITEMS => ['type' => 'integer', 'value' => 4],
                static::NEW_ARRIVALS_MIN_ITEMS => ['type' => 'integer', 'value' => 3],
                static::NEW_ARRIVALS_USE_SLIDER_ON_MOBILE => ['type' => 'boolean', 'value' => false],
                self::IMAGE_PREVIEW_ON_PRODUCT_LISTING_ENABLED => ['type' => 'boolean', 'value' => true],
            ]
        );

        return $treeBuilder;
    }


    /**
     * @param string $key
     * @return string
     */
    public static function getConfigKeyByName($key)
    {
        return implode(ConfigManager::SECTION_MODEL_SEPARATOR, [static::ROOT_NODE, $key]);
    }
}
