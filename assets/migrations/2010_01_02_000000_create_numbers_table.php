<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tanyudii\YinNumber\Services\NumberService;

class CreateNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("numbers", function (Blueprint $table) {
            $table->id();

            $table->string("name");
            $table->string("model");
            $table
                ->enum("reset_type", NumberService::RESET_TYPE_OPTIONS)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("numbers");
    }
}