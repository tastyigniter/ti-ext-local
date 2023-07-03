<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_auto_lat_lng')->default(0);
        });

        DB::table('locations')->get()->each(function ($location) {
            $values = DB::table('location_options')
                ->where('location_id', $location->location_id)
                ->whereIn('item', ['auto_lat_lng', 'gallery', 'hours'])
                ->pluck('value', 'item')
                ->all();

            DB::table('locations')
                ->where('location_id', $location->location_id)
                ->update([
                    'is_auto_lat_lng' => $values['auto_lat_lng'] ?? 0,
                ]);

            foreach (array_only($values, ['gallery', 'hours']) as $item => $data) {
                DB::table('location_settings')->insert([
                    'location_id' => $location->location_id,
                    'item' => $item,
                    'data' => json_encode($data),
                ]);
            }
        });
    }
};
