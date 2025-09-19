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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journalist_id')->constrained()->onDelete('cascade'); // journalist
            $table->string('title');
            $table->text('description');
            $table->date('news_date')->nullable(); // the Date field in doc
            $table->string('location')->nullable(); // New Orleans / Biloxi / Mobile / Pensacola
            $table->string('credit')->nullable();
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
