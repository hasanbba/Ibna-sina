<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reportings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('consultant_id')->constrained('consultants')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->integer('new')->default(0);
            $table->integer('report')->default(0);
            $table->integer('follow_up')->default(0);
            $table->integer('back')->default(0);
            $table->integer('total')->default(0);
            $table->foreignId('remark_id')->constrained('remarks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportings');
    }
};
