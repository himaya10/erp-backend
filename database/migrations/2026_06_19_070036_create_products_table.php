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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('product_name');
        $table->string('sku')->unique(); // බඩු වලට අදාළ Unique Code එක
        $table->string('type'); // Raw material ද Finished product ද කියලා
        $table->decimal('price', 10, 2);
        $table->integer('quantity')->default(0); // Inventory එකේ දැනට තියෙන ප්‍රමාණය
        $table->integer('min_stock_level')->default(5); // Alert වෙන්න ඕන අඩුම සීමාව
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
