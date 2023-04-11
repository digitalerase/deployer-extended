<?php

namespace Deployer;

use Throwable;

task('site:push', function () {
    writeln('Running site:push');
    // $is_failed = false;
    // try {
    //     invoke('deploy');
    // } catch (Throwable $th) {
    //     $is_failed = true;
    //     writeln($th->getMessage());
    //     writeln('<error>The command deploy failed</error>');
    // }
    // if (!$is_failed) {
    //     try {
    //         invoke('db:push');
    //     } catch (Throwable $th) {
    //         $is_failed = true;
    //         writeln($th->getMessage());
    //         writeln('<error>The command db:push failed</error>');
    //     }
    // }
    // if (!$is_failed) {
    //     try {
    //         invoke('media:push');
    //     } catch (Throwable $th) {
    //         $is_failed = true;
    //         writeln($th->getMessage());
    //         writeln('<error>The command media:push failed</error>');
    //     }
    // }
    invoke('deploy');
    invoke('db:push');
    invoke('media:push');
    writeln('<info>Finished the Task</info>');
})->desc('Push site to remote instance (Including database and media-files).');