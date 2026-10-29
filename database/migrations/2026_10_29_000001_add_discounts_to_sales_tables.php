<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total_discount', 10, 2)->default(0)->after('total');
        });
        Schema::table('sale_details', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_percent');
            $table->decimal('subtotal_with_discount', 10, 2)->nullable()->after('discount_amount');
        });
        // Los detalles anteriores no tenían descuentos: su subtotal neto es el subtotal original.
        \Illuminate\Support\Facades\DB::table('sale_details')
            ->update(['subtotal_with_discount' => \Illuminate\Support\Facades\DB::raw('subtotal')]);
    }

    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'subtotal_with_discount']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('total_discount');
        });
    }
};
