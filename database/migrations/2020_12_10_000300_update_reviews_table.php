<?php

namespace Igniter\Local\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateReviewsTable extends Migration
{
    public function up()
    {
        Schema::table('igniter_reviews', function (Blueprint $table) {
            $table->integer('customer_id')->nullable()->change();
            $table->string('author')->nullable()->change();
            $table->text('review_text')->nullable()->change();
        });

        $this->updateMorphsOnReviews();
    }

    public function down()
    {
    }

    protected function updateMorphsOnReviews()
    {
        if (DB::table('igniter_reviews')
            ->where('sale_type', 'Admin\Models\Orders_model')
            ->orWhere('sale_type', 'Admin\Models\Reservations_model')
            ->count()
        ) return;

        $morphs = [
            'order' => 'Admin\Models\Orders_model',
            'reservation' => 'Admin\Models\Reservations_model',
        ];

        DB::table('igniter_reviews')->get()->each(function ($model) use ($morphs) {
            if (!isset($morphs[$model->sale_type]))
                return FALSE;

            DB::table('igniter_reviews')->where('review_id', $model->review_id)->update([
                'sale_type' => $morphs[$model->sale_type],
            ]);
        });
    }
}
