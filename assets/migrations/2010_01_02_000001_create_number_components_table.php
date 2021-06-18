<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tanyudii\YinNumber\Services\NumberService;

class CreateNumberComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("number_components", function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("number_id");
            $table->unsignedInteger("sequence");
            $table->enum("type", NumberService::COMPONENT_TYPE_OPTIONS);
            $table->string("format");

            $table->timestamps();

            $table
                ->foreign("number_id")
                ->references("id")
                ->on("numbers")
                ->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("number_components");
    }
}
