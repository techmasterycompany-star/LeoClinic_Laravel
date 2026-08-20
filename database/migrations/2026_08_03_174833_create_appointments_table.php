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
    Schema::create('appointments', function (Blueprint $table) {

        $table->id();

        $table->foreignId('patient_id')
            ->constrained('patient_profiles')
            ->cascadeOnDelete();

        $table->foreignId('doctor_id')
            ->constrained('doctor_profiles')
            ->cascadeOnDelete();

        $table->foreignId('availability_id')
            ->constrained('availabilities')
            ->cascadeOnDelete();

      $table->enum('status', [
             'pending',
             'confirmed',
             'completed',
             'cancelled',
             'rejected'
         ])->default('pending');

        $table->text('reason')->nullable();

        $table->enum('cancelled_by', ['doctor', 'patient'])
            ->nullable();

        $table->timestamps();
    });
}
};
