<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main Artist Table
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('name');
            $table->string('genre')->nullable();
            $table->string('image')->nullable(); // profile picture
            $table->string('cover_photo')->nullable(); // new field for cover photo
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        // Artist Genres (many-to-many)
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Artist Photos (up to 5 optional but handled in validation)
        Schema::create('artist_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('photo_url');
            $table->timestamps();
        });

        // Artist Songs (mp3 embed urls)
        Schema::create('artist_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('mp3_url'); // could be S3 or external link
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_songs');
        Schema::dropIfExists('artist_photos');
        Schema::dropIfExists('artist_genre');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('artists');
    }
};
