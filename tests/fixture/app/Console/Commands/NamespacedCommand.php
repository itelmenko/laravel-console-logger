<?php

namespace Illuminated\Console\Tests\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminated\Console\Loggable;

class NamespacedCommand extends Command
{
    use Loggable;

    protected $signature = 'namespaced:command';

    public function handle()
    {
        $this->logInfo('Done!');
    }
}
