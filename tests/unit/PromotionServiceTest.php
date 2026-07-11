<?php

use App\Services\PromotionService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PromotionServiceTest extends CIUnitTestCase
{
    public function testDiscountedTriggerItemDoesNotAddGift(): void
    {
        $rules = [
            [
                'id' => 10,
                'promotion_id' => 7,
                'promotion_name' => 'Promo A',
                'trigger_product_id' => 101,
                'trigger_qty' => 2,
                'gift_product_id' => 202,
                'gift_qty' => 1,
                'priority' => 100,
                'same_product_allowed' => 0,
                'max_applications_per_invoice' => null,
            ],
        ];

        $products = [
            [
                'id' => 202,
                'name' => 'Gift Product',
                'code' => 'GIFT-202',
                'cost_price' => 5,
            ],
        ];

        $service = new class($rules, $products) extends PromotionService {
            public function __construct(array $rules, array $products)
            {
                $this->ruleModel = new class($rules) {
                    private $rules;

                    public function __construct(array $rules)
                    {
                        $this->rules = $rules;
                    }

                    public function getActiveRulesForProducts(int $storeId, array $productIds, $saleDate = null): array
                    {
                        return $this->rules;
                    }
                };

                $this->productModel = new class($products) {
                    private $products;
                    private $ids = [];

                    public function __construct(array $products)
                    {
                        $this->products = $products;
                    }

                    public function forStore(int $storeId)
                    {
                        return $this;
                    }

                    public function whereIn(string $field, array $ids)
                    {
                        $this->ids = $ids;
                        return $this;
                    }

                    public function findAll(): array
                    {
                        return array_values(array_filter($this->products, function (array $product): bool {
                            return in_array((int) ($product['id'] ?? 0), $this->ids, true);
                        }));
                    }
                };
            }
        };

        $result = $service->applyToSale([
            [
                'product_id' => 101,
                'qty' => 2,
                'unit_price' => 100,
                'discount' => 10,
                'discount_type' => 'fixed',
                'name' => 'Trigger Product',
                'code' => 'TRIGGER-101',
            ],
        ], 1);

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $result['items']);
        $this->assertSame([], $result['generated_gift_items']);
        $this->assertSame([], $result['applied_promotions']);
    }
}
