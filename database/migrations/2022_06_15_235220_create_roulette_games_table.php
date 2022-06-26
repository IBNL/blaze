<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('double_game', function (Blueprint $table) {
            $table->id();
            $table->string('id_blaze');
            $table->integer('color');
            $table->integer('roll');
            $table->string('server_seed');
            $table->dateTimeTz('created_at_blaze', $precision = 3);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('double_game');
    }
};
