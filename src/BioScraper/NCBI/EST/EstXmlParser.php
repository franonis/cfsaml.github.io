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
use SimpleXMLElement;

/**
* This is XML parsr for EST data from NCBI eutils, 
* parse only one GBSeq node at one time
*/
class EstXmlParser
{
	/**
	 * XML content
	 * @var string
	 */
	private $xml;


	/**
	 * GBSeq_locus
	 * @var string
	 */
	private $locus;	

	/**
    * Constructor, init
    * @param String $xml content of XML data of GBSeq
    */
	function __construct($xml)
	{
		$this->xml = $xml;
	}

	/**
    * Return array of all results
    * @return array array of results
    */
    public function getResult()
    {
        return $this->toArray();
    }

    /**
     * Return JSON result
     * @return json
     */
    public function toJSON()
    {
    	return json_encode($this->toArray());
    }

    /**
    * Run all getters on the xml object
    * @return array array of all getters
    */
    private function toArray()
    {
    	return array(
    		'Locus'				=> $this->getLoucs(),
    		'Length'			=> $this->getLength(),
    		'Strandedness'		=> $this->getStrandedness(),
    		'mulType'			=> $this->getMulType(),
    		'Topology'			=> $this->getTopology(),
    		'PriAccession'		=> $this->getPriAccession(),
    		'AccessionVersion'	=> $this->getAccessionVersion(),
    		'OtherSeqIds'		=> $this->getOtherSeqIds(),
    		'Organism'			=> $this->getOrganism(),
    		'Taxonomy'			=> $this->getTaxonomy(),
    		'Source'			=> $this->getSource(),
    		'Refferences'		=> $this->getRefs(),
    		'Comment'			=> $this->getComment(),
    		'Features'			=> $this->getFeatures(),
    		'Sequence'			=> $this->getSequence()
    	);
    }
    
	/**
	 * Get GBSeq_locus
	 * @return String GBSeq_locus
	 */
	public function getLoucs()
	{
		return (string) $this->xml->GBSeq_locus;
	}

	/**
	 * Get GBSeq_length
	 * @return integer GBSeq_length
	 */
	public function getLength()
	{
		return intval($this->xml->GBSeq_length);
	}

	/**
	 * Get GBSeq_strandedness
	 * @return String GBSeq_locus
	 */
	public function getStrandedness()
	{
		return (string) $this->xml->GBSeq_strandedness;
	}

	/**
	 * Get GBSeq_moltype
	 * @return String GBSeq_moltype
	 */
	public function getMulType()
	{
		return (string) $this->xml->GBSeq_moltype;
	}

	/**
	 * Get GBSeq_topology
	 * @return String GBSeq_topology
	 */
	public function getTopology()
	{
		return (string) $this->xml->GBSeq_topology;
	}

	/**
	 * Get GBSeq_primary-accession
	 * @return String GBSeq_primary-accession
	 */
	public function getPriAccession()
	{
		return (string) $this->xml->{'GBSeq_primary-accession'};
	}

	/**
	 * Get GBSeq_accession-version
	 * @return String GBSeq_accession-version
	 */
	public function getAccessionVersion()
	{
		return (string) $this->xml->{'GBSeq_accession-version'};
	}

	/**
	 * Get GBSeq_other-seqids
	 * @return String GBSeq_other-seqids
	 */
	public function getOtherSeqIds()
	{
		$seqids = [];
		if(isset($this->xml->{'GBSeq_other-seqids'})) {
            foreach ($this->xml->{'GBSeq_other-seqids'}->GBSeqid as $seqid) {
                $seqids[] = (string) $seqid;
	        }
		}
		return $seqids;
	}

	/**
	 * Get GBSeq_organism
	 * @return string GBSeq_organism
	 */
	public function getOrganism()
	{
		return (string) $this->xml->GBSeq_organism;
	}

	/**
	 * Get GBSeq_taxonomy
	 * @return string GBSeq_taxonomy
	 */
	public function getTaxonomy()
	{
		return (string) $this->xml->GBSeq_taxonomy;
	}

	/**
	 * Get GBSeq_source
	 * @return string GBSeq_source
	 */
	public function getSource()
	{
		return (string) $this->xml->GBSeq_source;
	}

	/**
	 * Get GBSeq_references
	 * @return $refs array of array of title and pubmed id
	 */
	public function getRefs()
	{
		$refs = [];
		foreach ($this->xml->GBSeq_references->GBReference as $reference) {
			$ref = [];
			$ref['authors'] = '';
			foreach ($reference->GBReference_authors->GBAuthor as $author) {
				$ref['authors'] .= (string) $author . ', ';
			}
			$ref['authors'] = trim($ref['authors'],', ');
			$ref['title'] = (string) $reference->GBReference_title;
			$ref['journal'] = (string) $reference->GBReference_journal;
			isset($reference->GBReference_pubmed) ? $ref['pubmedid'] = (string) $reference->GBReference_pubmed : '';
			
			$refs[] = $ref;
		}
		return $refs;
	}

	/**
	 * Get GBSeq_comment
	 * SUBMITTER: Name, Lab, Institution, Address, E-mail
	 * @return array GBSeq_comment
	 */
	public function getComment()
	{
		return explode('~',(string) $this->xml->GBSeq_comment);
	}

	/**
	 * Get GBFeature_quals
	 * @return array
	 */
	public function getFeatures()
	{
		$features = [];
		$featuresxml = $this->xml->{'GBSeq_feature-table'}->GBFeature->GBFeature_quals;
		foreach ($featuresxml->GBQualifier as $sf) {
			$features[(string) $sf->GBQualifier_name] = (string) $sf->GBQualifier_value;
		}
		return $features;
	}

	/**
	 * Get GBSeq_sequence
	 * @return string GBSeq_sequence
	 */
	public function getSequence()
	{
		return (string) $this->xml->GBSeq_sequence;
	}
}