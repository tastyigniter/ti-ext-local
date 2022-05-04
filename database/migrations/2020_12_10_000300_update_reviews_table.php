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
            ->where('sale_type', \Admin\Models\Orders_model::class)
            ->orWhere('sale_type', \Admin\Models\Reservations_model::class)
            ->count()
        ) return;

        $morphs = [
            'order' => \Admin\Models\Orders_model::class,
            'reservation' => \Admin\Models\Reservations_model::class,
        ];

        DB::table('igniter_reviews')->get()->each(function ($model) use ($morphs) {
            if (!isset($morphs[$model->sale_type]))
                return false;

            DB::table('igniter_reviews')->where('review_id', $model->review_id)->update([
                'sale_type' => $morphs[$model->sale_type],
            ]);
        });
    }
}
