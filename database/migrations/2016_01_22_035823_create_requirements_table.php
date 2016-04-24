<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('requirements')) {
            Schema::create('requirements', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('title');
                $table->string('contacts');
                $table->string('mobile');
                $table->text('intro');
                $table->integer('status')->default(0);
                $table->date('begin_time');
                $table->date('end_time');

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('requirements');
    }
}
