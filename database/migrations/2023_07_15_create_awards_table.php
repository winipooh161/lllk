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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('icon'); // SVG-код иконки или путь к SVG файлу
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('user_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('award_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('awarded_by'); // ID администратора
            $table->foreign('awarded_by')->references('id')->on('users');
            $table->dateTime('awarded_at');
            $table->text('comment')->nullable();
            $table->timestamps();

            // Пользователь может иметь одну и ту же награду несколько раз
            // $table->unique(['user_id', 'award_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_awards');
        Schema::dropIfExists('awards');
    }
};
