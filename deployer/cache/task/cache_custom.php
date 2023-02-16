<?php

namespace Deployer;

use Throwable;

// Read more on https://github.com/sourcebroker/deployer-extended#cache-clear-php-cli
task('cache:clear', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    if ($currentHostAlias == 'development') {
        writeln('Running development');
        // WP CORE Cache object
        try {
            // WP CORE Cache object
            writeln('<comment>Running wp cache flush</comment>');
            runLocally('wp cache flush');
        } catch (Throwable $e) {
            writeln('<error>wp cache flush could not run</error>');
        }
        // Check if Elementor is active
        try {
            runLocally('wp plugin is-active elementor');
            $is_elementor_active = runLocally('echo $?');
            if ($is_elementor_active == 0) {
                // Plugin is active
                writeln('<comment>Running wp elementor flush-css</comment>');
                // Run wp elementor flush-css
                runLocally('wp elementor flush-css');
            }
        } catch (Throwable $e) {
            writeln('<error>Elementor is not available - Ignoring command</error>');
        }
        writeln('<info>Finished the Task</info>');
    } else {
        // Running remotely - Must use run()
        writeln('Running stagin/production');
        invoke('cache:clear:litespeed');
        invoke('cache:clear:autoptimize');
        invoke('cache:clear:w3totalcache');
        invoke('cache:clear:wpcore');
        invoke('cache:clear:elementorcss');
        writeln('<info>Finished the Task</info>');
    }
})->desc('Clearing the cache for Litespeed Cache, Autoptimize, W3 Total Cache, WP Core, and flushes the css for Elementor');


task('cache:clear:litespeed', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    try {
        runLocally('wp @' . $currentHostAlias . ' plugin is-active litespeed-cache');
        $is_litespeed_cache_active = runLocally('echo $?');
        if ($is_litespeed_cache_active == 0) {
            // Plugin is active
            writeln('<comment>Running wp litespeed-purge all</comment>');
            // Run wp litespeed-purge all
            runLocally('wp @' . $currentHostAlias . ' litespeed-purge all');
        }
    } catch (Throwable $e) {
        writeln('<error>Litespeed Cache is not available - Ignoring command</error>');
    }
})->desc('Clearing the cache for Litespeed Cache');


task('cache:clear:autoptimize', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    try {
        // Check if Autoptimize is active
        runLocally('wp @' . $currentHostAlias . ' plugin is-active autoptimize');
        $is_autoptimize_active = runLocally('echo $?');
        if ($is_autoptimize_active == 0) {
            // Plugin is active
            writeln('<comment>Running wp autoptimize clear</comment>');
            // Run wp autoptimize clear
            runLocally('wp @' . $currentHostAlias . ' autoptimize clear');
        }
    } catch (Throwable $e) {
        writeln('<error>Autoptimize is not available - Ignoring command</error>');
    }
})->desc('Clearing the cache for Autoptimize');


task('cache:clear:w3totalcache', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    try {
        // Check if W3 Total Cache is active
        run('wp @' . $currentHostAlias . ' plugin is-active w3-total-cache');
        $is_w3_cache_active = run('echo $?');
        if ($is_w3_cache_active == 0) {
            // Plugin is active
            writeln('<comment>Running wp w3-total-cache flush all</comment>');
            // Run wp w3-total-cache flush
            run('wp @' . $currentHostAlias . ' w3-total-cache flush all');
        }
    } catch (Throwable $e) {
        writeln('<error>W3 Total Cache is not availbale - Ignoring command</error>');
    }
})->desc('Clearing the cache for W3 Total Cache');


task('cache:clear:wpcore', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    try {
        // WP CORE Cache object
        writeln('<comment>Running wp cache flush</comment>');
        runLocally('wp @' . $currentHostAlias . ' cache flush');
    } catch (Throwable $e) {
        writeln('<error>wp cache flush could not run</error>');
    }
})->desc('Clearing the cache for Wordpress Core');


task('cache:clear:elementorcss', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();
    try {
        runLocally('wp @' . $currentHostAlias . ' plugin is-active elementor');
        $is_elementor_active = runLocally('echo $?');
        if ($is_elementor_active == 0) {
            // Plugin is active
            writeln('<comment>Running wp elementor flush-css</comment>');
            // Run wp elementor flush-css
            runLocally('wp @' . $currentHostAlias . ' elementor flush-css');
        }
    } catch (Throwable $e) {
        writeln('<error>Elementor is not available - Ignoring command</error>');
    }
})->desc('Flushing the css for Elementor');
