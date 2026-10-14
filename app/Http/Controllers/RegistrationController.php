<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use App\Models\Chapter;
use App\Models\Registration;
use App\Services\RazorpayService;
use App\Support\MobileNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {
        // From a member's personal invite QR (?invited_by=Name). Anything but a plain string is ignored.
        $invitedBy = $request->query('invited_by');

        return view('registration.form', [
            'businessCategories' => BusinessCategory::orderBy('name')->get(),
            'chapters' => Chapter::orderBy('name')->get(),
            'fees' => [
                Registration::TYPE_MEMBER => Registration::pricing(Registration::TYPE_MEMBER),
                Registration::TYPE_VISITOR => Registration::pricing(Registration::TYPE_VISITOR),
            ],
            'invitedBy' => is_string($invitedBy) ? Str::limit($invitedBy, 120, '') : '',
        ]);
    }

    public function store(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $data = $this->validated($request);
        $pricing = Registration::pricing($data['type']);
        $reference = 'REG-'.Str::upper(Str::random(8));

        if ($pricing['total_amount'] <= 0) {
            Registration::create($data + $pricing + [
                'reference' => $reference,
                'payment_status' => Registration::PAYMENT_PAID,
            ]);

            return response()->json([
                'message' => 'Registration complete.',
                'redirect' => route('register.confirmed', $reference),
            ]);
        }

        // No row until the money lands: the form rides in the order's notes (Razorpay allows
        // 15 of up to 256 chars; this is 11) and record() writes it once payment is confirmed.
        $notes = array_map('strval', array_filter($data + $pricing, fn ($value) => $value !== null));

        try {
            $order = $razorpay->createOrder($pricing['total_amount'], $reference, $notes);
        } catch (\Throwable $e) {
            Log::error('Razorpay order could not be opened for a registration.', ['exception' => $e]);

            return response()->json([
                'message' => 'We could not reach the payment gateway just now. '
                    .'Nothing has been charged — please try again in a moment.',
            ], 502);
        }

        return response()->json([
            'message' => 'Complete payment to finish your registration.',
            'order' => $order,
        ]);
    }

    public function paid(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if (! $razorpay->verify($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature'])) {
            return response()->json([
                'message' => 'We could not verify that payment. If money was taken, your '
                    .'registration will be confirmed as soon as the bank tells us.',
            ], 422);
        }

        // If Razorpay can't be reached this throws a 500 and the form falls back to polling status().
        $registration = $this->record($razorpay, $data['razorpay_order_id'], $data['razorpay_payment_id']);

        return response()->json([
            'message' => 'Payment received — your registration is confirmed.',
            'redirect' => route('register.confirmed', $registration->reference),
        ]);
    }

    /** Asks Razorpay directly how an order stands, for when /register/paid didn't get through. */
    public function status(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $orderId = $data['razorpay_order_id'];

        $registration = Registration::firstWhere('razorpay_order_id', $orderId);

        if ($registration?->payment_status !== Registration::PAYMENT_PAID) {
            try {
                foreach ($razorpay->payments($orderId) as $entity) {
                    if (($entity['status'] ?? null) === 'captured') {
                        $registration = $this->record($razorpay, $orderId, $entity['id'] ?? null);
                        break;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not ask Razorpay how a registration order stands.', ['exception' => $e]);
            }
        }

        $paid = $registration?->payment_status === Registration::PAYMENT_PAID;

        return response()->json([
            'status' => $paid ? Registration::PAYMENT_PAID : Registration::PAYMENT_CREATED,
            'redirect' => $paid ? route('register.confirmed', $registration->reference) : null,
        ]);
    }

    /** Writes the registration for a paid order from the form data store() left in its notes. */
    private function record(RazorpayService $razorpay, string $orderId, ?string $paymentId): Registration
    {
        // An existing row is a retry of the same order, or one opened before rows waited for payment.
        // ponytail: paid() and status() could race and write it twice; a unique index on razorpay_order_id stops that if it ever happens.
        $registration = Registration::firstWhere('razorpay_order_id', $orderId);

        if (! $registration) {
            $order = $razorpay->order($orderId);
            $registration = new Registration([...$order['notes'], 'reference' => $order['receipt'], 'razorpay_order_id' => $orderId]);
        }

        $registration->fill(['payment_status' => Registration::PAYMENT_PAID, 'razorpay_payment_id' => $paymentId])->save();

        return $registration;
    }

    public function confirmed(string $reference)
    {
        $registration = Registration::where('reference', $reference)
            ->where('payment_status', Registration::PAYMENT_PAID)
            ->with('businessCategory', 'chapter')
            ->firstOrFail();

        return view('registration.confirmed', ['registration' => $registration]);
    }

    private function validated(Request $request): array
    {
        $phone = $request->input('phone');

        $request->merge([
            'phone' => is_string($phone) && $phone !== '' ? MobileNumber::normalise($phone) : $phone,
        ]);

        $data = $request->validate([
            'type' => ['required', Rule::in([Registration::TYPE_MEMBER, Registration::TYPE_VISITOR])],
            'name' => ['required', 'string', 'max:120'],
            'business_category_id' => ['required', 'exists:business_categories,id'],
            'chapter_id' => ['required_if:type,'.Registration::TYPE_MEMBER, 'nullable', 'exists:chapters,id'],
            'phone' => ['required_if:type,'.Registration::TYPE_VISITOR, 'nullable', 'regex:'.MobileNumber::PATTERN],
            'email' => ['required_if:type,'.Registration::TYPE_VISITOR, 'nullable', 'email', 'max:120'],
            'invited_by' => ['nullable', 'string', 'max:120'],
        ], [
            'phone.regex' => 'Enter a 10-digit mobile number starting 6, 7, 8 or 9, or a number from abroad with its + country code.',
        ]);

        return $data['type'] === Registration::TYPE_MEMBER
            ? [...$data, 'phone' => null, 'email' => null, 'invited_by' => null]
            : [...$data, 'chapter_id' => null];
    }
}
