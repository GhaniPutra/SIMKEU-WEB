<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_lists_and_id_routes_are_limited_to_the_authenticated_store(): void
    {
        $storeA = Store::create(['name' => 'Store A']);
        $storeB = Store::create(['name' => 'Store B']);
        $adminA = User::factory()->create(['role' => 'admin', 'store_id' => $storeA->id]);
        $employeeB = User::factory()->create(['role' => 'pegawai', 'store_id' => $storeB->id]);

        $this->actingAs($adminA);
        $categoryA = Category::create(['name' => 'Kategori A']);
        $productA = Product::create(['name' => 'Produk A', 'category_id' => $categoryA->id, 'stock' => 8, 'unit' => 'pcs']);

        auth()->logout();
        $this->actingAs(User::factory()->create(['role' => 'admin', 'store_id' => $storeB->id]));
        $categoryB = Category::create(['name' => 'Kategori B']);
        $productB = Product::create(['name' => 'Produk B', 'category_id' => $categoryB->id, 'stock' => 9, 'unit' => 'pcs']);

        $this->actingAs($adminA);
        $this->get(route('products.index'))->assertOk()->assertSee('Produk A')->assertDontSee('Produk B');
        $this->put(route('products.update', $productB), [
            'name' => 'Produk B dimodifikasi',
            'category_id' => $categoryA->id,
            'buy_price' => 0,
            'sell_price' => 0,
            'stock' => 0,
            'unit' => 'pcs',
        ])->assertNotFound();
        $this->get(route('employees.manage', $employeeB))->assertNotFound();
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'name' => 'Produk B', 'store_id' => $storeB->id]);
    }

    public function test_mobile_api_rejects_cross_store_product_ids(): void
    {
        $storeA = Store::create(['name' => 'Store A']);
        $storeB = Store::create(['name' => 'Store B']);
        $adminA = User::factory()->create(['role' => 'admin', 'store_id' => $storeA->id]);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'store_id' => $storeB->id]));
        $productB = Product::create(['name' => 'Produk B', 'stock' => 4, 'unit' => 'pcs']);

        Sanctum::actingAs($adminA);
        $this->getJson('/api/products/'.$productB->id)->assertNotFound();
        $this->putJson('/api/products/'.$productB->id, ['name' => 'Produk B diubah'])->assertNotFound();
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'name' => 'Produk B']);
    }

    public function test_employee_can_accept_an_allocation_for_their_store_through_the_mobile_api(): void
    {
        $store = Store::create(['name' => 'Store SIMKEU']);
        $admin = User::factory()->create(['role' => 'admin', 'store_id' => $store->id]);
        $employee = User::factory()->create(['role' => 'pegawai', 'store_id' => $store->id]);

        Sanctum::actingAs($admin);
        $product = Product::create(['name' => 'Produk Alokasi', 'stock' => 7, 'unit' => 'pcs']);
        $response = $this->postJson(route('employees.stock.allocations.store', $employee), [
            'product_id' => $product->id,
            'requested_quantity' => 3,
        ])->assertCreated();
        $allocationId = $response->json('allocation.id');

        Sanctum::actingAs($employee);
        $this->postJson('/api/stock-allocations/'.$allocationId.'/review', [
            'decision' => 'accept',
            'confirmed_quantity' => 3,
        ])->assertOk()->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 4]);
        $this->assertDatabaseHas('product_user', ['user_id' => $employee->id, 'product_id' => $product->id, 'stock' => 3]);
    }

    public function test_mobile_registration_creates_a_private_store_for_the_new_admin(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'store_name' => 'Toko Baru',
        ])->assertCreated();

        $userId = $response->json('data.user.id');
        $storeId = $response->json('data.user.store_id');
        $this->assertDatabaseHas('users', ['id' => $userId, 'role' => 'admin', 'store_id' => $storeId]);
        $this->assertDatabaseHas('stores', ['id' => $storeId, 'name' => 'Toko Baru']);
    }
}
