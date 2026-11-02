<?php

use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$files = [$root.'/importmap.php', $root.'/.php-cs-fixer.dist.php'];

foreach (['src', 'tests', 'migrations', 'config', 'bin', 'public', 'tools'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (str_starts_with($relative, 'config/secrets/') || str_starts_with($relative, 'public/assets/') || str_starts_with($relative, 'public/bundles/')) {
            continue;
        }
        if ($file->getExtension() === 'php' || in_array($relative, ['bin/console', 'bin/phpunit'], true)) {
            $files[] = $file->getPathname();
        }
    }
}

foreach ($files as $file) {
    $process = new Process([PHP_BINARY, '-l', $file]);
    $process->run();
    if (!$process->isSuccessful()) {
        fwrite(STDERR, $process->getOutput().$process->getErrorOutput());
        exit(1);
    }
}

printf("Syntaxe PHP valide : %d fichiers.\n", count($files));
