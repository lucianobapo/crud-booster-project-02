<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->increments('cod')->index();
            $table->integer('owner_id')->nullable()->index();
            $table->integer('order_type_id')->nullable()->index();
            $table->uuid('partner_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
            $table->text('description')->nullable();
            $table->double('total')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropPrimary('orders_cod_primary');
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
        Schema::dropIfExists('orders');
    }
}
