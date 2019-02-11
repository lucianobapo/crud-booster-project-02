<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostCenters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_centers', function (Blueprint $table) {
//            $table->increments('id');
            $table->uuid('id');
            $table->increments('cod')->index();

            $table->timestamps();
            $table->softDeletes();
            $table->integer('owner_id')->nullable()->index();

            $table->string('name');
        });

        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropPrimary('cost_centers_cod_primary');
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
        Schema::dropIfExists('cost_centers');
    }
}
