<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_combined_tabs_keep_independent_state_and_legacy_category_route_redirects(): void
    {
        $this->actingAs(User::factory()->create());
        $first = null;

        foreach (range(1, 13) as $number) {
            $category = Category::create(['name' => sprintf('Category %02d', $number)]);
            $first ??= $category;
        }

        $product = Product::create([
            'category_id' => $first->id,
            'name' => 'Tea',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 8,
            'unit' => 'pcs',
        ]);

        $this->get(route('products.index', ['search' => 'Tea', 'category_id' => $first->id]))
            ->assertOk()
            ->assertSee('Tea')
            ->assertSee('Category 01');

        $this->get(route('products.index', [
            'tab' => 'kategori',
            'search' => 'Tea',
            'category_id' => $first->id,
            'category_search' => 'Category',
            'products_page' => 3,
            'categories_page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Category 13')
            ->assertSee('name="products_page" value="3"', false)
            ->assertSee('name="category_search"', false)
            ->assertSee('value="Category"', false)
            ->assertSee('value="Tea"', false);

        $this->get(route('categories.index', ['search' => 'Category', 'page' => 2]))
            ->assertRedirect(route('products.index', [
                'category_search' => 'Category',
                'categories_page' => 2,
                'tab' => 'kategori',
            ]));
    }

    public function test_product_and_category_crud_remain_available_and_category_delete_nulls_product_relation(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('categories.store', [
            'search' => 'Tea',
            'category_id' => 42,
            'products_page' => 3,
            'category_search' => 'Drinks',
            'categories_page' => 2,
            'tab' => 'kategori',
        ]), [
            'name' => 'Drinks',
            'description' => 'Beverages',
        ])->assertRedirect(route('products.index', [
            'search' => 'Tea',
            'category_id' => 42,
            'products_page' => 3,
            'category_search' => 'Drinks',
            'categories_page' => 2,
            'tab' => 'kategori',
        ]));

        $category = Category::where('name', 'Drinks')->firstOrFail();
        $this->put(route('categories.update', $category), [
            'name' => 'Cold Drinks',
            'description' => 'Chilled beverages',
        ])->assertRedirect(route('products.index', ['tab' => 'kategori']));

        $category->refresh();
        $this->post(route('products.store'), [
            'name' => 'Tea',
            'category_id' => $category->id,
            'buy_price' => 1000,
            'sell_price' => 1500,
            'tax_rate' => 0,
            'discount_rate' => 0,
            'stock' => 8,
            'unit' => 'pcs',
        ])->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Tea')->firstOrFail();
        $this->put(route('products.update', $product), [
            'name' => 'Green Tea',
            'category_id' => $category->id,
            'buy_price' => 1100,
            'sell_price' => 1600,
            'tax_rate' => 0,
            'discount_rate' => 0,
            'stock' => 8,
            'unit' => 'pcs',
        ])->assertRedirect(route('products.index'));

        $product->refresh();
        $this->patch(route('products.adjust-stock', $product), ['adjustment' => 2])
            ->assertRedirect(route('products.index'));
        $this->assertSame(10, $product->fresh()->stock);

        $this->delete(route('categories.destroy', $category), ['tab' => 'kategori'])
            ->assertRedirect(route('products.index', ['tab' => 'kategori']));
        $this->assertNull($product->fresh()->category_id);

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
