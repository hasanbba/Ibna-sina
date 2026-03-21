<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('in_outs', function (Blueprint $table) {
            $table->unique(['date', 'consultant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('in_outs', function (Blueprint $table) {
            $table->dropUnique('in_outs_date_consultant_id_unique');
        });
    }
};
