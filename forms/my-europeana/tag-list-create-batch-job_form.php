<?php

	return
	'<h3>batch job</h3>' .
	'<p>verify that the sample result set below matches the results you’re expecting. if they do, you can create a background server process that will create an XML file based on this query. the resulting XML file will contain a total of ' . number_format( $TagResponse->totalResults ) . ' items and can be used to upload those items to <a href="https://commons.wikimedia.org">Wikimedia Commons</a> with the <a href="http://www.mediawiki.org/wiki/Extension:GWToolset">Mediawiki GWToolset Extension</a>.</p>' .

	'<form action="/my-europeana/tag-list-create-batch-job" method="post" role="form">' .
		'<input type="hidden" name="' . $Csrf->getTokenKey() . '" value="' . $Csrf->getTokenValue() . '" />' .
		'<input type="hidden" name="public-api-key" value="' . $j_username . '" />' .
		'<input type="hidden" name="private-api-key" value="' . $j_password . '" />' .
		'<input type="hidden" name="total-records-found" value="' . $TagResponse->totalResults . '" />' .
		'<input type="hidden" name="europeanaid" value="' . $europeanaid . '" />' .
		'<input type="hidden" name="tag" value="' . $tag . '" />' .
		'<input type="hidden" name="create-batch-job" value="true" />' .
		'<p>' .
			'<input type="submit" class="btn btn-default" value="create a batch job" />' .
		'</p>' .
	'</form>';