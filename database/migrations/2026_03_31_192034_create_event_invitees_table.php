<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_invitees', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->morphs('inviteable'); // user or group
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->unique(['calendar_event_id', 'inviteable_id', 'inviteable_type'], 'event_invitees_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_invitees');
    }
};
