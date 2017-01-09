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
		$this->articleCount = (int) $xml->Count;

		if($this->articleCount <= 0){
			die("No article was found, check your spelling!\n");
		}
		
		$this->tmpfile = $this->dir .'/'. 
						preg_replace('/\s/', '_', $term).'_'.
						date('YmdHis') . '_' . 
						$this->getDb() .'.xml.webenv';

		$this->querykey = (int) $xml->QueryKey;
		$this->webenv = $xml->WebEnv;
		$articles = $this->webEnvFetch();
		return $articles;
	}

	/**
	 * Fetch all data with WebEnv
	 * @return SimpleXMLElement instance $xml of bland new
	 */

	private function webEnvFetch()
	{
		$url = $this->fetchUrl();
		$url .= "?db=" . $this->getDb() . "&retmode=" . self::RETURN_MODE;
		$url .="&query_key=". $this->querykey . "&WebEnv=" . $this->webenv;

		$this->curl = curl_init(); // new curl
		$this->connectionTimeout = 10*60; // 10 minutes
		$this->timeout = 10*60;

		$content = $this->sendRequest($url);
		$dirr = opendir($this->dir);

		// delete previous tmp file
		while(false !== ($file=readdir($dirr))){
			if($file != "." && $file != ".."){
				$pattern = '/' . $this->getDb() . ".xml.webenv$/";
				if(preg_match($pattern, $file)) unlink($this->dir.'/'.$file);
			}
		}

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
