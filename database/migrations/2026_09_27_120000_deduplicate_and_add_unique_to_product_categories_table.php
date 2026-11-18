<?php

use App\Models\OtherProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Group categories by normalized name and merge duplicates
        $categories = DB::table('product_categories')->orderBy('id')->get();
        $seen = [];

        foreach ($categories as $cat) {
            $normalizedName = strtolower(trim($cat->name));

            if (! isset($seen[$normalizedName])) {
                $seen[$normalizedName] = $cat->id;
            } else {
                $primaryId = $seen[$normalizedName];
                $duplicateId = $cat->id;

                // Re-link catalog products
                DB::table('products')
                    ->where('product_category_id', $duplicateId)
                    ->update(['product_category_id' => $primaryId]);

                // Re-link market / other products
                if (Schema::hasTable('other_products')) {
                    DB::table('other_products')
                        ->where('category_id', $duplicateId)
                        ->update(['category_id' => $primaryId]);
                }

                // Delete duplicate category row
                DB::table('product_categories')->where('id', $duplicateId)->delete();
            }
        }

        // 2. Add unique constraint on name
        Schema::table('product_categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
