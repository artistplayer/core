<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('api_token', 60)->unique()->nullable();
            $table->string('photo_url')->nullable()->default('default-placeholder.png');
            $table->boolean('is_supervisor')->default(false);
            $table->boolean('is_active')->default(true);

            $table->integer('plot_id')->nullable();
            $table->integer('project_id')->nullable();

            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();

            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_suffix')->nullable();
            $table->string('address_zipcode')->nullable();
            $table->string('address_city')->nullable();

            $table->string('internal_note')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
