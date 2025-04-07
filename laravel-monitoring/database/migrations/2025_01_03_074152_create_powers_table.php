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
        Schema::create('powers', function (Blueprint $table) {
            $table->id();
            $table->float('reactive_energy')->nullable();
            $table->float('reactive_power')->nullable();
            $table->float('current')->nullable();
            $table->float('voltage')->nullable();
            $table->float('frequency')->nullable();
            $table->float('power_factor')->nullable();
            $table->float('apparent_power')->nullable();
            $table->timestamp('power_timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('powers');
    }
};
