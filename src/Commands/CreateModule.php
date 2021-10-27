<?php

declare(strict_types=1);

namespace SebastiaanLuca\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:create
                            {name : The studly cased name of the module}
                            {--D|directory= : The directory in the root project to create the module in}
                            {--K|keep : Keep existing module autoload entries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() : void
    {
        $name = Str::studly($this->argument('name'));

        $moduleDir = $this->option('directory') ?? head(config('module-loader.directories'));

        if (! $moduleDir) {
            $this->error('No module directories configured nor any explicitly given!');

            return;
        }

        $path = base_path(sprintf(
            '%s/%s/src/Providers',
            $moduleDir,
            $name
        ));

        if (is_dir($path)) {
            $this->error(sprintf(
                '%s module already exists!',
                $name
            ));

            return;
        }

        mkdir($path, 0777, true);

        $provider = file_get_contents(__DIR__ . '/../../resources/stubs/DummyServiceProvider.php');

        $provider = str_replace('Dummy', $name, $provider);

        $target = sprintf('%s/%sServiceProvider.php', $path, $name);
        
        File::ensureDirectoryExists(dirname($target));

        file_put_contents($target, $provider);

        $this->info(sprintf(
            '%s module created!',
            $name
        ));

        if (! config('module-loader.runtime_autoloading')) {
            $this->call('modules:refresh', [
                '--keep' => $this->option('keep'),
            ]);
        }
    }
}
