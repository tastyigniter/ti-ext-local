<?php

namespace Igniter\Local\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToReviews extends Migration
{
    public function up()
    {
        Schema::table('igniter_reviews', function (Blueprint $table) {
            $table->timestamp('date_added')->change();
            $table->renameColumn('date_added', 'created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down()
    {
    }
}
