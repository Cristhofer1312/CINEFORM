<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class setCrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setCrypt {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XXX';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $value = $this->argument('value');
        $this->warn(__("Encrypted Key").": ". \App\Helpers\LockDB::encrypt($value));
    }
}
