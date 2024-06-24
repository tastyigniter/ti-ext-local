<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('igniter_reviews', function(Blueprint $table) {
            $table->renameColumn('sale_id', 'reviewable_id');
            $table->renameColumn('sale_type', 'reviewable_type');
        });
    }

    public function down() {}
};
