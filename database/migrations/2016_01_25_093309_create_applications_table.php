<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('req_id');
                $table->integer('user_id');
                $table->string('contacts');
                $table->string('mobile');
                $table->text('intro');
                $table->integer('status');

                $table->timestamps();
                $table->softDeletes();
                $table->unique(['req_id', 'user_id']);
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
        Schema::drop('applications');
    }
}
