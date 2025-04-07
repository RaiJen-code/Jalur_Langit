<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('current');  // nama parameter (misalnya: current)
            $table->float('min_value')->nullable();
            $table->float('max_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thresholds');
    }
};