<?php

namespace Metrogen;

use Exception;

/*
    Metrogen metadata generation class
*/
class Metadata {
    
    /*
        integer $columns
        integer $rows
        integer $block
        integer $margin
        array $shapes
    */
	protected $columns;
	protected $rows;
    protected $block = 200;
	protected $margin = 0;
    protected $shapes = ['1x1', '1x2', '2x1', '2x2'];
    
    /*
        Constructor
        
        Parameters
            array $options
    */
    public function __construct(array $options) {
        // Set options
        if(isset($options['columns'])) {
            $this->columns = (int)$options['columns'];
        }
        if(isset($options['rows'])) {
            $this->rows = (int)$options['rows'];
        }
        if(isset($options['block'])) {
            $this->block = (int)$options['block'];
        }
        if(isset($options['margin'])) {
            $this->margin = (int)$options['margin'];
        }
        if(isset($options['shapes'])) {
            $this->shapes = (array)$options['shapes'];
        }
        // Verify options
        if(!is_int($this->columns) || !is_int($this->rows)) {
			throw new Exception("'columns' and 'rows' options must be defined");
		}
    }
    
    /*
        Generate metadata for a future gallery
        
        Return
            array
    */
    public function generate() {
        // Generate an empty map (y,x)
        $map = array_fill(0, $this->rows, array_fill(0, $this->columns, 0));
        $blocks = ($this->columns * $this->rows);
        $metadata = [];
        // Prepare shapes
        foreach($this->shapes as $shape) {
            list($width, $height) = explode('x', $shape);
            $shapes[] = ['width' => $width, 'height' => $height];
        }
        // Position elements on the map
        while($position = $this->_getNextAvailablePosition($map)) {
            // Test the availability of each shape for that position
            $available = [];
            foreach($shapes as $shape) {
                if($this->_isShapeAllowed($shape, $position, $map, $blocks)) {
                    $available[] = $shape;
                }
            }
            // No shape found for that position
            if(!$available) {
                break;
            }
            else {
                // Choose the shape to use
                $shape = $available[array_rand($available)];
                // Save block on map
                $map = $this->_markPosition($map, $shape, $position);
                $blocks -= $shape['width'] * $shape['height'];
                // Save element position and dimensions
                $metadata[] = [
                    'x' => $position['x'] * $this->block,
                    'y' => $position['y'] * $this->block,
                    'width' => $shape['width'] * $this->block,
                    'height' => $shape['height'] * $this->block
                ];
            }
        }
        // Return the gallery metadata
        return $metadata;
	}
    
    /*
        Get the next available position on a map
        
        Parameters
            array $map
        
        Return
            array, false
    */
    protected function _getNextAvailablePosition($map) {
        $found = false;
        for($i=0, $j=$this->rows; $i<$j; ++$i) {
            for($k=0, $l=$this->columns; $k<$l; ++$k) {
                if($map[$i][$k] === 0) {
                    $found = true;
                    $x = $k;
                    $y = $i;
                    break;
                }
            }
            if($found) {
                break;
            }
        }
        if($found) {
            return ['x' => $x, 'y' => $y];
        }
        else {
            return false;
        }
    }
    
    /*
        Verify is the given shape is allowed at a specific position on the map
        
        Parameters
            array $map
        
        Return
            array, false
    */
    protected function _isShapeAllowed($shape, $position, $map, $blocks) {
        // Not allowed, out of blocks
        if($shape['width'] * $shape['height'] > $blocks) {
            return false;
        }
        else {
            // Not allowed, out of bounds
            if(
                ($position['x'] + $shape['width'] > $this->columns) ||
                ($position['y'] + $shape['height'] > $this->rows)
            ) {
                return false;
            }
            else {
                // Not allowed, adjacent to an occupied block
                for($i=$position['y'], $j=$position['y']+$shape['height']; $i<$j; ++$i) {
                    for($k=$position['x'], $l=$position['x']+$shape['width']; $k<$l; ++$k) {
                        if($map[$i][$k]) {
                            return false;
                        }
                    }
                }
                // The shape is allowed
                return true;
            }
        }
    }
    
    /*
        Lock a position on the map
        
        Parameters
            array $map
            array $shape
            array $position
        
        Return
            array
    */
    protected function _markPosition($map, $shape, $position) {
        for($i=$position['y'], $j=$position['y']+$shape['height']; $i<$j; ++$i) {
            for($k=$position['x'], $l=$position['x']+$shape['width']; $k<$l; ++$k) {
                $map[$i][$k] = 1;
            }
        }
        return $map;
    }
    
}