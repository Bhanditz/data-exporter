<?php

	use \Pennline\Html\Script;

	/**
	 * set-up page
	 */
	header( 'Content-Type: ' . $Config->content_type . '; charset=' . $Config->charset );

	$WebPage->page = 'my-europeana/tag-list/results';
	$WebPage->title = 'Results - Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->heading = 'Results - Tag List, My Europeana: ' . $Config->site_name;
	$WebPage->view = 'html-layout.tpl.php';

	if ( isset( $_SERVER['PHP_ENV'] ) && $_SERVER['PHP_ENV'] === 'development'  ) {
		$WebPage->addScript( new Script( array( 'src' => '/js/prettify.js' ) ) );
	} else {
		$WebPage->addScript( new Script( array( 'content' => file_get_contents( 'public/js/prettify.min.js' ) ) ) );
	}

	$WebPage->addScript( new Script( array( 'content' => 'prettyPrint();' ) ) );


	/**
	 * set-up variables
	 */
	$debug = false;
	$email = '';
	$empty_result = '<pre class="prettyprint">[{}]</pre>';
	$europeanaid = '';
	$form_feedback = '';
	$html_result = '';
	$j_username = '';
	$j_password = '';
	$login_request_options = array();
	$login_result = '';
	$start = 1;
	$tag = '';
	$tag_request_options = array();
	$tag_result = '';


	/**
	 * set-up csrf
	 */
	$Csrf = new Pennline\Owasp\Csrf( array( 'Session' => $Session, 'token-key-obfuscate' => true ) );


	/**
	 * check for a posted form
	 */
	try {

		do {

			// check for a post
			if ( empty( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


			// check for cookie
			if ( !$Session->cookiePresent() ) {
				$html_result .= '<ul><li><span class="error">In order to use this form, your browser must accept cookies for this site.</span></li><li><a href="https://support.google.com/websearch/answer/35851?hl=en" target="_external">Enable cookies</a> for this site and then return to <a href="/my-europeana/tag-list-search">the tag list search form</a>.</li></ul>';
				$html_result .= $empty_result;
				break;
			}


			// check for token
			if ( !$Csrf->isTokenValid( $_POST ) ) {
				$html_result .= $empty_result;
				break;
			}


			// get login params
			if ( isset( $_POST['public-api-key'] ) ) {
				$j_username = filter_var( $_POST['public-api-key'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['public-api-key'] ) ) {
				$j_password = filter_var( $_POST['private-api-key'], FILTER_SANITIZE_STRING );
			}


			// get regular form params
			if ( isset( $_POST['debug'] ) && $_POST['debug'] === 'true' ) {
				$debug = true;
			}

			if ( isset( $_POST['europeanaid'] ) ) {
				$europeanaid = filter_var( $_POST['europeanaid'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['tag'] ) ) {
				$tag = filter_var( $_POST['tag'], FILTER_SANITIZE_STRING );
			}

			if ( isset( $_POST['email'] ) ) {
				$email = filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL );
			}


			// verify required fields
			if ( empty( $j_username ) ) {
				$form_feedback .= '<li class="error">please provide your valid public api key</li>';
			}

			if ( empty( $j_password ) ) {
				$form_feedback .= '<li class="error">please provide your valid private api key</li>';
			}

			if ( empty( $email ) ) {
				$form_feedback .= '<li class="error">please provide your valid email address</li>';
			}

			if ( !empty( $form_feedback ) ) {
				$html_result .= '<ul id="form-feedback">' . $form_feedback. '</ul>';
				break;
			}


			// setup curl
			$Curl = new Pennline\Php\Curl( array( 'curl-followlocation' => true ) ); // because of 302 Moved Temporarily response from login.do
			$Curl->setHttpHeader( array( 'Accept: application/json' ) );


			// make the login call
			$login_request_options = array(
				'j_username' => $j_username,
				'j_password' => $j_password,
				'RequestService' => $Curl
			);

			$LoginRequest = new Europeana\Api\Request\MyEuropeana\Login( $login_request_options );
			$LoginResponse = new Europeana\Api\Response\Login( $LoginRequest->call() );


			// output curl info & response
			if ( $debug ) {
				$login_result .= '<h3>login cURL info</h3>';
				$login_result .= '<pre class="prettyprint">' . print_r( $LoginResponse->http_info, true ) . '</pre>';

				$login_result .= '<h3>login response body</h3>';
				$login_result .= '<pre class="prettyprint">' . $LoginResponse->getResponseAsJson() . '</pre>';
			}


			// setup tag request
			$tag_request_options = array(
				'europeanaid' => $europeanaid,
				'RequestService' => $Curl,
				'tag' => $tag
			);


			// make the tag call
			$TagRequest = new Europeana\Api\Request\MyEuropeana\Tag( $tag_request_options );
			$TagResponse = new Europeana\Api\Response\Tag( $TagRequest->call(), $j_username );


			// process the response
			if ( $TagResponse->totalResults > $Config->jobs->max_allowed ) {

				$html_result .=
					sprintf(
						'<h2 class="page-header">batch job</h2><p>the total result set of <b>%s</b> items exceeds the maximum job limit of <b>%s</b> items. you need to narrow down the result set in order to create a batch job.</p>',
						number_format( $TagResponse->totalResults ),
						number_format( $Config->jobs->max_allowed )
					);

					$html_result .= Europeana\Api\Helpers\Response::getResponseImagesWithLinks( $TagResponse );

			} elseif ( $TagResponse->totalResults > 0 ) {

				// add batch job form
				$html_result .= include 'my-europeana/tag-list/create-batch-job.form.php';
				$html_result .= Europeana\Api\Helpers\Response::getResponseImagesWithLinks( $TagResponse );

			} else {

				$html_result .= '<h3>sample result set</h3>';
				$html_result .= '<p>no tags found</p>';

			}


			// output curl info & response
			if ( $debug ) {
				$tag_result .= '<h3>tag cURL info</h3>';
				$tag_result .= '<pre class="prettyprint">' . print_r( $TagResponse->http_info, true ) . '</pre>';

				$tag_result .= '<h3>tag response body</h3>';
				$tag_result .= '<pre class="prettyprint">' . $TagResponse->getResponseAsJson() . '</pre>';
			}


			// finalize html output
			$html_result .= $login_result . $tag_result;

		} while( false );

	} catch( Exception $e ) {

		$msg = '<p class="error">%s</p>';
		$parts = explode( 'Array', $e->getMessage(), 2 );

		if ( count( $parts ) === 2 ) {
			$html_result .= sprintf( $msg, nl2br( $parts[0] ) );
			$html_result .= '<pre class="prettyprint">' . $parts[1] . '</pre>';
		} else {
			$html_result .= sprintf( $msg, $e->getMessage() );
		}

	}


	/**
	 * set-up page view
	 */
	$WebPage->html = $html_result;
	include $WebPage->view;
