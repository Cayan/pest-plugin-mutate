<?php

declare(strict_types=1);

use Pest\Mutate\Support\StreamWrapper;

beforeEach(function (): void {
    $this->directory = sys_get_temp_dir().'/pest-mutate-stream-wrapper-'.uniqid();
    mkdir($this->directory);

    $this->original = $this->directory.'/Original.php';
    $this->override = $this->directory.'/Override.php';
    file_put_contents($this->original, "<?php\n");
    file_put_contents($this->override, "<?php\n");

    StreamWrapper::start($this->original, $this->override);
});

afterEach(function (): void {
    StreamWrapper::disable();

    foreach (glob($this->directory.'/*') as $file) {
        chmod($file, 0o644);
        unlink($file);
    }

    rmdir($this->directory);
});

it('reports an unreadable file as an existing file', function (): void {
    $locked = $this->directory.'/locked.log';
    file_put_contents($locked, 'content');
    chmod($locked, 0o000);

    expect([file_exists($locked), is_file($locked), filesize($locked)])
        ->toBe([true, true, 7]);
})->skip(fn (): bool => function_exists('posix_geteuid') && posix_geteuid() === 0, 'File permissions are not enforced for the root user.');

it('reports a missing file as missing without a warning', function (): void {
    expect([file_exists($this->directory.'/missing.log'), is_file($this->directory.'/missing.log')])
        ->toBe([false, false]);
});
