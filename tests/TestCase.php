<?php

namespace Illuminated\Console\Tests;

use Mockery;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Illuminated\Testing\TestingTools;
use Illuminated\Console\Tests\App\Console\Kernel;
use Illuminate\Contracts\Console\Kernel as KernelContract;

Mockery::globalHelpers();

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use TestingTools;

    public $mockConsoleOutput = false;

    protected $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDate();
        $this->setUpDatabase();
        $this->setUpSendmail();
        $this->setUpStorage();
    }

    private function setUpDate()
    {
        $this->date = date('Y-m-d');
    }

    protected function setUpDatabase()
    {
        config(['database.default' => 'testing']);
    }

    protected function setUpSendmail()
    {
        config(['mail.sendmail' => '/usr/sbin/sendmail -bs']);
    }

    private function setUpStorage()
    {
        $this->app->useStoragePath(__DIR__ . '/fixture/storage');
    }

    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(KernelContract::class, Kernel::class);

        app(KernelContract::class);
    }

    protected function tearDown(): void
    {
        $this->cleanLogsDirectory();

        parent::tearDown();
    }

    private function cleanLogsDirectory()
    {
        $objects = (new Finder)->in(storage_path('logs'))->depth(0);
        foreach ($objects as $object) {
            if (File::isDirectory($object)) {
                File::deleteDirectory($object);
            } else {
                File::delete($object);
            }
        }
    }
}
