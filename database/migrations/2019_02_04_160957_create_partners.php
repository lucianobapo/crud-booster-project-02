<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
//            $table->increments('id');
            $table->uuid('id');
            $table->increments('cod')->index();
            $table->integer('owner_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->integer('partner_type_id')->nullable()->index();
            $table->uuid('cost_center_id')->nullable()->index();
        });
        Schema::table('partners', function (Blueprint $table) {
            $table->dropPrimary('partners_cod_primary');
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
        Schema::dropIfExists('partners');
    }
}
