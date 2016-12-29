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
use SimpleXMLElement;
use \Exception;

class PubMedId extends PubMed
{
	/**
	 * Main function of this class, get the result xml, searching
	 * by PubMedId (PMID)
	 * @param  string $pmid PubMedID
	 * @return object New BioScraper\PubMed\Article
	 */
	public function query($pmid)
	{
		$url = $this->fetchUrl();
		$url .= "?db=" . $this->getDb() . "&retmode=" . self::RETURN_MODE;
		$url .= "&id=" . intval($pmid);

		$content = $this->sendRequest($url);
		$xml = new SimpleXMLElement($content);
		if(count($xml->PubmedArticle)===1) {
			$this->articleCount = 1;
			return new Article($xml->PubmedArticle);
		} else {
			throw new Exception('Nothing found! Check your PMID '.$pmid);
		}
	}
}
