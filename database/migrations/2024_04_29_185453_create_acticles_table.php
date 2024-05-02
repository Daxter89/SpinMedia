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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->default(null);
            $table->string('link')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
            $table->string('pubDate')->nullable()->default(null);
            $table->string('picture')->nullable()->default(null);
            $table->string('item1_url')->nullable()->default(null);
            $table->string('item1_title')->nullable()->default(null);
            $table->string('item1_source')->nullable()->default(null);
            $table->string('item2_url')->nullable()->default(null);
            $table->string('item2_title')->nullable()->default(null);
            $table->string('item2_source')->nullable()->default(null);
            $table->string('article_title')->nullable()->default(null);
            $table->string('article_description')->nullable()->default(null);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
