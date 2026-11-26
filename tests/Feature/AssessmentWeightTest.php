<?php

namespace Tests\Feature;

use App\Models\AssessmentComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentWeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_weight_total(): void
    {
        AssessmentComponent::create(['name'=>'A','type'=>'formatif','weight'=>40,'is_active'=>true]);
        AssessmentComponent::create(['name'=>'B','type'=>'sumatif','weight'=>60,'is_active'=>true]);
        $this->assertEquals(100, AssessmentComponent::sum('weight'));
    }

    public function test_invalid_weight_total(): void
    {
        AssessmentComponent::create(['name'=>'A','type'=>'formatif','weight'=>60,'is_active'=>true]);
        AssessmentComponent::create(['name'=>'B','type'=>'sumatif','weight'=>60,'is_active'=>true]);
        $this->assertGreaterThan(100, AssessmentComponent::sum('weight'));
    }
}
