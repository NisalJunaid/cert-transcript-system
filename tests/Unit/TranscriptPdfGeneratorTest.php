<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Student;
use App\Models\Transcript;
use App\Services\TranscriptPdfGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TranscriptPdfGeneratorTest extends TestCase
{
    private function resolveTemplate(Transcript $transcript, string $requestedTemplate = 'auto'): string
    {
        $generator = new TranscriptPdfGenerator();
        $method = new ReflectionMethod($generator, 'resolveTemplateForTranscript');
        $method->setAccessible(true);

        return $method->invoke($generator, $transcript, $requestedTemplate);
    }

    public function testAssociateDegreeTemplateUsedForLevelSixCourse(): void
    {
        $transcript = new Transcript();
        $transcript->setRelation('course', new Course(['level' => 6]));
        $transcript->setRelation('student', new Student());

        $template = $this->resolveTemplate($transcript);

        $this->assertSame('associate-degree', $template);
    }

    public function testBachelorsTemplateUsedForBachelorsLevelCourse(): void
    {
        $transcript = new Transcript();
        $transcript->setRelation('course', new Course(['level' => 'Bachelors']));
        $transcript->setRelation('student', new Student());

        $template = $this->resolveTemplate($transcript);

        $this->assertSame('bachelors-single', $template);
    }
}
