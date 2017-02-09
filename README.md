Metrogen 2.0.0
==============

Metrogen is a tiny library aiming to automatically create metadata, ready to integrate, for a metro style gallery (as we can see in Windows 8). The position/dimensions of each place in the gallery is randomized.

This library is part of the Myriade 2 project (link coming soon).

Install
-------

```
composer require pyrsmk/metrogen
```

Use
---

The constructor takes several options for generating the gallery :

- `block` : the block size in px (default : `200`)
- `columns` : the number of columns of the workspace (this option is `required`)
- `rows` : the number of rows of the workspace (this option is `required`)
- `shapes` : the list of the different allowed shapes (default : `['1x1', '1x2', '2x1', '2x2']`)
- `margin` : a margin to add around each element (default : `0`)

To create a metro gallery, we divide all the workspace into blocks (with a fixed size in pixels), and then generate shapes (based on the allowed shapes) to add into the gallery. The size of the workspace is fixed to the number of columns and rows, based on the block size.

Here's a quick example, with a workspace of `1600x1000`, other interesting shapes, and a margin :

```php
$metrogen = new Metrogen\Metadata([
    'columns' => 8,
    'rows' => 5,
    'shapes' => ['1x1', '1x3', '3x1', '2x2', '3x2'],
    'margin' => 5
]);

$metadata = $metrogen->generate();

var_dump($metadata);
```

It will print :

```
array(13) {
  [0] => array(4) {
    ["x"] => int(0)
    ["y"] => int(0)
    ["width"] => int(200)
    ["height"] => int(600)
  }
  [1] => array(4) {
    ["x"] => int(200)
    ["y"] => int(0)
    ["width"] => int(400)
    ["height"] => int(400)
  }
  [2] => array(4) {
    ["x"] => int(600)
    ["y"] => int(0)
    ["width"] => int(600)
    ["height"] => int(400)
  }
  [3] => array(4) {
    ["x"] => int(1200)
    ["y"] => int(0)
    ["width"] => int(400)
    ["height"] => int(400)
  }
  [4] => array(4) {
    ["x"] => int(200)
    ["y"] => int(400)
    ["width"] => int(600)
    ["height"] => int(200)
  }
  [5] => array(4) {
    ["x"] => int(800)
    ["y"] => int(400)
    ["width"] => int(400)
    ["height"] => int(400)
  }
  [6] => array(4) {
    ["x"] => int(1200)
    ["y"] => int(400)
    ["width"] => int(400)
    ["height"] => int(400)
  }
  [7] => array(4) {
    ["x"] => int(0)
    ["y"] => int(600)
    ["width"] => int(600)
    ["height"] => int(200)
  }
  [8] => array(4) {
    ["x"] => int(600)
    ["y"] => int(600)
    ["width"] => int(200)
    ["height"] => int(200)
  }
  [9] => array(4) {
    ["x"] => int(0)
    ["y"] => int(800)
    ["width"] => int(600)
    ["height"] => int(200)
  }
  [10] => array(4) {
    ["x"] => int(600)
    ["y"] => int(800)
    ["width"] => int(600)
    ["height"] => int(200)
  }
  [11] => array(4) {
    ["x"] => int(1200)
    ["y"] => int(800)
    ["width"] => int(200)
    ["height"] => int(200)
  }
  [12] => array(4) {
    ["x"] => int(1400)
    ["y"] => int(800)
    ["width"] => int(200)
    ["height"] => int(200)
  }
}
```

License
-------

[MIT](http://dreamysource.mit-license.org).
