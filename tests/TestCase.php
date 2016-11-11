<?php

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Symfony\Component\Finder\Finder;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpStorage();
    }

    private function setUpDatabase()
    {
        config(['database.default' => 'testing']);
    }

    private function setUpStorage()
    {
        $this->app->useStoragePath(__DIR__ . '/fixture/storage');
    }

    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(KernelContract::class, Kernel::class);
    }

    protected function tearDown()
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

    public function assertLogFileExists($path)
    {
        $this->assertFileExists(storage_path("logs/{$path}"));
    }
}