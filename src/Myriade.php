<?php

/*
	A fluid/responsive multi-layout gallery generator
*/
class Myriade extends Chernozem {
	
	/*
		array $__data
		string $layout
		integer $block_size
		integer $columns
		integer $rows
		integer $margin
		integer $weight_scale
		mixed $callback
		array $__scenarii
		integer $__retries
	*/
	protected $__data;
	protected $layout = 'metro';
	protected $block_size = 200;
	protected $columns;
	protected $rows;
	protected $margin = 0;
	protected $weight_scale = array(
		'min' => 1,
		'max' => 4
	);
	protected $callback;
	protected $__scenarii = array();
	protected $__retries;
	
	/*
		Constructor
		
		Parameters
			array $data
	*/
	public function __construct(array $data) {
		foreach($data as $i=>&$d) {
			if(is_string($d)) {
				$d = array(
					'path' => $d,
					'weight' => rand($this->weight_scale['min'], $this->weight_scale['max'])
				);
			}
			if(!isset($d['path'])) {
				throw new Exception("'path' option must be defined");
			}
			if(pathinfo($d['path'], PATHINFO_BASENAME) == 'Thumbs.db') {
				array_splice($data, $i, 1);
			}
		}
		unset($d);
		$this->__data = $data;
	}
	
	
	/*
		Build a vertical gallery
		
		Parameters
			string $directory
		
		Return
			array
	*/
	public function buildVerticalGallery($directory) {
		return $this->_build($directory, 'vertical');
	}
	
	
	/*
		Build a horizontal gallery
		
		Parameters
			string $directory
		
		Return
			array
	*/
	public function buildHorizontalGallery($directory) {
		return $this->_build($directory, 'horizontal');
	}
	
	/*
		Build the gallery
		
		Parameters
			string $directory
			string $orientation
		
		Return
			array
	*/
	protected function _build($directory, $orientation) {
		$this->_verifyOptions();
		$this->_scaleDataWeights();
		$this->_sortData();
		do {
			$workspace = $this->_initWorkspace();
			$gallery = $this->_createGallery($directory, $orientation, $workspace);
			if($gallery === false) {
				++$this->__retries;
				if($this->__retries == 20) {
					$this->__retries = 0;
					throw new Exception("It seems we cannot build a gallery with the current configuration, please verify your parameters");
				}
			}
		}
		while($gallery === false);
		return $gallery;
	}
	
	/*
		Verify options
	*/
	protected function _verifyOptions() {
		if(!is_int($this->columns) && !is_int($this->rows)) {
			throw new Exception("'columns' or 'rows' option must be defined");
		}
		switch($this->layout) {
			case 'horizontal':
			case 'vertical':
			case 'metro':
				break;
			default:
				throw new Exception("'layout' option must be 'horizontal', 'vertical' or 'metro'");
		}
		if($this->weight_scale['max'] <= $this->weight_scale['min']) {
			throw new Exception("'weight_scale' maximum value must be greater then its minimum value");
		}
	}
	
	/*
		Scale weights
		
		Return
			array
	*/
	protected function _scaleDataWeights() {
		// Define global weights
		$weights = array();
		foreach($this->__data as $data) {
			$weights[] = $data['weight'];
		}
		sort($weights);
		// Verify
		if($weights[0] == $weights[count($weights)-1]) {
			return;
		}
		// Scale weights
		foreach($this->__data as &$data) {
			$data['weight'] = round(($data['weight'] * $this->weight_scale['max']) / $weights[count($weights)-1]);
			if($data['weight'] == 0) {
				$data['weight'] = 1;
			}
		}
		unset($data);
	}
	
	/*
		Initialize workspace
		
		Return
			array
	*/
	protected function _initWorkspace() {
		// Prepare
		$workspace = array();
		// Compute the gallery weight
		$gallery_weight = 0;
		foreach($this->__data as $data) {
			$gallery_weight += $data['weight'];
		}
		// Fill the blanks
		if(!$this->columns) {
			$this->columns = ceil($gallery_weight / $this->rows);
		}
		if(!$this->rows) {
			$this->rows = ceil($gallery_weight / $this->columns);
		}
		// Verify the number of images
		$workspace_weight = $this->columns * $this->rows;
		if(count($this->__data) > $workspace_weight) {
			throw new Exception("That '".$this->columns."x".$this->rows."' workspace is too short to position all '".count($this->__data)."' images");
		}
		// Initialize workspace
		for($x=1, $i=$this->columns; $x<=$i; ++$x) {
			for($y=1, $j=$this->rows; $y<=$j; ++$y) {
				$workspace[] = $x.':'.$y;
			}
		}
		shuffle($workspace);
		// Fill the workspace
		if($gallery_weight > $workspace_weight) {
			$i = 0;
			while($gallery_weight > $workspace_weight) {
				if($this->__data[$i]['weight'] > $this->weight_scale['min']) {
					--$this->__data[$i]['weight'];
					--$gallery_weight;
				}
				if(++$i == count($this->__data)) {
					$i = 0;
				}
			}
		}
		else if($gallery_weight < $workspace_weight) {
			$i = 0;
			while($gallery_weight < $workspace_weight) {
				if($this->__data[$i]['weight'] < $this->weight_scale['max']) {
					++$this->__data[$i]['weight'];
					++$gallery_weight;
				}
				if(++$i == count($this->__data)) {
					$i = 0;
				}
			}
		}
		return $workspace;
	}
	
	/*
		Sort data
		
		Return
			array
	*/
	protected function _sortData() {
		usort($this->__data, function($a, $b) {
			return $b['weight'] - $a['weight'];
		});
	}
	
