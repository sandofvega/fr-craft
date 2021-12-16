<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Fr\CraftPluginList\Commands\CraftPluginListCommand;

/**
 * @see https://symfony.com/doc/current/console.html#testing-commands
 */
final class CraftPluginListCommandTest extends TestCase
{
    private $applicationTester;

    protected function setUp(): void
    {
        $craftPluginListCommand = new CraftPluginListCommand;
        
        $application = new Application();
        $application->add($craftPluginListCommand);
        $application->setDefaultCommand($craftPluginListCommand->getName(), true);
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);
    }

    public function testTableOutput(): void
    {
        $this->applicationTester->run([
            '--limit' => 1
        ]);

        // check SUCCESS
        $this->applicationTester->assertCommandIsSuccessful('Something went wrong!!');

        // check table output
        $display = $this->applicationTester->getDisplay();
        $this->assertStringEndsWith("-+\n", $display, 'Error in table output');
    }

    public function testLimitOptionNonStringValidation(): void
    {
        $this->applicationTester->run([
            '--limit' => 'five'
        ]);

        // check FAILURE
        $statusCode = $this->applicationTester->getStatusCode();
        $this->assertEquals($statusCode, 1, 'Limit option validation not working.');

        // check error message
        $display = $this->applicationTester->getDisplay();
        $this->assertEquals(
            $display,
            "Invalid limit option\n",
            'Didn\'t return correct error message.'
        );
    }

    public function testLimitOptionNonNegativeValidation(): void
    {
        $this->applicationTester->run([
            '--limit' => -5
        ]);

        // check FAILURE
        $statusCode = $this->applicationTester->getStatusCode();
        $this->assertEquals($statusCode, 1, 'Limit option validation not working.');

        // check error message
        $display = $this->applicationTester->getDisplay();
        $this->assertEquals(
            $display,
            "Invalid limit option\n",
            'Limit option validation didn\'t return correct error message.'
        );
    }

    public function testOrderByOptionValidation(): void
    {
        $this->applicationTester->run([
            '--orderBy' => 'version'
        ]);

        // check FAILURE
        $statusCode = $this->applicationTester->getStatusCode();
        $this->assertEquals($statusCode, 1, 'Order by option validation not working.');

        // check error message
        $display = $this->applicationTester->getDisplay();
        $this->assertEquals(
            $display,
            "Invalid orderBy option\n",
            'Order by option validation didn\'t return correct error message.'
        );
    }

    public function testOrderOptionValidation(): void
    {
        $this->applicationTester->run([
            '--order' => 'dasc'
        ]);

        // check FAILURE
        $statusCode = $this->applicationTester->getStatusCode();
        $this->assertEquals($statusCode, 1, 'Order option validation not working.');

        // check error message
        $display = $this->applicationTester->getDisplay();
        $this->assertEquals(
            $display,
            "Invalid order option\n",
            'Order option validation didn\'t return correct error message.'
        );
    }

    public function testOutputOptionValidation(): void
    {
        $this->applicationTester->run([
            '--output' => 'output_file.txt'
        ]);

        // check FAILURE
        $statusCode = $this->applicationTester->getStatusCode();
        $this->assertEquals($statusCode, 1, 'Output option validation not working.');

        // check error message
        $display = $this->applicationTester->getDisplay();
        $this->assertEquals(
            $display,
            "Invalid output option. Valid output ends with .json\n",
            'Output option validation didn\'t return correct error message.'
        );
    }

    public function testAllValidOptions(): void
    {
        $this->applicationTester->run([
            '--limit' => 1,
            '--orderBy' => 'dependents',
            '--order' => 'asc',
            '--output' => 'tests/output_file.json'
        ]);

        // check SUCCESS
        $this->applicationTester->assertCommandIsSuccessful('Something went wrong!!');
    }
}