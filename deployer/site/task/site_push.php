<?php

namespace Deployer;

use Throwable;

task('site:push', function () {
    writeln('Running site:push');
    invoke('deploy');
    invoke('db:push');
    invoke('media:push');
    writeln('<info>Finished the Task</info>');
})->desc('Push site to remote instance (Including database and media-files).');