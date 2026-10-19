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
        if (! Schema::hasTable('face_profiles')) {
            Schema::create('face_profiles', function (Blueprint $table) {
                $table->id();
            });
        }

        Schema::table('face_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('face_profiles', 'student_id')) {
                $table->foreignId('student_id')->unique()->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('face_profiles', 'face_embedding')) {
                $table->longText('face_embedding')->nullable();
            }

            if (! Schema::hasColumn('face_profiles', 'enrolled_at')) {
                $table->timestamp('enrolled_at')->nullable();
            }

            if (! Schema::hasColumn('face_profiles', 'status')) {
                $table->string('status')->default('not_enrolled');
            }

            if (! Schema::hasColumn('face_profiles', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('face_profiles')) {
            return;
        }

        Schema::table('face_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('face_profiles', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('face_profiles', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }

            if (Schema::hasColumn('face_profiles', 'face_embedding')) {
                $table->dropColumn('face_embedding');
            }

            if (Schema::hasColumn('face_profiles', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }
        });
    }
};
