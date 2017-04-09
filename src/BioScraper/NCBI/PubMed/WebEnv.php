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
use BioScraper\NCBI\PubMed\PubMed;
use BioScraper\NCBI\PubMed\Article;
use SimpleXMLElement;

class WebEnv extends PubMed
{
	/**
	 * Tmp file path and name
	 */
	private $tmpfile;

	/**
	 * Tmp file directory
	 */
	private $dir;

	/**
	 * Main function of this class, get the result xml
	 * @param  string $term What are we searching?
	 * @param string $dir Where to save the results, defaults to current directory
	 * @return array array of New BioScraper\PubMed\Article objects
	 */
	public function query($term,$dir='.')
	{
		$this->dir = $dir;
		$this->term = $term;
		// Initiate search
		$this->setReturnMax(0);	// receive all
		$this->useHistory(true);	// use history

		$url  = $this->searchUrl();
		$url .= "?db=" . $this->getDb();
		intval($this->returnMax) ? $url .= "&retmax=" . intval($this->returnMax) : '';
		$this->usehistory ? $url .= "&usehistory=y" : '';
		$url .= "&retmode=" . self::RETURN_MODE;
		$url .= "&retstart=" . intval($this->returnStart);
		$url .= "&term=" . urlencode($term);

		$content = $this->sendRequest($url);
		$xml = new SimpleXMLElement($content);
		$this->esearchCount = (int) $xml->Count;

		if($this->esearchCount <= 0){
			throw new \Exception("No article was found, check your spelling!", 1);
		}

		$this->querykey = (int) $xml->QueryKey;
		$this->webenv = $xml->WebEnv;
		$articles = $this->webEnvFetch();
		return $articles;
	}

	/**
	 * If the results from NCBI is more than 10,000, all the results need to download for
	 * multiple times
	 *
	 * @return array of EstXmlParser | Exception
	 */
	public function nextQuery ()
	{   	
	    $this->setReturnStart($this->returnStart + $this->returnMax);
	    $article = $this->webEnvFetch();
	    return $article;
	}


	/**
	 * Fetch all data with WebEnv
	 * @return SimpleXMLElement instance $xml of bland new
	 */

	private function webEnvFetch()
	{
		$url = $this->fetchUrl();
		$url .= "?db=" . $this->getDb() . "&retmode=" . self::RETURN_MODE;
		$url .= '&retstart=' . $this->returnStart .'&retmax=' . $this->returnMax;
		$url .="&query_key=". $this->querykey . "&WebEnv=" . $this->webenv;

		$this->curl = curl_init(); // new curl
		$this->connectionTimeout = 120*60; // 120 minutes
		$this->timeout = 120*60;

		$content = $this->sendRequest($url);
		$dirr = opendir($this->dir);

		$this->tmpfile = $this->dir .'/'. 
						preg_replace('/\s/', '_', $this->term).'_'.
						date('YmdHis') . '_' . 
						$this->getDb() .'.xml.webenv';

		isset($this->tmpfile) ? file_put_contents($this->tmpfile, $content) : '';

		$articlesxml = (new SimpleXMLElement($content))->PubmedArticle;
		unset($content); // release memory

		$article = [];
		foreach ($articlesxml as $articlexml) {
			$article[] = new Article($articlexml);
		}
		return $article;
	}
}