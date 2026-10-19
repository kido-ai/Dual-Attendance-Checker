<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FaceProfileSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_face_profiles_table_has_required_enrollment_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('face_profiles', 'student_id'));
        $this->assertTrue(Schema::hasColumn('face_profiles', 'face_embedding'));
        $this->assertTrue(Schema::hasColumn('face_profiles', 'status'));
        $this->assertTrue(Schema::hasColumn('face_profiles', 'enrolled_at'));
    }
}