	/*
		Create gallery
		
		Parameters
			string $directory
			string $orientation
			array $workspace
		
		Return
			array, false
	*/
	protected function _createGallery($directory, $orientation, $workspace) {
		// Prepare
		$gallery = array(
			'css' => 'position: relative;'.
					 'box-sizing: border-box;'.
					 ($orientation == 'vertical' ? 'max-width: './*($this->columns * $this->block_size).'px;'*/'100%;' : '').
					 ($orientation == 'vertical' ? 'padding-bottom: '.($this->rows * 100 / $this->columns).'%;' : '').
					 ($orientation == 'horizontal' ? 'height: '.($this->rows * $this->block_size / 10).'vh;' : '').
					 ($orientation == 'horizontal' ? 'width: '.($this->columns * $this->block_size / 10).'vh;' : ''),
			'images' => array()
		);
		$directory = substr($directory, -1) == '/' ? substr($directory, 0, -1) : $directory;
		// Position images
		foreach($this->__data as &$data) {
			$position = $this->_getPosition($data['weight'], $workspace);
			// Current positioning is endless, reroll
			if($position === false) {
				return false;
			}
			// Save position
			else {
				$data['position'] = $position;
			}
		}
		unset($data);
		// Generate images
		foreach($this->__data as $data) {
			// Load image
			try {
				$imagix = Imagix\Factory::forge($data['path']);
			}
			catch(Imagix\Exception $e) {
				continue;
			}
			// Adapt image to the requested size
			$imagix->resize(max(
				$data['position']['width'] * 100 / $imagix->getWidth(),
				$data['position']['height'] * 100 / $imagix->getHeight()
			).'%');
			// Crop image
			$imagix->crop(
				($imagix->getWidth() - $data['position']['width']) / 2 + $this->margin,
				($imagix->getHeight() - $data['position']['height']) / 2 + $this->margin,
				$data['position']['width'] - ($this->margin * 2),
				$data['position']['height'] - ($this->margin * 2)
			);
			// Apply callback
			if(is_callable($this->callback)) {
				call_user_func($this->callback, $imagix);
			}
			// Save final image
			$newpath = $directory.'/'.pathinfo($data['path'], PATHINFO_BASENAME);
			$imagix->save($newpath);
			// Add image to the gallery
			$gallery['images'][] = array(
				'path'		=> $newpath,
				'css'		=> 'position: absolute;'.
							   'width: '.(($data['position']['width'] - ($this->margin * 2)) * 100 / ($this->columns * $this->block_size)).'%;'.
							   'height: '.(($data['position']['height'] - ($this->margin * 2)) * 100 / ($this->rows * $this->block_size)).'%;'.
							   'left: '.(($data['position']['left'] + $this->margin) * 100 / ($this->columns * $this->block_size)).'%;'.
							   'top: '.(($data['position']['top'] + $this->margin) * 100 / ($this->rows * $this->block_size)).'%;',
				'data'		=> isset($data['data']) ? $data['data'] : array()
			);
		}
		return $gallery;
	}
	
	/*
		Search for an available position in the workspace
		
		Parameters
			integer $weight
			array $workspace
		
		Return
			array, false
	*/
	protected function _getPosition($weight, &$workspace) {
		for($i=0, $j=count($workspace); $i<$j; ++$i) {
			// Extract the position
			list($x, $y) = explode(':', $workspace[$i]);
			$x = (int)$x;
			$y = (int)$y;
			// Verify all positioning scenarii
			foreach($this->_getScenarii($weight) as $scenario) {
				// Verify if we're in the bounds
				if(($x+$scenario['width']-1) > $this->columns || ($y+$scenario['height']-1) > $this->rows) {
					continue;
				}
				// Verify if the current scenario works
				$available = true;
				for($k=$x, $l=($x+$scenario['width']-1); $k<=$l; ++$k) {
					for($m=$y, $n=($y+$scenario['height']-1); $m<=$n; ++$m) {
						if(!in_array($k.':'.$m, $workspace)) {
							$available = false;
							break;
						}
					}
					if(!$available) {
						break;
					}
				}
				if($available) {
					// Lock position
					for($k=$x, $l=($x+$scenario['width']-1); $k<=$l; ++$k) {
						for($m=$y, $n=($y+$scenario['height']-1); $m<=$n; ++$m) {
							array_splice($workspace, array_search($k.':'.$m, $workspace), 1);
						}
					}
					// Return position metadata
					return array(
						'width' => $scenario['width'] * $this->block_size,
						'height' => $scenario['height'] * $this->block_size,
						'left' => ($x - 1) * $this->block_size,
						'top' => ($y - 1) * $this->block_size
					);
				}
			}
		}
		return false;
	}
	
	/*
		Generate the positioning scenarii
		
		Parameters
			integer $weight
		
		Return
			array
	*/
	protected function _getScenarii($weight) {
		// Verify cache
		if(!isset($this->__scenarii[$weight])) {
			$scenarii = array();
			// Compute scenarii
			for($i=$weight; $i>=1; --$i) {
				if($weight % $i == 0) {
					switch($this->layout) {
						case 'horizontal':
							if($weight == 1 || $i > $weight/$i) {
								$scenarii[] = array('width' => $i, 'height' => $weight/$i);
							}
							break;
						case 'vertical':
							if($weight == 1 || $i < $weight/$i) {
								$scenarii[] = array('width' => $i, 'height' => $weight/$i);
							}
							break;
						case 'metro':
							$scenarii[] = array('width' => $i, 'height' => $weight/$i);
							break;
					}
				}
			}
			// Save data
			$this->__scenarii[$weight] = $scenarii;
		}
		return $this->__scenarii[$weight];
	}
	
}