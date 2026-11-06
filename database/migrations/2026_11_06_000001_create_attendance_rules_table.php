<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('attendance_rules', function(Blueprint $table) {
   $table->id(); $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
   $table->string('name'); $table->string('code',30)->nullable();
   $table->time('shift_start')->nullable(); $table->time('shift_end')->nullable();
   $table->unsignedSmallInteger('grace_minutes')->default(0);
   $table->decimal('minimum_hours',4,2)->default(0); $table->boolean('is_active')->default(true);
   $table->timestamps(); $table->index(['school_id','is_active']);
  });
 }
 public function down(): void { Schema::dropIfExists('attendance_rules'); }
};
