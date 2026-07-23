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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('book_number')->nullable()->index();
            $table->date('book_date')->nullable()->index();
            $table->string('title')->index();
            $table->string('author')->nullable()->index();
            $table->string('publisher')->nullable();
            $table->string('edition', 100)->nullable();
            $table->unsignedSmallInteger('publish_year')->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained('book_categories')->restrictOnDelete();
            $table->string('document_type')->default('book')->index();
            $table->text('keywords')->nullable();
            $table->longText('notes')->nullable();
            $table->string('status')->default('archived')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
