<?php

namespace App\Support;

/**
 * Calculates the sale-time values shared by web and mobile POS flows.
 *
 * `subtotal` is the gross item amount before discounts. Product discount is
 * applied before tax; an optional transaction-level discount is applied after
 * tax, matching the existing POS workflow.
 */
final class SaleCalculator
{
    /**
     * @param array<int, array{product_id?: int, quantity: int, price: int|float|string, discount_rate?: int|float|string|null, tax_rate?: int|float|string|null}> $items
     * @return array{subtotal: float, product_discount: float, extra_discount: float, discount: float, tax: float, total: float, items: array<int, array{product_id?: int, quantity: int, price_at_sale: float, subtotal: float, discount: float, tax: float}>}
     */
    public static function calculate(array $items, int|float|string $extraDiscount = 0): array
    {
        $calculatedItems = [];
        $subtotal = 0.0;
        $productDiscount = 0.0;
        $tax = 0.0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $price = round((float) $item['price'], 2);
            $lineSubtotal = round($price * $quantity, 2);
            $lineDiscount = round($lineSubtotal * ((float) ($item['discount_rate'] ?? 0) / 100), 2);
            $lineTax = round(($lineSubtotal - $lineDiscount) * ((float) ($item['tax_rate'] ?? 0) / 100), 2);

            $calculatedItems[] = array_filter([
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $quantity,
                'price_at_sale' => $price,
                'subtotal' => $lineSubtotal,
                'discount' => $lineDiscount,
                'tax' => $lineTax,
            ], static fn ($value) => $value !== null);

            $subtotal = round($subtotal + $lineSubtotal, 2);
            $productDiscount = round($productDiscount + $lineDiscount, 2);
            $tax = round($tax + $lineTax, 2);
        }

        // A discount cannot exceed the remaining value after product discounts.
        // This keeps the recorded total equal to subtotal - discount + tax.
        $maximumExtraDiscount = max(0.0, round($subtotal - $productDiscount, 2));
        $extraDiscount = min(max(0.0, round((float) $extraDiscount, 2)), $maximumExtraDiscount);
        $discount = round($productDiscount + $extraDiscount, 2);

        return [
            'subtotal' => $subtotal,
            'product_discount' => $productDiscount,
            'extra_discount' => $extraDiscount,
            'discount' => $discount,
            'tax' => $tax,
            'total' => round($subtotal - $discount + $tax, 2),
            'items' => $calculatedItems,
        ];
    }
}
