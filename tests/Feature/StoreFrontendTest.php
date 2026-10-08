<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StoreFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_listing_search_and_category_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Garden Guide']);
        Product::factory()->create(['name' => 'Desk Lamp']);

        $this->get('/?search=Garden&category='.$category->id)
            ->assertOk()
            ->assertSee('Garden Guide')
            ->assertDontSee('Desk Lamp');
    }

    public function test_customer_can_add_to_cart_and_place_an_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Garden Guide', 'price' => '12.50', 'stock' => 5]);

        $this->actingAs($user)
            ->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))->assertOk()->assertSee('25.00');
        $this->get(route('checkout.index'))->assertOk()->assertSee('Place order');
        $this->post(route('checkout.place'))->assertRedirect(route('orders.confirmation', 1));

        $order = Order::firstOrFail();
        $this->assertSame('25.00', $order->total_amount);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Garden');
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::create(['user_id' => $owner->id, 'total_amount' => '15.00', 'status' => 'confirmed']);

        $this->actingAs($other)->get(route('orders.show', $order))->assertNotFound();
    }

    public function test_registration_and_login_use_the_web_session(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('store.index'));

        $this->assertAuthenticated();
        $this->post(route('logout'))->assertRedirect(route('store.index'));
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => 'alex@example.com', 'password' => 'password123'])
            ->assertRedirect(route('store.index'));
        $this->assertAuthenticated();
    }

    public function test_filament_management_is_only_available_to_admin_users(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/products')->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->actingAs($admin)
            ->get('/admin/products/create')
            ->assertOk()
            ->assertSee('Product');

        $this->get('/admin/categories/create')
            ->assertOk()
            ->assertSee('Category');
    }

    public function test_admin_can_create_categories_and_products_in_filament(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateCategory::class)
            ->fillForm(['name' => 'Office'])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::where('name', 'Office')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Desk Organizer',
                'category_id' => $category->id,
                'description' => 'A small desk organizer.',
                'price' => '12.50',
                'stock' => 8,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', ['name' => 'Desk Organizer', 'category_id' => $category->id, 'stock' => 8]);

        $product = Product::where('name', 'Desk Organizer')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['name' => 'Desk Organizer Updated', 'price' => '15.00'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Desk Organizer Updated', 'price' => '15.00']);

        Livewire::actingAs($admin)
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction(DeleteAction::class);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        Livewire::actingAs($admin)
            ->test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Workspace'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Workspace']);

        Livewire::actingAs($admin)
            ->test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->callAction(DeleteAction::class);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_command_creates_a_filament_admin_when_user_does_not_exist(): void
    {
        $this->artisan('app:make-admin admin@example.com')
            ->expectsQuestion('Name', 'Shop Admin')
            ->expectsQuestion('Password', 'password123')
            ->expectsQuestion('Confirm password', 'password123')
            ->assertExitCode(0);

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Shop Admin', $user->name);
        $this->assertTrue($user->is_admin);
        $this->assertTrue(password_verify('password123', $user->password));
    }
}
