<?php
/*
 * This file is part of the Biophpwrappers package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BioWrapper;

abstract class Entrez
{
	/**
	* Curl resource handle
	* @var resource
	*/
	protected $curl;

	/**
	* The number of seconds to wait while trying to connect.
	* @var integer
	*/
	protected $connectionTimeout = 30;

	/**
	* The maximum number of seconds to allow cURL functions to execute.
	* @var integer
	*/
	protected $timeout = 10;

	/**
	* Which database from NCBI to pull from
	* @var string
	*/
	protected $db;

	/**
	 * set database that going to search
	 */
	abstract public function getDb();

	/**
	 *  The base url of NCBI entrez eutils
	 */
	protected $baseUrl = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
	/**
	* The maximum number of articles to receive. Defaults to 0, receive all
	* @var integer
	*/
	protected $returnMax = 0;

	/**
	* Which article to start at
	* @var integer
	*/
	protected $returnStart = 0;

	/**
	* NCBI URL, should be set in child class
	* @var string
	*/
	protected $url;

	/**
	 * Use history to save previous search record
	 */
	protected $usehistory;

	/**
	 * Needed after searching with usehistory='y'
	 */
	protected $webenv;
	protected $querykey;
	/**
	* NCBI search URI name, should be set in child class
	* @var string
	*/
	protected $searchTermName;

	/**
	* Return mode from NCBI's API
	*/
	const RETURN_MODE = 'xml';

	/**
	*  Initiate the cURL connection
	*/
	public function __construct()
	{
		$this->curl = curl_init();
	}

	/**
	* Get the search URL, specific to child classes
	* -- do not implement here
	* @return string url
	*/
	protected function searchUrl() {
		return $this->baseUrl.'esearch.fcgi';
	}

	/**
	* Get the fetch URL, specific to child classes
	* -- do not implement here
	* @return string url
	*/
	protected function fetchUrl(){
		return $this->baseUrl.'efetch.fcgi';
	}

	/**
	 * Use history to save previous search record
	 * @param boolval $bool
	 */
	public function useHistory($bool)
	{
		boolval($bool) ? ($this->usehistory = true) : ($this->usehistory = false);
	}

	/**
	* At which article number to start?
	* @param integer $value The starting article index number
	*/
	public function setReturnStart($start)
	{
		return $this->returnStart = intval($start);
	}

	/**
	* Send the request to NCBI, return the raw result,
	* throw \Ambry\Pubmed exception on error
	* @param  string $url what are we searching for?
	* @return string XML string
	*/
	protected function sendRequest($url)
	{
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

		$rs = curl_exec($this->curl);
		$curl_error = curl_error($this->curl);
		curl_close($this->curl);

		if ($curl_error) {
			throw new \Exception($curl_error);
		}

		return $rs;
	}
}
