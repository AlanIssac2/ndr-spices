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
    Schema::create('cart_items', function (Blueprint $table) {
        $table->id();
        // Links the cart to the specific logged-in user
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        // Links to the product (e.g., Black Pepper)
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        // Links to the specific weight variant (e.g., 25g, 100g)
        $table->unsignedBigInteger('variant_id'); 
        // How many packets they want
        $table->integer('quantity')->default(1);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('cart_items');
}
};
