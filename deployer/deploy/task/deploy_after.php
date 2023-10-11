<?php

namespace Deployer;

use Throwable;

task('deploy:after_deploy', function () {
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();

    // If the current host is staging
    if ($currentHostAlias == 'staging') {

        // ######## Running after deploy staging ########

        // writeln('Running after_deploy staging');

        cd('{{deploy_path}}');
        $remote_base_path = run('pwd');

        // Download all files
        $remote_shared_folder = $remote_base_path . '/shared';

        // writeln('Downloading htpasswd and htaccess files');

        // Setting the local root path from /vendors/digitalerase/deployer-extended/deployer/deploy/task
        // Note: This is the same level as deploy.php (Project root folder)
        $local_root_path = dirname(__FILE__) . '/../../../../../..';

        // Check if the downloads folder exists, create it if not
        $downloads_folder = $local_root_path . '/deploy-files/downloads';
        if (!is_dir($downloads_folder)) {
            // Recursively create the downloads folder
            mkdir($downloads_folder, 0777, true);
        }

        // Check if the templates folder exists, create it if not
        $templates_folder = $local_root_path . '/deploy-files/templates';
        if (!is_dir($templates_folder)) {
            // Recursively create the templates folder
            mkdir($templates_folder, 0777, true);
        }

        // Download htpasswd file
        writeln('Downloading htpasswd file');
        try {
            download($remote_shared_folder . '/web/.htpasswd', $local_root_path . '/deploy-files/downloads/.htpasswd');
        } catch (\Throwable $th) {
            warning('Downloading htpasswd file failed');
        }

        // Download htaccess file
        writeln('Downloading htaccess file');
        try {
            download($remote_shared_folder . '/web/.htaccess', $local_root_path . '/deploy-files/downloads/.htaccess');
        } catch (\Throwable $th) {
            warning('Downloading htaccess file failed');
        }

        // Download wordfence-waf.php file
        writeln('Downloading wordfence-waf.php file');
        try {
            download($remote_shared_folder . '/web/wp/wordfence-waf.php', $local_root_path . '/deploy-files/downloads/wordfence-waf.php');
        } catch (\Throwable $th) {
            warning('Downloading wordfence-waf.php file failed');
        }
        // Check if .user.ini and wordfence-waf.php files exist
        // $user_ini_file_exists = file_exists($local_root_path . '/deploy-files/downloads/.user.ini');
        
        // Check if htpasswd and htaccess files exists
        $htpasswd_file_exists = file_exists($local_root_path . '/deploy-files/downloads/.htpasswd');
        $htaccess_file_exists = file_exists($local_root_path . '/deploy-files/downloads/.htaccess');
        $wordfence_waf_file_exists = file_exists($local_root_path . '/deploy-files/downloads/wordfence-waf.php');

        writeln('Checking files');

        if ($htpasswd_file_exists) {
            // File exists
            $local_htpasswd_file = $local_root_path . '/deploy-files/downloads/.htpasswd';
            $htpasswd_file_contents = file_get_contents($local_htpasswd_file);
            preg_match_all("/digitalera/", $htpasswd_file_contents, $matches);
            if (count($matches[0]) > 0) {
                writeln('<info>htpasswd file contains digitalera, do nothing</info>');
            } else {
                writeln('htpasswd file does not contain digitalera');
                // Append digitalera to htpasswd file
                $new_row = 'digitalera:$apr1$tnd0zux5$JquYi59V9X/q6m/39pBny0';
                writeln('Appending digitalera to htpasswd file');
                $res = file_put_contents($local_htpasswd_file, "\n" . $new_row, FILE_APPEND);
                // Upload htpasswd file from deploy-files > downloads to staging
                writeln('uploading updated htpasswd file');
                upload($local_htpasswd_file, $remote_shared_folder . '/web/.htpasswd');
            }
        } else {
            // File does not exist
            writeln('htpasswd file does not exist');
            writeln('uploading the template htpasswd file');
            // Check if the template file exists
            $template_htpasswd_file_exists = file_exists($local_root_path . '/deploy-files/templates/.htpasswd');
            if ($template_htpasswd_file_exists) {
                // File exists
                upload($local_root_path . '/deploy-files/templates/.htpasswd', $remote_shared_folder . '/web/.htpasswd');
            } else {
                // File does not exist
                writeln('template htpasswd file does not exist');
            }
            // upload($local_root_path . '/deploy-files/templates/.htpasswd', $remote_shared_folder . '/web/.htpasswd');
        }

        if ($htaccess_file_exists) {
            // File exists
            $local_htaccess_file = $local_root_path . '/deploy-files/downloads/.htaccess';
            $htaccess_file_contents = file_get_contents($local_htaccess_file);
            preg_match_all("/\#\sBEGIN\sCustom\sAuth\s\.htaccess/", $htaccess_file_contents, $matches);
            if (count($matches[0]) > 0) {
                writeln('<info>htaccess file contains Custom Auth .htaccess, do nothing</info>');
            } else {
                writeln('htaccess file does not contain Custom Auth .htaccess');
                // Append Custom Auth .htaccess to htaccess file
                $new_content = <<<EOF
                # BEGIN Custom Auth .htaccess
                AuthType Basic
                AuthName "Restricted Content"
                AuthUserFile $remote_shared_folder/web/.htpasswd
                Require valid-user
                # END Custom Auth .htaccess
                EOF;
                writeln('Appending Custom Auth .htaccess to htaccess file');
                $res = file_put_contents($local_htaccess_file, "\n\n" . $new_content, FILE_APPEND);
                // Upload htaccess file from deploy-files > downloads to staging
                writeln('uploading updated htaccess file');
                upload($local_htaccess_file, $remote_shared_folder . '/web/.htaccess');
            }
        } else {
            // File does not exist
            writeln('htaccess file does not exist');
            writeln('uploading the template htaccess file');
            // Check if the template file exists
            $template_htaccess_file_exists = file_exists($local_root_path . '/deploy-files/templates/.htaccess');
            if ($template_htaccess_file_exists) {
                // File exists
                upload($local_root_path . '/deploy-files/templates/.htaccess', $remote_shared_folder . '/web/.htaccess');
            } else {
                // File does not exist
                writeln('template htaccess file does not exist');
            }
            // upload($local_root_path . '/deploy-files/templates/.htaccess', $remote_shared_folder . '/web/.htaccess');
        }

        
        if ( $wordfence_waf_file_exists ) {
            // File exists
            writeln('<info>The wordfence-waf.php file exists, do nothing</info>');
        } else {
            // File does not exist
            writeln('The wordfence-waf.php file does not exist');
            writeln('uploading the template wordfence-waf.php file');
            // Check if the template file exists
            $template_wordfence_waf_file_exists = file_exists($local_root_path . '/deploy-files/templates/wordfence-waf.php');
            if ($template_wordfence_waf_file_exists) {
                // Local file exists
                // Check if remote wp folder exists
                try {
                    upload($local_root_path . '/deploy-files/templates/wordfence-waf.php', $remote_shared_folder . '/web/wp/wordfence-waf.php');
                } catch (\Throwable $th) {
                    // Failed to upload file
                    warning('Failed to upload wordfence-waf.php file');
                    // Try to create wp folder
                    writeln('Trying to create wp folder');
                    cd($remote_shared_folder . '/web');
                    run('mkdir wp');

                    // Retry upload
                    writeln('Retrying to upload wordfence-waf.php file');
                    try {
                        upload($local_root_path . '/deploy-files/templates/wordfence-waf.php', $remote_shared_folder . '/web/wp/wordfence-waf.php');
                    } catch (\Throwable $th) {
                        // Failed to upload file
                        warning('Failed 2nd attempt to upload wordfence-waf.php file');
                    }
                }
            } else {
                // File does not exist
                writeln('template wordfence-waf.php file does not exist');
            }
        }

        // Remove files from downloads
        if ($htpasswd_file_exists) {
            unlink($local_root_path . '/deploy-files/downloads/.htpasswd');
        }
        if ($htaccess_file_exists) {
            unlink($local_root_path . '/deploy-files/downloads/.htaccess');
        }
        if ($wordfence_waf_file_exists) {
            unlink($local_root_path . '/deploy-files/downloads/wordfence-waf.php');
        }

        // info('Done running after_deploy staging');
    } elseif ($currentHostAlias == 'production') {

        // ######## Running after deploy production ########

        // writeln('Running after_deploy staging');

        cd('{{deploy_path}}');

        $remote_base_path = run('pwd');

        // Download all files
        $remote_shared_folder = $remote_base_path . '/shared';

        // writeln('Downloading .user.ini and wordfence-waf.php files');

        // Setting the local root path from /vendors/digitalerase/deployer-extended/deployer/deploy/task
        // Note: This is the same level as deploy.php (Project root folder)
        $local_root_path = dirname(__FILE__) . '/../../../../../..';

        // Check if the downloads folder exists, create it if not
        $downloads_folder = $local_root_path . '/deploy-files/downloads';
        if (!is_dir($downloads_folder)) {
            // Recursively create the downloads folder
            mkdir($downloads_folder, 0777, true);
        }

        // Check if the templates folder exists, create it if not
        $templates_folder = $local_root_path . '/deploy-files/templates';
        if (!is_dir($templates_folder)) {
            // Recursively create the templates folder
            mkdir($templates_folder, 0777, true);
        }

        // Download .user.ini file
        writeln('Downloading .user.ini file');
        try {
            download($remote_shared_folder . '/web/.user.ini', $local_root_path . '/deploy-files/downloads/.user.ini');
        } catch (\Throwable $th) {
            warning('Downloading .user.ini file failed');
        }

        // Download wordfence-waf.php file
        writeln('Downloading wordfence-waf.php file');
        try {
            download($remote_shared_folder . '/web/wp/wordfence-waf.php', $local_root_path . '/deploy-files/downloads/wordfence-waf.php');
        } catch (\Throwable $th) {
            warning('Downloading wordfence-waf.php file failed');
        }

        // Check if .user.ini and wordfence-waf.php files exist
        $user_ini_file_exists = file_exists($local_root_path . '/deploy-files/downloads/.user.ini');
        $wordfence_waf_file_exists = file_exists($local_root_path . '/deploy-files/downloads/wordfence-waf.php');

        writeln('Checking files');

        if ( $user_ini_file_exists ) {
            // File exists

            // Check if the .user.ini points the wordfence-waf.php file to the shared wp folder
            $local_user_ini_file = $local_root_path . '/deploy-files/downloads/.user.ini';
            $user_ini_content = file_get_contents($local_user_ini_file);

            preg_match_all("/shared\/web\/wp\/wordfence-waf\.php/", $user_ini_content, $matches);
            if (count($matches[0]) > 0) {
                // The .user.ini points the wordfence-waf.php file to the shared wp folder
                writeln('<info>The .user.ini points the wordfence-waf.php file to the shared wp folder, do nothing</info>');
            } else {
                // The .user.ini does not point the wordfence-waf.php file to the shared wp folder
                writeln('The .user.ini does not point the wordfence-waf.php file to the shared wp folder');
                $new_content = <<<EOF
                ; Wordfence WAF
                auto_prepend_file = '$remote_shared_folder/web/wp/wordfence-waf.php'
                ; END Wordfence WAF
                EOF;
                writeln('Appending new content to .user.ini file');
                $res = file_put_contents($local_user_ini_file, "\n\n" . $new_content, FILE_APPEND);
                // Upload htaccess file from deploy-files > downloads to staging
                writeln('uploading updated .user.ini file');
                upload($local_user_ini_file, $remote_shared_folder . '/web/.user.ini');
            }
            // shared\/web\/wp\/wordfence-waf\.php
        } else {
            // File does not exist
            writeln('The .user.ini file does not exist');
            writeln('uploading the template .user.ini file');
            // Check if the template file exists
            $template_user_ini_file_exists = file_exists($local_root_path . '/deploy-files/templates/.user.ini');
            if ($template_user_ini_file_exists) {
                // File exists
                // Check if remote wp folder exists
                try {
                    upload($local_root_path . '/deploy-files/templates/.user.ini', $remote_shared_folder . '/web/.user.ini');
                } catch (\Throwable $th) {
                    // Failed to upload file
                    warning('Failed to upload .user.ini file');
                    // Try to create wp folder
                    // writeln('Trying to create wp folder');
                    // cd($remote_shared_folder . '/web');
                    // run('mkdir wp');

                    // // Retry upload
                    // writeln('Retrying to upload .user.ini file');
                    // try {
                    //     upload($local_root_path . '/deploy-files/templates/.user.ini', $remote_shared_folder . '/web/.user.ini');
                    // } catch (\Throwable $th) {
                    //     // Failed to upload file
                    //     warning('Failed 2nd attempt to upload .user.ini file');
                    // }
                }
                // upload($local_root_path . '/deploy-files/templates/.user.ini', $remote_shared_folder . '/web/.user.ini');
            } else {
                // File does not exist
                writeln('template .user.ini file does not exist');
            }
        }

        if ( $wordfence_waf_file_exists ) {
            // File exists
            writeln('<info>The wordfence-waf.php file exists, do nothing</info>');
        } else {
            // File does not exist
            writeln('The wordfence-waf.php file does not exist');
            writeln('uploading the template wordfence-waf.php file');
            // Check if the template file exists
            $template_wordfence_waf_file_exists = file_exists($local_root_path . '/deploy-files/templates/wordfence-waf.php');
            if ($template_wordfence_waf_file_exists) {
                // File exists
                // Check if remote wp folder exists
                try {
                    upload($local_root_path . '/deploy-files/templates/wordfence-waf.php', $remote_shared_folder . '/web/wp/wordfence-waf.php');
                } catch (\Throwable $th) {
                    // Failed to upload file
                    warning('Failed to upload wordfence-waf.php file');
                    // Try to create wp folder
                    writeln('Trying to create wp folder');
                    cd($remote_shared_folder . '/web');
                    run('mkdir wp');

                    // Retry upload
                    writeln('Retrying to upload wordfence-waf.php file');
                    try {
                        upload($local_root_path . '/deploy-files/templates/wordfence-waf.php', $remote_shared_folder . '/web/wp/wordfence-waf.php');
                    } catch (\Throwable $th) {
                        // Failed to upload file
                        warning('Failed 2nd attempt to upload wordfence-waf.php file');
                    }
                }
                // upload($local_root_path . '/deploy-files/templates/wordfence-waf.php', $remote_shared_folder . '/web/wp/wordfence-waf.php');
            } else {
                // File does not exist
                writeln('template wordfence-waf.php file does not exist');
            }
        }

        // Remove files from downloads
        if ( $user_ini_file_exists ) {
            unlink($local_root_path . '/deploy-files/downloads/.user.ini');
        }
        if ( $wordfence_waf_file_exists ) {
            unlink($local_root_path . '/deploy-files/downloads/wordfence-waf.php');
        }

        info('Done running after_deploy production');
    }

})->desc('Task that is run after a deploy');

