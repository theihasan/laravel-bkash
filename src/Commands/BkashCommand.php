<?php

namespace Ihasan\Bkash\Commands;

use Illuminate\Console\Command;

class BkashCommand extends Command
{
    public $signature = 'laravel-bkash';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
