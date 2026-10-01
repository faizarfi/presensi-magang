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
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('check_out_time')->nullable()->after('check_in_time');
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('longitude');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->string('check_out_photo')->nullable()->after('photo');
            $table->boolean('is_late')->default(false)->after('check_out_time');
            $table->integer('late_minutes')->default(0)->after('is_late');
            $table->text('late_reason')->nullable()->after('late_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_out_time',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_photo',
                'is_late',
                'late_minutes',
                'late_reason',
            ]);
        });
    }
};
