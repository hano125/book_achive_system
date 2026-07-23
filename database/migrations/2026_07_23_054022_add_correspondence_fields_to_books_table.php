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
        Schema::table('books', function (Blueprint $table) {
            $table->string('department')->after('book_number')->index();
            $table->string('reference_number')->after('department')->index();
            $table->unsignedInteger('quantity')->after('reference_number');
            $table->text('procedure')->nullable()->after('title');
            $table->text('subject')->after('procedure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'reference_number',
                'quantity',
                'procedure',
                'subject',
            ]);
        });
    }
};