// Run the deploy:after_deploy task after the deploy
after('deploy', 'deploy:after_deploy');

task('custom:upload_env', function () {
    // writeln('Running custom:upload_env');
    $currentHost = currentHost();
    $currentHostAlias = $currentHost->getAlias();

    cd('{{deploy_path}}');
    $remote_base_path = run('pwd');

    // Remote shared folder
    $remote_shared_folder = $remote_base_path . '/shared';

    // Setting the local root path from /vendors/digitalerase/deployer-extended/deployer/deploy/task
    // Note: This is the same level as deploy.php (Project root folder)
    $local_root_path = dirname(__FILE__) . '/../../../../../..';

    // Check if the downloads folder exists, create it if not
    $downloads_folder = $local_root_path . '/deploy-files/downloads';
    if (!is_dir($downloads_folder)) {
        // Recursively create the downloads folder
        mkdir($downloads_folder, 0777, true);
    }

    $remote_env_file = $remote_shared_folder . '/.env';

    // Download .env file
    writeln('Downloading .env file');
    try {
        download($remote_env_file, $local_root_path . '/deploy-files/downloads/.env');
    } catch (\Throwable $th) {
        warning('Downloading .env file failed');
    }

    // Check if .env file exists
    $env_file_exists = file_exists($local_root_path . '/deploy-files/downloads/.env');

    $file_size = filesize($local_root_path . '/deploy-files/downloads/.env');

    writeln('Checking .env file');

    if ( $env_file_exists && $file_size > 0 ) {
        // File exists and is not empty, do nothing
        writeln('<info>The .env file exists and is not empty, do nothing</info>');
    } else {
        // File does not exist, upload it (.env.staging or .env.production)
        if ($currentHostAlias === 'staging') {
            // ######## Running on staging ########
            // Check if the local .env file exists
            $staging_env_file_exists = file_exists($local_root_path . '/.env.staging');
            if ($staging_env_file_exists) {
                // File exists, upload it
                writeln('Local .env file (.env.staging) uploaded');
                upload($local_root_path . '/.env.staging', $remote_env_file);
            } else {
                // Local env file does not exist
                writeln('Local .env file does not exist (.env.staging)');
            }
        } elseif ($currentHostAlias === 'production') {
            // ######## Running on production ########
            // Check if the local .env file exists
            $production_env_file_exists = file_exists($local_root_path . '/.env.production');
            if ($production_env_file_exists) {
                // File exists, upload it
                writeln('Local .env file (.env.production) uploaded');
                upload($local_root_path . '/.env.production', $remote_env_file);
            } else {
                // Local env file does not exist
                writeln('Local .env file does not exist (.env.production)');
            }
        }
    }

    // Remove files from downloads
    if ( $env_file_exists ) {
        unlink($local_root_path . '/deploy-files/downloads/.env');
    }

    // info('Done custom:upload_env');
});

after('deploy:vendors', 'custom:upload_env');