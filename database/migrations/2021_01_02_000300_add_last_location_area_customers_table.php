<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('customers', 'last_location_area')) {
            return;
        }

        Schema::table('customers', function(Blueprint $table) {
            $table->text('last_location_area');
        });
    }

    public function down() {}
};
