<?php

namespace Tests\Unit\Support;

use App\Models\LearningModule;
use App\Support\ScormDemoScenario;
use PHPUnit\Framework\TestCase;

class ScormDemoScenarioTest extends TestCase
{
    public function test_it_identifies_the_primary_demo_course_by_title(): void
    {
        $this->assertTrue(ScormDemoScenario::isPrimaryDemoCourse('Customer Data Handling Essentials'));
        $this->assertFalse(ScormDemoScenario::isPrimaryDemoCourse('Manager Coaching Conversations'));

        $module = new LearningModule(['title' => 'Customer Data Handling Essentials']);

        $this->assertTrue(ScormDemoScenario::isPrimaryDemoCourse($module));
    }
}
