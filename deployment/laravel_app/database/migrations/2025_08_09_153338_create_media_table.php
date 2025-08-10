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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->enum('type', ['image', 'video']);
            $table->string('path_or_embed');
            $table->string('caption')->nullable();
            $table->boolean('is_home_slider')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('bytes')->nullable();
            $table->integer('order')->default(0);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
            $table->index('is_home_slider');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
