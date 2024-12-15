<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::table('devices', function (Blueprint $table) {
      $table->string('lat')->nullable()->change();
      $table->string('lon')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
  }
};
