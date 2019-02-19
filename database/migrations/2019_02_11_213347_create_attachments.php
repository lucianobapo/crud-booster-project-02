<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
//            $table->increments('id');
            $table->uuid('id');
            $table->integer('owner_id')->nullable()->index();
            $table->integer('cms_privilege_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->boolean('check_referer')->nullable();
            $table->string('referers')->nullable();
            $table->boolean('check_permissions')->nullable();
            $table->string('file')->index();
        });


        Schema::table('attachments', function (Blueprint $table) {
//            $table->dropPrimary('attachments_cod_primary');
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
        Schema::dropIfExists('attachments');
    }
}
