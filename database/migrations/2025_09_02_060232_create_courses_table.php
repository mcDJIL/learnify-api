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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->string('short_description', 255)->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->uuid('instructor_id')->nullable();
            $table->uuid('category_id')->nullable();
            $table->float('duration_hours')->default(0);
            $table->float('rating')->default(0);
            $table->integer('total_students')->default(0);
            $table->enum('is_completed', [0, 1])->default(0);
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')
                  ->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')
                  ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
