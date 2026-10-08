<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendOrderConfirmation;
use Tests\TestCase;

class EcommerceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_login_and_logout(): void
    {
        $this->postJson('/api/register', ['name' => 'Alex', 'email' => 'alex@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertCreated()->assertJsonStructure(['data' => ['id', 'name', 'email'], 'token']);
        $this->postJson('/api/login', ['email' => 'alex@example.com', 'password' => 'wrong-password'])->assertUnprocessable();
        $response = $this->postJson('/api/login', ['email' => 'alex@example.com', 'password' => 'password123'])->assertOk();
        $token = $response->json('token');
        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('data.email', 'alex@example.com');
        $this->withToken($token)->postJson('/api/logout')->assertOk();
    }

    public function test_authentication_routes_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', ['email' => 'missing@example.com', 'password' => 'wrong-password']);
        }
        $this->postJson('/api/login', ['email' => 'missing@example.com', 'password' => 'wrong-password'])->assertStatus(429);
    }

    public function test_category_crud(): void
    {
        $this->actingAs($this->adminUser(), 'sanctum');
        $this->postJson('/api/categories', ['name' => 'Audio'])->assertCreated();
        $category = Category::where('name', 'Audio')->firstOrFail();
        $this->getJson('/api/categories/'.$category->id)->assertOk()->assertJsonPath('data.name', 'Audio');
        $this->putJson('/api/categories/'.$category->id, ['name' => 'Sound'])->assertOk()->assertJsonPath('data.name', 'Sound');
        $this->deleteJson('/api/categories/'.$category->id)->assertNoContent();
    }

    public function test_product_catalog_search_pagination_and_crud(): void
    {
        $this->actingAs($this->adminUser(), 'sanctum');
        $category = Category::factory()->create();
        Product::factory(3)->create(['category_id' => $category->id, 'name' => 'Phone Case']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Desk Lamp']);
        $this->getJson('/api/products?search=phone&per_page=2')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 3)->assertJsonPath('meta.current_page', 1);
        $payload = ['category_id' => $category->id, 'name' => 'New Cable', 'description' => 'USB cable', 'price' => '12.50', 'stock' => 8];
        $this->postJson('/api/products', $payload)->assertCreated();
        $product = Product::where('name', 'New Cable')->firstOrFail();
        $this->putJson('/api/products/'.$product->id, ['price' => '15.00'])->assertOk()->assertJsonPath('data.price', '15.00');
        $this->deleteJson('/api/products/'.$product->id)->assertNoContent();
        $this->postJson('/api/products', [])->assertUnprocessable();
    }

    public function test_cart_ownership_stock_and_order_transaction(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => '12.50', 'stock' => 5]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertCreated();
        $cartItem = Cart::where('user_id', $user->id)->firstOrFail()->items()->firstOrFail();
        $this->actingAs($other, 'sanctum')->putJson('/api/cart/items/'.$cartItem->id, ['quantity' => 1])->assertNotFound();
        $this->actingAs($user, 'sanctum')->postJson('/api/orders')->assertCreated()->assertJsonPath('data.total_amount', '25.00');
        Queue::assertPushed(SendOrderConfirmation::class);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
        $order = Order::where('user_id', $user->id)->firstOrFail();
        $this->actingAs($other, 'sanctum')->getJson('/api/orders/'.$order->id)->assertNotFound();
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'price' => '12.50', 'subtotal' => '25.00']);
    }

    public function test_insufficient_stock_keeps_order_and_cart_unchanged(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 1]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $product->update(['stock' => 0]);
        $this->actingAs($user, 'sanctum')->postJson('/api/orders')->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['cart_id' => Cart::where('user_id', $user->id)->value('id'), 'quantity' => 1]);
    }

    public function test_cart_increments_duplicate_items_updates_quantity_and_removes_items(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 10]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertCreated();
        $item = Cart::where('user_id', $user->id)->firstOrFail()->items()->firstOrFail();
        $this->assertSame(3, $item->quantity);
        $this->putJson('/api/cart/items/'.$item->id, ['quantity' => 4])->assertOk();
        $this->assertSame(4, $item->fresh()->quantity);
        $this->deleteJson('/api/cart/items/'.$item->id)->assertNoContent();
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_normal_users_cannot_manage_products_or_categories(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $this->postJson('/api/categories', ['name' => 'Audio'])->assertForbidden();
        $this->postJson('/api/products', [])->assertForbidden();
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }
}
