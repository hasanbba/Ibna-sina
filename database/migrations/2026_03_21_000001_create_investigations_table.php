<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->foreignId('consultant_id')->constrained('consultants')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('investigation_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['month', 'consultant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
