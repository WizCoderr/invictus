<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // The form has no login, so the confirmation page is found by this unguessable reference.
            $table->string('reference')->unique();

            $table->enum('type', ['member', 'visitor']);
            $table->string('name');
            $table->foreignId('business_category_id')->constrained();
            $table->foreignId('chapter_id')->nullable()->constrained();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('invited_by')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('payment_status')->default('created'); // created|paid

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
