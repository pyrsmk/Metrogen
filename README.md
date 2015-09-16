Myriade 1.0.2
=============

Myriade is a tiny library that aims to build automatically a fluid/responsive [vertical]()/[horizontal]() gallery with several different layouts :

- [metro](http://myriade.dreamysource.fr/vertical1) (like Windows 8)
- [horizontal](http://myriade.dreamysource.fr/vertical2)
- [vertical](http://myriade.dreamysource.fr/horizontal3)

Install
-------

Pick up the source or install it with [Composer](https://getcomposer.org/) :

```shell
composer require pyrsmk/myriade
```

If you're not installing it with Composer, you'll need to set up an autoloader to load Myriade by yourself with its dependencies : [Chernozem](https://github.com/pyrsmk/Chernozem) and [Imagix](https://github.com/pyrsmk/Imagix).

Examples
--------

- [vertical gallery 1](http://myriade.dreamysource.fr/vertical1)
- [vertical gallery 2](http://myriade.dreamysource.fr/vertical2)
- [vertical gallery 3](http://myriade.dreamysource.fr/vertical3)
- [vertical gallery 4](http://myriade.dreamysource.fr/vertical4)
- [horizontal gallery 1](http://myriade.dreamysource.fr/horizontal1)
- [horizontal gallery 2](http://myriade.dreamysource.fr/horizontal2)
- [horizontal gallery 3](http://myriade.dreamysource.fr/horizontal3)

Basics
------

First, we need to understand how Myriade works. It takes a list of weighted images so it knows what image is most important from another :

```php
$myriade = new Myriade(array(
	array(
		'path' => 'image.jpg',
		'weight' => 300,
		'data' => array('id' => 12) // optional, you can put here all the data you want to retrieve in each built image
	)
));
```

Your weight can be the popularity of a project, or the number of comments of an article. It doesn't matter because all image weights will be scaled to a predefined scale of 1 at minimum and 4 at maximum, by default. You can modify the scale, but please note that if you put a maximum value of about 10 (it depends of your global configuration, the number of images, etc), Myriade could fail to build your gallery because the image weights will be too exotic to place them easily on the workspace.

```
// Modify the weight scale
// The default scale is [1,4]
$myriade['weight_scale'] = array(
	'min' => 2,
	'max' => 6
);
```

You can also specify a simple list of images (all weights will be randomized) :

```php
// Define a simple image list
$myriade = new Myriade(array(
	'image1.jpg',
	'image2.jpg',
	'image3.jpg',
	'image4.jpg'
));
```

That said, we need to set our workspace. The workspace is divided into a number of columns and rows :

```php
$myriade['columns'] = 10;
$myriade['rows'] = 5;
```

If your layout needs to extend as much as possible, you can just specify the `columns` or the `rows` parameters :

```php
// Only 'columns' is defined, then the gallery will extend vertically
$myriade['columns'] = 10;
```

But we still don't know how much pixel size our gallery will take. We need to define the `block_size` parameter which defines the size of each block in the workspace (`200` by default) :

```php
// The workspace now has a width of 1000px
$myriade['block_size'] = 100;
```

Additionnaly, you can add a margin around each image :

```php
$myriade['margin'] = 10;
```

Now, the most interesting thing we can configure is the gallery layout. It can be `metro`, `horizontal` or `vertical`. Test them out!

```php
$myriade['layout'] = 'metro';
```

Here we are! Our weighted images and our workspace are all set. Now, we can build our gallery.
Try to play with all the values to better understand how Myriade's working and how you can achieve exactly what you need ;)

Vertical HTML layout
--------------------

```php
// Build a gallery for vertical HTML layouts (usually the case)
$gallery = $myriade->buildVerticalGallery('build_path/');
```

The structure of the returned `$gallery` variable is :

```php
array(
	'css' => string,
	'images' => array(
		array(
			'path' => string,
			'css' => string,
			'data' => mixed
		),
		array(
			'path' => string,
			'css' => string,
			'data' => mixed
		),
		...
	)
)
```

As you can see, Myriade generates the CSS code for your gallery and the images inside it, then you can simply integrates Myriade in your templates :

```html
<!-- Example with Twig templates -->
<div class="container">
	<div class="gallery" style="{{gallery.css}}">
		{% for image in gallery.images %}
			<img src="{{image.path}}" alt="" style="{{image.css}}">
		{% endfor %}
	</div>
</div>
```

As you may note, we wrapped our gallery with another container. We only need to do this with a vertical layout (the horizontal layout works differently). That wrapper can be the `body` itself, we just need to have a sized wrapper for the gallery to be fluid. The wrapper needs some additional CSS code, depending on how you want to display your gallery on your website :

```css
.container {
	// the magic value, here we can host a 1600px fluid workspace
	max-width: 1600px;
}
```

Horizontal HTML layout
----------------------

```php
// Build a gallery for horizontal HTML layouts
$gallery = $myriade->buildHorizontalGallery('build_path/');
```

The returned `$gallery` value, and how can implement it in a template, are the same as the previous chapter ;)

The horizontal layout works completely differently from the vertical layout. We don't need a container for our gallery but the workspace is set with `vh` units. Then, our images won't be exactly sized as expected but it will be close enough. Myriade computes the `vh` value by divinding the number of `px` of the workspace by `10`.

If you want to have a gallery that takes `70%` of the viewport height, you need to set your workspace height to `700px`. And if we want our gallery to take `100%` of the viewport height, we need a workspace height of `1000px`.

Responsive layouts
------------------

We still don't have a responsive gallery in our hands, just a fluid one. We need to build as many galleries as we have responsive modes. Per example, if we want to handle `max-width: 1280px`, `max-width: 768px` and `max-width: 480px` media queries, we'll build 3 gallery, on for each mode with different parameters. It's up to you to set them as your needs.

Take a look at the examples at the top of this doc to see something concrete ;)

Apply effects to images
-----------------------

If needed, you can apply some effects to your images when Myriade's building them, by setting a callback. An [Imagix](https://github.com/pyrsmk/Imagix) image will be passed to that callback. Please read the related documentation to see what effects are available and how we can use them.

```php
$myriade['callback'] = function($imagix) {
	$imagix->pixelate();
};
```

Note about the gallery generation
----------------------------------

Myriade will do 20 attempts (by default) to generate the gallery. If the parameters are too complicated, or weird, or impossible to handle, it will throw an exception. If you've encountered this problem, you'll need to modify some values. `weight_scale` should not be too big, `block_size` should not be too small, etc... But if you want specifically Myriade to render such a gallery and don't want to modify your parameters, you can modify modify the number of attempts :

```php
$myriade['attempts'] = 1000;
```

License
-------

Myriade is published under the [MIT license](http://dreamysource.mit-license.org).
