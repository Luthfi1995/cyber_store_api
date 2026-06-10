<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutMidtransTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CustomerAddress $address;
    protected Expedition $expedition;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->user->id,
            'label' => 'Rumah',
            'receiver_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Kemanggisan No. 20',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Barat',
            'district' => 'Palmerah',
            'postal_code' => '11480',
            'latitude' => '-6.2011',
            'longitude' => '106.7818',
            'is_default' => true,
        ]);

        $this->expedition = Expedition::create([
            'name' => 'JNE Reguler',
            'code' => 'jne',
            'service' => 'REG',
            'base_cost' => 10000,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Kaos',
            'slug' => 'kaos',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Kaos UBSI Keren',
            'slug' => 'kaos-ubsi-keren',
            'description' => 'Baju kaos berkualitas',
            'price' => 75000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'size' => 'L',
            'color' => 'Hitam',
        ]);
    }

    public function test_checkout_calls_midtrans_and_creates_payment_successfully()
    {
        // Fake Midtrans API charge call
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, Bank Transfer transaction is created',
                'transaction_id' => 'midtrans-trans-id-999',
                'order_id' => 'dummy-order-id',
                'gross_amount' => '160000.00',
                'payment_type' => 'bank_transfer',
                'transaction_time' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'va_numbers' => [
                    [
                        'bank' => 'bca',
                        'va_number' => '12345678901'
                    ]
                ]
            ], 201)
        ]);

        // Put Midtrans Key in env config dynamically for test
        config(['services.midtrans.server_key' => 'dummy_server_key']);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/checkout', [
                'customer_address_id' => $this->address->id,
                'expedition_id' => $this->expedition->id,
                'bank_code' => 'bca',
                'note' => 'Tolong diproses ya'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => [
                    'id',
                    'invoice_number',
                    'grand_total',
                    'status',
                    'payment' => [
                        'bank_code',
                        'virtual_account_number',
                        'biller_code',
                        'status',
                        'external_reference'
                    ]
                ]
            ]);

        // Cart should be empty
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(0, $cart->items);

        // Product stock should decrement by 2
        $this->product->refresh();
        $this->assertEquals(8, $this->product->stock);

        // Payment check
        $this->assertDatabaseHas('payments', [
            'bank_code' => 'bca',
            'virtual_account_number' => '12345678901',
            'amount' => 161000.00,
            'status' => 'waiting_payment',
            'external_reference' => 'midtrans-trans-id-999',
        ]);
    }

    public function test_midtrans_callback_handles_successful_payment()
    {
        config(['services.midtrans.server_key' => 'dummy_server_key']);

        // Create a dummy Order and Payment first
        $order = Order::create([
            'invoice_number' => 'INV-TEST-WEBHOOK-111',
            'user_id' => $this->user->id,
            'customer_address_id' => $this->address->id,
            'expedition_id' => $this->expedition->id,
            'subtotal' => 150000,
            'shipping_cost' => 11000,
            'grand_total' => 161000,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'virtual_account_number' => '12345678901',
            'amount' => 161000,
            'status' => Payment::STATUS_WAITING_PAYMENT,
            'external_reference' => 'midtrans-trans-id-999',
        ]);

        // Generate signature key: order_id + status_code + gross_amount + server_key
        $signature = hash('sha512', 'INV-TEST-WEBHOOK-111200161000.00dummy_server_key');

        $response = $this->postJson('/api/v1/payments/midtrans-callback', [
            'order_id' => 'INV-TEST-WEBHOOK-111',
            'status_code' => '200',
            'gross_amount' => '161000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $payment->refresh();

        $this->assertEquals(Order::STATUS_PAID, $order->status);
        $this->assertEquals(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_midtrans_callback_handles_expired_payment_and_restores_stock()
    {
        config(['services.midtrans.server_key' => 'dummy_server_key']);

        // Create a dummy Order and Payment first
        $order = Order::create([
            'invoice_number' => 'INV-TEST-WEBHOOK-222',
            'user_id' => $this->user->id,
            'customer_address_id' => $this->address->id,
            'expedition_id' => $this->expedition->id,
            'subtotal' => 150000,
            'shipping_cost' => 11000,
            'grand_total' => 161000,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'virtual_account_number' => '12345678901',
            'amount' => 161000,
            'status' => Payment::STATUS_WAITING_PAYMENT,
            'external_reference' => 'midtrans-trans-id-999',
        ]);

        // Add item to order so stock can be restored
        $order->items()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'size' => 'L',
            'color' => 'Hitam',
            'price' => 75000,
            'quantity' => 2,
            'total' => 150000,
        ]);

        // Stock was decremented at checkout, let's say it is currently 8 (original was 10)
        $this->product->update(['stock' => 8]);

        // Generate signature key: order_id + status_code + gross_amount + server_key
        // Status code for expire/cancel is usually 202 or 407, Midtrans documentation signature is always calculated with the status_code sent in webhook. Let's say it is 202.
        $signature = hash('sha512', 'INV-TEST-WEBHOOK-222202161000.00dummy_server_key');

        $response = $this->postJson('/api/v1/payments/midtrans-callback', [
            'order_id' => 'INV-TEST-WEBHOOK-222',
            'status_code' => '202',
            'gross_amount' => '161000.00',
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $payment->refresh();
        $this->product->refresh();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertEquals(Payment::STATUS_EXPIRED, $payment->status);
        
        // Stock should be incremented back to 10
        $this->assertEquals(10, $this->product->stock);

        // Check stock movement is created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 2,
            'reference' => 'INV-TEST-WEBHOOK-222',
        ]);
    }
}
