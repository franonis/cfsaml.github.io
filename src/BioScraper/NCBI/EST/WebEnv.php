<?php
/*
 * This file is part of the BioScraper package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BioScraper\NCBI\EST;
use BioScraper\NCBI\EST\EstXmlParser;
use BioScraper\NCBI\Entrez;
use SimpleXMLElement;
use Exception;

class WebEnv extends Entrez
{
	/**
	 * @var est count
	 */
	private $est_num;

	/**
	 * Tmp file directory
	 */
	private $dir;

	/**
	 * set db
	 * @return string $this->db
	 */
	public function getDb(){
		return $this->db = 'nucest';
	}

	/**
	 * Main function of this class, get the result xml
	 * @param  string $term What are we searching?
	 * @return array array of New BioScraper\EST\EstXmlParser objects
	 */
	public function query($term,$dir = '.')
	{
		$this->dir = $dir;
		$this->term = $term;
		// Initiate search
		$url  = $this->searchUrl();
		$url .= "?db=" . $this->getDb();
		$url .= "&usehistory=y&retmode=" . self::RETURN_MODE;
		$url .= "&retstart=" . intval($this->returnStart) . "&term=" . urlencode($term);

		$content = $this->sendRequest($url);
		$envxml = new SimpleXMLElement($content);
		$this->webenv = $envxml->WebEnv;
		$this->querykey = $envxml->QueryKey;
		return $this->webEnvFetch();
	}

	/**
	 * If the results from NCBI is more than 10,000, all the results need to download for
	 * multiple times
	 *
	 * @return array of EstXmlParser | Exception
	 */
	public function nextQuery ()
	{
	    if($this->est_num < $this->returnMax)
	    	throw new Exception("No more return data from NCBI", 404);
	    	
	    $this->setReturnStart($this->returnStart + $this->returnMax);
	    $ests = $this->webEnvFetch();
	    return $ests;
	}

	/**
	 * Fetch the results with WebEnv and query_key
	 */
	private function webEnvFetch()
	{
		$this->curl = curl_init(); // new curl
		$this->connectionTimeout = 10*60; // 10 minutes
		$this->timeout = 10*60;

		$url = $this->fetchUrl();
		$url .= '?db=' . $this->getDb() . "&retmode=" . self::RETURN_MODE;
		$url .= '&retstart=' . $this->returnStart .'&retmax=' . $this->returnMax;
		$url .='&WebEnv=' . $this->webenv . '&query_key=' . $this->querykey;

		$content = $this->sendRequest($url);

		$this->tmpfile = $this->dir .'/'. 
						preg_replace('/\s/', '_', $this->term).'_'.
						date('YmdHis') . '_' . 
						$this->getDb() .'.xml.webenv';
		file_put_contents($this->tmpfile, $content);
		
		$estxml = new SimpleXMLElement($content);
		$ests = [];
		foreach ($estxml->GBSeq as $ex) {
			$ests[] = new EstXmlParser($ex);
		}
		$this->est_num = count($ests);
		return $ests;
	}
}
