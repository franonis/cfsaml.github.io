<?php
/*
 * This file is part of the biophpwrappers package.
 *
 * (c) Bing Liu <liub@mail.bnu.edu.cn>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace BioWrapper\EST;
use BioWrapper\EST\EstXmlParser;
use BioWrapper\Entrez;
use SimpleXMLElement;

class EstId extends Entrez
{
	/**
	 * EST ID
	 * @var integer
	 */
	public $estid;

	/**
	 * XML content curl from NCBI
	 */
	public $xml;

 	/**
	 * set database that going to search
	 */
	public function getDb(){
		return $this->db = 'nucest';
	}

	/**
	 * Get EST information with a EST id
	 * @param integer $estid EST id
	 * @return String content of XML data
	 */
	public function query($estid){
		$url = $this->fetchUrl();
		$url .= '?db='.$this->getDb().'&retmode='.
				self::RETURN_MODE.'&id='.intval($estid);

		$content = $this->sendRequest($url);
		$xml = (new SimpleXMLElement($content))->GBSeq;
		if (count($xml)<1)
			return new \Exception('No EST information fetched from NCBI, check EST id '.$estid);
		
		return new EstXmlParser($xml) ;
	}

}