<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function(Blueprint $table): void {
            $table->boolean('is_featured')->default(false);
            $table->date('featured_from')->nullable();
            $table->date('featured_until')->nullable();
        });
    }
};
