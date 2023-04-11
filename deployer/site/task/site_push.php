<?php

namespace Deployer;

use Throwable;

task('site:push', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    if ($currentHostAlias == 'staging') {
        writeln('Running site:push to staging');
        writeln('Running deploy');
        invoke('deploy');
        writeln('Running db:push');
        invoke('db:push');
        writeln('Running media:push');
        invoke('media:push');
        writeln('<info>Finished the Task</info>');
    } elseif ($currentHostAlias == 'production') {
        writeln('Running site:push to production');
        writeln('Running deploy');
        invoke('deploy');
        writeln('Running db:copy');
        invoke('db:copy staging --options=target:production');
        writeln('Running media:copy');
        invoke('dep media:copy staging --options=target:production');
        writeln('<info>Finished the Task</info>');
    } else {
        writeln('<error>site:push can only be run on staging or production</error>');
    }
    
})->desc('Push site to remote instance (Including database and media-files).');