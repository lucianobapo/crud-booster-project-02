<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
//            $table->increments('id');
            $table->uuid('id');
            $table->increments('cod')->index();

            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->text('description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropPrimary('products_cod_primary');
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
