<?php
/*
 * This file is part of the biophpwrappers package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BioWrapper\NCBI\EST;
use BioWrapper\NCBI\EST\EstXmlParser;
use BioWrapper\NCBI\Entrez;
use SimpleXMLElement;

class WebEnv extends Entrez
{
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
	 * @return array array of New BioWrapper\EST\EstXmlParser objects
	 */
	public function query($term,$dir = '.')
	{
		$this->dir = $dir;
		// Initiate search
		$url  = $this->searchUrl();
		$url .= "?db=" . $this->getDb();
		$url .= "&usehistory=y&retmode=" . self::RETURN_MODE;
		$url .= "&retstart=" . intval($this->returnStart) . "&term=" . urlencode($term);

		$content = $this->sendRequest($url);
		$envxml = new SimpleXMLElement($content);
		$this->webenv = $envxml->WebEnv;
		$this->querykey = $envxml->QueryKey;

		$this->tmpfile = $this->dir .'/'. 
						preg_replace('/\s/', '_', $term).'_'.
						date('Y_m_d_H_i_s') . '_' . 
						$this->getDb() .'.xml.webenv';

		return $this->webEnvFetch();
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
		$url .='&WebEnv=' . $this->webenv . '&query_key=' . $this->querykey;

		$content = $this->sendRequest($url);

		$dirr = opendir($this->dir);

		// delete previous tmp file
		while(false !== ($file=readdir($dirr))){
			if($file != "." && $file != ".."){
				$pattern = '/'.$this->getDb().".xml.webenv$/";
				if(preg_match($pattern, $file)) unlink($file);
			}
		}
		isset($this->tmpfile) ? file_put_contents($this->tmpfile, $content) : '';
		
		$estxml = new SimpleXMLElement($content);
		$ests = [];
		foreach ($estxml->GBSeq as $ex) {
			$ests[] = new EstXmlParser($ex);
		}

		return $ests;
	}
}