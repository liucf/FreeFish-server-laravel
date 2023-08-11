<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->float('amount');
            // $table->enum('status', [ 'pending', 'paid', 'processing', 'Shipped',  'deliveried', 'completed', 'declined', 'cancelled'])->default('pending');
            $table->string('status')->default('pending')->comment('pending, paid, processing, shipped, deliveried, completed, declined, cancelled');
            $table->string('payment_method')->default('stripe')->comment('cash, stripe, paypal');
            $table->string('payment_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_reviewed')->default(false);
            
            $table->unsignedBigInteger('address_id')->nullable()->references('id')->on('addresses')->onDelete('cascade');
            $table->string('delivered_by')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
