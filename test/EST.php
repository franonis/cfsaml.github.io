<?php
/*
 * This file is part of the biowrappers package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require '../vendor/autoload.php';

## EstId
$est = new BioWrapper\EST\EstId();
$est = $est->query('664802198');
var_dump($est->toJSON());

## WebEnv
$ests = new BioWrapper\EST\WebEnv();
$xml = $ests->query('Giant panda');
var_dump($xml);
