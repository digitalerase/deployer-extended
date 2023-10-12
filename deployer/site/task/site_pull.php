<?php

namespace Deployer;

use Throwable;

task('site:pull', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    if ($currentHostAlias == 'staging') {
        writeln('Running site:pull from staging');
        writeln('Running db:pull');
        invoke('db:pull');
        writeln('Running media:pull');
        invoke('media:pull');
        writeln('<info>Finished the Task</info>');
    } elseif ($currentHostAlias == 'production') {
        writeln('Running site:pull from production');
        writeln('Running db:pull');
        invoke('db:pull');
        writeln('Running media:pull');
        invoke('media:pull');
        writeln('<info>Finished the Task</info>');
    } else {
        writeln('<error>site:pull can only be run on staging or production</error>');
    }
    
})->desc('Pull site from remote instance (Including database and media-files).');