<?php

	$menu_items = array(
		'tag' => array(
			'href' => '/my-europeana/tag-list-search',
			'page' => 'my-europeana/tag-list-search',
			'title' => 'tag list'
		),
		'queue' => array(
			'href' => '/queue',
			'page' => 'queue',
			'title' => 'queue'
		)
	);

	if ( APPLICATION_ENV === 'development' ) {
			$menu_items = array(
				'search' => array(
					'href' => '/search',
					'page' => 'search',
					'title' => 'search'
				)
			) +
			$menu_items;
	}

	$Nav = new Html\Nav( $menu_items );
?>
<div id="header">
	<a class="logo" href="/" title="<?php echo $config['site-name']; ?>"></a>
	<h1><?php echo $Page->heading; ?></h1>
	<?php echo $Nav->getNavAsUl( 'nav', $Page->page ); ?>
</div>