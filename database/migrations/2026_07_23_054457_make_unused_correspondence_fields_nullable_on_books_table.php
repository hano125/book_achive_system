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
            $table->string('department')->nullable()->change();
            $table->string('reference_number')->nullable()->change();
            $table->unsignedInteger('quantity')->nullable()->change();
            $table->string('title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('department')->nullable(false)->change();
            $table->string('reference_number')->nullable(false)->change();
            $table->unsignedInteger('quantity')->nullable(false)->change();
            $table->string('title')->nullable(false)->change();
        });
    }
};
