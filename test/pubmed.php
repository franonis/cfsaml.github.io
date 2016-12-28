<?php
/*
 * This file is part of the biophpwrappers package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
require '../vendor/autoload.php';

/**
 * WebEnv: get all the articles once with search term
 */
$webenv = new BioWrapper\PubMed\WebEnv();
$articles = $webenv->query('sea cucumber database');
foreach ($articles as $article) {
	// echo $article->getPubMedId()."\t".json_encode($article->getKeywords())."\n";
	echo $article->getArticleTitle()."\n";
	echo 'Author'.$article->getAuthors()[0]."\n";
}

/**
 * PubMedId: get an aticle with a PubMed ID
 */
$pubmedid = new BioWrapper\PubMed\PubMedId();
$article = $pubmedid->query(28012135);
echo $article->getArticleTitle()."\n";