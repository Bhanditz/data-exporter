<?php

	chdir( dirname( __DIR__ ) );
	include 'bootstrap.php';

	$job = array();
	$job_path = realpath( __DIR__ . '/../cli-jobs/' ) . '/';
	$output_path = realpath( __DIR__ . '/../cli-output/' ) . '/';
	$wskey = '';

	if ( isset( $config['wskey'] ) ) {
		$wskey = filter_var( $config['wskey'], FILTER_SANITIZE_STRING );
	}

	try {

		do {

			$job = App\Helpers\Jobs::retrieveJob(
				array(
					'filename' => $config['dataset-jobs'],
					'path' => $job_path
				)
			);

			if ( empty( $job ) ) {
				break;
			}

			$job = App\Helpers\Jobs::processJob(
				$job,
				array(
					'job-run-limit' => $config['job-run-limit'],
					'output-path' => $output_path,
					'wskey' => $wskey
				)
			);

		} while( false );

	} catch ( Exception $e ) {
		error_log( $e->getMessage() );
	}


	if ( !empty( $job['items'] ) ) {
		App\Helpers\Jobs::addJobToFile(
			$job,
			array(
				'filename' => $config['dataset-jobs'],
				'path' => $job_path
			)
		);
	} elseif ( !empty( $job['errors'] ) ) {
		App\Helpers\Jobs::addJobToFile(
			$job,
			array(
				'filename' => $config['dataset-errors'],
				'path' => $job_path
			)
		);
	}