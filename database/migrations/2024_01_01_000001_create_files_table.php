<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('filename')->unique();
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('gcs_path');
            $table->boolean('is_published')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
