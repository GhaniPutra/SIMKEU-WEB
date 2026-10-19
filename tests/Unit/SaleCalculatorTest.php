<?php

namespace Tests\Unit;

use App\Support\SaleCalculator;
use PHPUnit\Framework\TestCase;

class SaleCalculatorTest extends TestCase
{
    public function test_sale_without_product_discounts_or_tax_uses_gross_subtotal_as_total(): void
    {
        $result = SaleCalculator::calculate([
            ['product_id' => 1, 'quantity' => 2, 'price' => 100],
            ['product_id' => 2, 'quantity' => 1, 'price' => 50],
        ]);

        $this->assertSame(250.0, $result['subtotal']);
        $this->assertSame(0.0, $result['discount']);
        $this->assertSame(0.0, $result['tax']);
        $this->assertSame(250.0, $result['total']);
        $this->assertSame(200.0, $result['items'][0]['subtotal']);
        $this->assertSame(0.0, $result['items'][0]['discount']);
        $this->assertSame(0.0, $result['items'][0]['tax']);
    }

    public function test_product_discount_is_recorded_in_total_discount(): void
    {
        $result = SaleCalculator::calculate([
            ['quantity' => 2, 'price' => 100, 'discount_rate' => 10],
        ]);

        $this->assertSame(200.0, $result['subtotal']);
        $this->assertSame(20.0, $result['discount']);
        $this->assertSame(180.0, $result['total']);
        $this->assertSame(20.0, $result['items'][0]['discount']);
    }

    public function test_tax_is_calculated_after_product_discount(): void
    {
        $result = SaleCalculator::calculate([
            ['quantity' => 2, 'price' => 100, 'tax_rate' => 10],
        ]);

        $this->assertSame(200.0, $result['subtotal']);
        $this->assertSame(0.0, $result['discount']);
        $this->assertSame(20.0, $result['tax']);
        $this->assertSame(220.0, $result['total']);
    }

    public function test_product_and_extra_discounts_and_tax_follow_the_shared_formula(): void
    {
        $result = SaleCalculator::calculate([
            ['quantity' => 2, 'price' => 100, 'discount_rate' => 10, 'tax_rate' => 10],
        ], 5);

        $this->assertSame(200.0, $result['subtotal']);
        $this->assertSame(20.0, $result['product_discount']);
        $this->assertSame(5.0, $result['extra_discount']);
        $this->assertSame(25.0, $result['discount']);
        $this->assertSame(18.0, $result['tax']);
        $this->assertSame(193.0, $result['total']);
        $this->assertSame(
            $result['subtotal'] - $result['discount'] + $result['tax'],
            $result['total']
        );
    }

    public function test_extra_discount_is_capped_at_the_remaining_subtotal(): void
    {
        $result = SaleCalculator::calculate([
            ['quantity' => 1, 'price' => 100, 'discount_rate' => 10, 'tax_rate' => 10],
        ], 500);

        $this->assertSame(100.0, $result['discount']);
        $this->assertSame(9.0, $result['tax']);
        $this->assertSame(9.0, $result['total']);
    }
}
