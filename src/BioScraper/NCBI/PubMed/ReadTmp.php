<?php
/*
 * This file is part of the BioScraper package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace BioScraper\NCBI\PubMed;
use BioScraper\NCBI\PubMed\Article;
use SimpleXMLElement;

class ReadTmp
{
	/**
	 * tmpfiel
	 * @var string
	 */
	private $tmpfile;

	/**
	 * construct
	 */
	public function __construct($tmpfile)
	{
		$this->tmpfile = $tmpfile;
	}

	/**
	 * Main function of this class, get the result xml
	 * @param  string $term What are we searching?
	 * @param string $dir Where to save the results, defaults to current directory
	 * @return array array of New BioScraper\PubMed\Article objects
	 */
	public function parse()
	{
		file_exists($this->tmpfile) ? 
			$content = file_get_contents($this->tmpfile) : 
			die("No such tmpfile ".$this->tmpfile);

		$articlesxml = (new SimpleXMLElement($content))->PubmedArticle;
		unset($content); // release memory

		$article = [];
		foreach ($articlesxml as $articlexml) {
			$article[] = new Article($articlexml);
		}
		return $article;
	}
}
