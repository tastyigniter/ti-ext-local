<?php namespace Igniter\Local\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateClassNamesApiResourcesTable extends Migration
{
    public function up()
    {
        DB::table('igniter_api_resources')
          ->where('controller', 'Igniter\Local\Resources\Menus')
          ->update(['controller' => \Igniter\Local\ApiResources\Menus::class]);

        DB::table('igniter_api_resources')
          ->where('transformer', 'Igniter\Local\Resources\Transformers\MenuTransformer')
          ->update(['transformer' => \Igniter\Local\ApiResources\Transformers\MenuTransformer::class]);

        DB::table('igniter_api_resources')
          ->where('controller', 'Igniter\Local\Resources\Categories')
          ->update(['controller' => \Igniter\Local\ApiResources\Categories::class]);

        DB::table('igniter_api_resources')
          ->where('transformer', 'Igniter\Local\Resources\Transformers\CategoryTransformer')
          ->update(['transformer' => \Igniter\Local\ApiResources\Transformers\CategoryTransformer::class]);

        DB::table('igniter_api_resources')
          ->where('controller', 'Igniter\Local\Resources\Locations')
          ->update(['controller' => \Igniter\Local\ApiResources\Locations::class]);

        DB::table('igniter_api_resources')
          ->where('transformer', 'Igniter\Local\Resources\Transformers\LocationTransformer')
          ->update(['transformer' => \Igniter\Local\ApiResources\Transformers\LocationTransformer::class]);
    }

    public function down()
    {
    }
}