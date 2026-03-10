<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');
        });

        DB::table('products')->whereNull('user_id')->update(['user_id' => 1]);

        Schema::table('products', function (Blueprint $table) {
            // 3. Ahora que todos tienen dueño, hacemos que la columna sea obligatoria
            // y añadimos la relación (llave foránea)
            $table->foreignId('user_id')->nullable(false)->change()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
