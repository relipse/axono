<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type')->default('text/plain');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('total_lines')->default(0);
            $table->unsignedInteger('used_lines')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_files');
    }
};
