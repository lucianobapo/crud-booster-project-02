<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
//            $table->increments('id');
            $table->uuid('id');
            $table->integer('owner_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('body')->nullable();

        });

        Schema::table('posts', function (Blueprint $table) {
//            $table->dropPrimary('posts_cod_primary');
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
        Schema::dropIfExists('posts');
    }
}
