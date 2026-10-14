<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\Chapter;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment-gateway.default' => 'razorpay',
            'payment-gateway.test_mode' => true,
            'payment-gateway.drivers.razorpay.test' => ['key' => 'rzp_test_key', 'secret' => 'test_secret'],
            'payment-gateway.drivers.razorpay.live' => ['key' => 'rzp_live_key', 'secret' => 'live_secret'],
        ]);
    }

    public function test_test_mode_switch_picks_the_matching_razorpay_keys(): void
    {
        Http::fake(['api.razorpay.com/v1/orders' => Http::response(['id' => 'order_1', 'amount' => 141600, 'currency' => 'INR'])]);
        $category = BusinessCategory::create(['name' => 'Real Estate'])->id;
        $visitor = ['type' => 'visitor', 'name' => 'Simran Kaur', 'business_category_id' => $category,
            'phone' => '9876543210', 'email' => 'simran@example.com'];

        $this->postJson('/register', $visitor)->assertJsonPath('order.key', 'rzp_test_key');

        config(['payment-gateway.test_mode' => false]);
        $this->postJson('/register', $visitor)->assertJsonPath('order.key', 'rzp_live_key');

        // The live order was opened with the live credentials, not the test ones.
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('rzp_live_key:live_secret')));
    }

    public function test_visitor_registration_opens_a_razorpay_order_for_fee_plus_gst(): void
    {
        Http::fake(['api.razorpay.com/v1/orders' => Http::response(['id' => 'order_123', 'amount' => 141600, 'currency' => 'INR'])]);

        $this->postJson('/register', [
            'type' => 'visitor',
            'name' => 'Simran Kaur',
            'business_category_id' => BusinessCategory::create(['name' => 'Real Estate'])->id,
            'phone' => '98765 43210',
            'email' => 'simran@example.com',
        ])->assertOk()
            ->assertJsonPath('order.id', 'order_123')
            ->assertJsonPath('order.key', 'rzp_test_key');

        // ₹1,200 + 18% GST, in paise.
        Http::assertSent(fn ($request) => $request['amount'] === 141600 && $request['currency'] === 'INR');

        $registration = Registration::sole();
        $this->assertSame('+919876543210', $registration->phone);
        $this->assertNull($registration->invited_by);
        $this->assertSame('order_123', $registration->razorpay_order_id);
        $this->assertSame(Registration::PAYMENT_CREATED, $registration->payment_status);
    }

    public function test_payment_is_confirmed_only_with_a_genuine_signature(): void
    {
        $registration = Registration::create([
            'reference' => 'REG-TEST0001',
            'type' => 'visitor',
            'name' => 'Simran Kaur',
            'business_category_id' => BusinessCategory::create(['name' => 'Real Estate'])->id,
            'razorpay_order_id' => 'order_123',
            'payment_status' => Registration::PAYMENT_CREATED,
        ]);

        $payment = ['razorpay_order_id' => 'order_123', 'razorpay_payment_id' => 'pay_1'];

        $this->postJson('/register/paid', $payment + ['razorpay_signature' => 'forged'])->assertStatus(422);
        $this->assertSame(Registration::PAYMENT_CREATED, $registration->fresh()->payment_status);

        $signature = hash_hmac('sha256', 'order_123|pay_1', 'test_secret');

        $this->postJson('/register/paid', $payment + ['razorpay_signature' => $signature])
            ->assertOk()
            ->assertJsonPath('redirect', route('register.confirmed', 'REG-TEST0001'));
        $this->assertSame(Registration::PAYMENT_PAID, $registration->fresh()->payment_status);
    }

    public function test_free_registration_is_confirmed_without_payment(): void
    {
        Http::fake();
        Setting::put('member_fee', 0);

        $response = $this->postJson('/register', [
            'type' => 'member',
            'name' => 'Kashiv Seth',
            'business_category_id' => BusinessCategory::create(['name' => 'IT & Software'])->id,
            'chapter_id' => Chapter::create(['name' => 'Solis'])->id,
        ]);

        $registration = Registration::sole();
        $response->assertOk()->assertJsonPath('redirect', route('register.confirmed', $registration->reference));
        $this->assertSame(Registration::PAYMENT_PAID, $registration->payment_status);
        Http::assertNothingSent();

        $this->get(route('register.confirmed', $registration->reference))->assertOk()->assertSee('Kashiv Seth');
    }

    public function test_invite_link_prefills_invited_by_and_ignores_junk(): void
    {
        $this->get('/register?invited_by=Kashiv%20Seth')
            ->assertOk()
            ->assertSee('Kashiv Seth has invited you')
            ->assertSee('value="Kashiv Seth"', false);

        $this->get('/register?invited_by[]=x')->assertOk()->assertSee('Registration open');
    }

    public function test_admin_pages_need_a_sign_in(): void
    {
        $this->get('/admin/registrations')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/registrations')
            ->assertOk()
            ->assertSee('Registration QR code');
    }
}
