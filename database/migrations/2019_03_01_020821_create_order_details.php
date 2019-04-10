<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();


            $table->integer('owner_id')->nullable()->index();
            $table->uuid('order_id')->nullable()->index();
            $table->uuid('product_id')->nullable()->index();

            $table->text('description')->nullable();
            $table->double('price')->nullable();
            $table->float('quantity')->nullable();
            $table->double('sub_total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
