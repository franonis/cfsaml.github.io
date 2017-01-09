<?php
/*
 * This file is part of the BioScraper package.
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
$webenv = new BioScraper\NCBI\PubMed\WebEnv();
$articles = $webenv->query('sea cucumber database');
foreach ($articles as $article) {
	// echo $article->getPubMedId()."\t".json_encode($article->getKeywords())."\n";
	echo $article->getJournalTitle()."\n";
	// var_dump($article->getAuthors());
}

/**
 * PubMedId: get an aticle with a PubMed ID
 */
$pubmedid = new BioScraper\NCBI\PubMed\PubMedId();
$article = $pubmedid->query(28012135);
var_dump($article->getAuthors());