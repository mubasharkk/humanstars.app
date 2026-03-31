<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->renameColumn('location', 'address');

            $table->enum('type', ['virtual', 'on-site'])->default('on-site')->after('description');
            $table->string('meeting_url')->nullable()->after('type');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->renameColumn('address', 'location');

            $table->dropColumn(['type', 'meeting_url', 'latitude', 'longitude']);
        });
    }
};
