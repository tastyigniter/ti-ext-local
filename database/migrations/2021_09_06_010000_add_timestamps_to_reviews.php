<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('igniter_reviews', 'date_added')) {
            return;
        }

        Schema::table('igniter_reviews', function(Blueprint $table) {
            $table->timestamp('date_added')->change();
            $table->renameColumn('date_added', 'created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down() {}
};
