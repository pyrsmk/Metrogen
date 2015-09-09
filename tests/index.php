<?php

ini_set('display_errors', 1);

// Load classes
require '../vendor/autoload.php';
require '../src/Myriade.php';
require 'vendor/autoload.php';
$lumy = new Lumy\Http();

// Set the current page
$lumy['page'] = substr($lumy['request']->getResourceUri(), 1);
if(!file_exists('templates/'.$lumy['page'].'.twig')) {
	echo "'{$lumy['page']}' page does not exist";
	exit;
}

// Read configuration
$lumy['config'] = json_decode(file_get_contents('conf.json'));

// Prepare data
$lumy->middleware(function($middlewares) use($lumy) {
	$data = array();
	foreach(lessdir('images/') as $path) {
		$data[] = array(
			'path' => "images/$path",
			'weight' => $lumy['config']->{$lumy['page']}->global_weight == 'random' ?
						rand(1, 300) :
						$lumy['config']->{$lumy['page']}->global_weight
		);
	}
	$lumy['data'] = $data;
	$middlewares->next();
});

// Load Twig
$lumy['twig'] = function() {
	$loader = new Twig_Loader_Filesystem('templates/');
	return new Twig_Environment($loader, array('debug' => true));
};
$lumy->service('twig');

// Create Myriade
$lumy['myriade'] = function() use($lumy) {
	$myriade = new Myriade($lumy['data']);
	$myriade['layout'] = (string)$lumy['config']->{$lumy['page']}->layout;
	$myriade['columns'] = (int)$lumy['config']->{$lumy['page']}->columns;
	$myriade['rows'] = (int)$lumy['config']->{$lumy['page']}->rows;
	$myriade['block_size'] = (int)$lumy['config']->{$lumy['page']}->block_size;
	$myriade['margin'] = (float)$lumy['config']->{$lumy['page']}->margin;
	$myriade['weight_scale'] = (array)$lumy['config']->{$lumy['page']}->weight_scale;
	/*$myriade['callback'] = function($imagix) {
		$imagix->grayscale();
		$imagix->smooth();
	};*/
	return $myriade;
};
$lumy->service('myriade');

// Display the requested gallery
$lumy->get('/*', function() use($lumy) {
	// Create the directory
	if(!file_exists('gallery/'.$lumy['page'])) {
		mkdir('gallery/'.$lumy['page']);
	}
	// Clean up the gallery
	if($lumy['config']->debug) {
		foreach(lessdir('gallery/'.$lumy['page']) as $path) {
			unlink('gallery/'.$lumy['page'].'/'.$path);
		}
	}
	// Check the cache
	if(count(lessdir('gallery/'.$lumy['page']))) {
		$gallery = json_decode(file_get_contents('gallery/'.$lumy['page'].'/gallery.json'));
	}
	else {
		// Build
		switch($lumy['config']->{$lumy['page']}->orientation) {
			case 'vertical':
				$gallery = $lumy['myriade']->buildVerticalGallery('gallery/'.$lumy['page']);
				break;
			case 'horizontal':
				$gallery = $lumy['myriade']->buildHorizontalGallery('gallery/'.$lumy['page']);
				break;
		}
		// Cache
		file_put_contents('gallery/'.$lumy['page'].'/gallery.json', json_encode($gallery));
	}
	// Render gallery
	echo $lumy['twig']->render($lumy['page'].'.twig', array(
		'debug' => $lumy['config']->debug,
		'rooturi' => $lumy['request']->getRootUri(),
		'gallery' => $gallery
	));
});

$lumy->run();