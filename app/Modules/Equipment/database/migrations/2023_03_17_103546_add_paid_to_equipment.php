<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean("major")->default(false);
        });
    }


    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            //
        });
    }
};
