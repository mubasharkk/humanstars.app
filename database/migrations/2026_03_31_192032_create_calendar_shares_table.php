<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('calendar_id')->constrained()->cascadeOnDelete();
            $table->morphs('shareable'); // user or group
            $table->enum('permission', ['READ', 'READWRITE'])->default('READ');
            $table->timestamps();

            $table->unique(['calendar_id', 'shareable_id', 'shareable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_shares');
    }
};
