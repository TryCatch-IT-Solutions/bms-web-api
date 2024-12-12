<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    if(Schema::hasTable('groups')) {
      Schema::create('devices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('group_id')->constrained()->cascadeOnUpdate();
        $table->string('model');
        $table->string('serial_no');
        $table->string('lat');
        $table->string('lon');
        $table->softDeletes();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('devices');
  }
};
