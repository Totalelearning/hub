<?php

namespace App\Support;

use App\Models\LearningModule;

class ScormDemoScenario
{
    public const PRIMARY_DEMO_COURSE_TITLE = 'Customer Data Handling Essentials';

    public static function isPrimaryDemoCourse(LearningModule|string|null $module): bool
    {
        $title = $module instanceof LearningModule
            ? $module->title
            : $module;

        return $title === self::PRIMARY_DEMO_COURSE_TITLE;
    }
}
