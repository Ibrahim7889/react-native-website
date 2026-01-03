<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->enum('state', ['scheduled', 'betting', 'spinning', 'resolved'])->default('scheduled');
            $table->string('seed_hash')->nullable();
            $table->binary('seed')->nullable();
            $table->unsignedTinyInteger('result_number')->nullable();
            $table->string('result_color')->nullable();
            $table->timestamp('betting_opened_at')->nullable();
            $table->timestamp('betting_closed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
