<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calendar_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at');             // stored in UTC
            $table->dateTime('ends_at');               // stored in UTC
            $table->string('timezone')->nullable();    // overrides calendar timezone, e.g. America/New_York
            $table->string('rrule')->nullable();       // e.g. FREQ=DAILY;COUNT=5
            $table->integer('reminder_minutes')->nullable(); // minutes before start to notify
            $table->timestamps();
            $table->softDeletes();

            $table->index('calendar_id');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
