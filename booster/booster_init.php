<?php
/**
* CSS-JS-BOOSTER
*
* An easy to use PHP-Library that combines, optimizes, dataURI-fies, re-splits,
* compresses and caches your CSS and JS for quicker loading times.
*
* PHP version 5
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published
* by the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with this program.
* If not, see <http://www.gnu.org/licenses/lgpl-3.0.txt>
*
* @category  PHP
* @package   CSS-JS-Booster
* @author    Christian Schepp Schaefer <schaepp@gmx.de> <http://twitter.com/derSchepp>
* @copyright 2010 Christian Schepp Schaefer
* @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 3.0
* @link      http://github.com/Schepp/CSS-JS-Booster
*/

// Starting zlib-compressed output
@ini_set('zlib.output_compression',2048);
@ini_set('zlib.output_compression_level',4);

// Starting gzip-compressed output if zlib-compression is turned off
if (
	isset($_SERVER['HTTP_ACCEPT_ENCODING'])
	&& substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')
	&& function_exists('ob_gzhandler')
	&& (!ini_get('zlib.output_compression') || ini_get('zlib.output_compression') == '' || strtolower(ini_get('zlib.output_compression')) == 'off' || intval(ini_get('zlib.output_compression')) != 2048)
	&& !function_exists('booster_wp')
)
{
	$booster_use_ob_gzhandler = TRUE;
	@ob_start('ob_gzhandler');
}
else
{
	@ob_start();
}

