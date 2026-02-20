<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('data_file_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cron_expression')->default('0 9 * * *'); // default: daily at 9am
            $table->string('timezone')->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->integer('posts_per_run')->default(1);
            $table->integer('current_line')->default(0); // tracks position in data file
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_schedules');
    }
};
