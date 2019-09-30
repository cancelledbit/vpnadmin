<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Upd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vpn_users', function (Blueprint $table) {
            $table->string("username")->default('');
            $table->string("server")->default("*");
            $table->string("password");
            $table->string('ip');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vpn_users', function (Blueprint $table) {
            //
        });
    }
}
