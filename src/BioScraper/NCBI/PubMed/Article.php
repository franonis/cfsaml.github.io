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
use SimpleXMLElement;

class Article
{
    /**
    * SimpleXMLElement class will work on
    * @var object
    */
    private $xml;

    private $articlexml;

    /**
    * PubMed ID
    * @var integer
    */
    private $pmid;

    /**
    * Constructor, init
    * @param SimpleXMLElement $xml The main xml object to work on
    */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml->MedlineCitation;
        $this->articlexml = $xml->MedlineCitation->Article;
        $this->pmid = (string) $xml->MedlineCitation->PMID;
    }

    /**
    * Magic Method 
    * @return string return object print_r for debugging
    */
    public function __toString()
    {
        return print_r($this->articlexml, true);
    }

    /**
    * Get JSON result of all items
    * @return string JSON encoded string of results
    */
    public function toJson()
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
            'PMID'                  =>     $this->getPubMedId(),
            'Volume'                =>     $this->getVolume(),
            'Issue'                 =>     $this->getIssue(),
            'PubYear'               =>     $this->getPubYear(),
            'PubMonth'              =>     $this->getPubMonth(),
            'PubDay'                =>     $this->getPubDay(),
            'ISSN'                  =>     $this->getISSN(),
            'JournalTitle'          =>     $this->getJournalTitle(),
            'JournalTitleAbbre'     =>     $this->getJournalTitleAbbre(),
            'JournalCountry'        =>     $this->getJournalCountry(),
            'JournalIssue'          =>     $this->getJournalISSNLinking(),
            'JournalNlmUniqueID'    =>     $this->getJournalNlmUniqueID(),
            'Pagination'            =>     $this->getPagination(),
            'ArticleTitle'          =>     $this->getArticleTitle(),
            'AbstractText'          =>     $this->getAbstractText(),
            'Affiliation'           =>     $this->getAffiliation(),
            'Authors'               =>     $this->getAuthors(),
            'Keywords'              =>     $this->getKeywords(),
            'Doi'                   =>     $this->getDoi(),
            'Pii'                   =>     $this->getPii(),
            'CopyrightInformation'  =>     $this->getCopyRightInformation()
        );
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
    * Loop through authors, return Lastname First Initial
    * @return array The list of authors
    */
    public function getAuthors()
    {
        $authors = array();
        if (isset($this->articlexml->AuthorList)) {
            foreach ($this->articlexml->AuthorList->Author as $authorxml) {
                $author = [];
                $author['lastname'] = (string) $authorxml->LastName;
                $author['firstname'] = (string) $authorxml->ForeName;
                $author['initials'] = (string) $authorxml->Initials;
                $author['affiliation'] = (string) $authorxml->AffiliationInfo->Affiliation;

                $authors[] = $author;
            }
        }

        return $authors;
    }

    /**
    * Loop through KeywordList, return Lastname First Initial
    * @return array The list of Keywords
    */
    public function getKeywords()
    {
        $keywords = array();
        if (isset($this->xml->KeywordList)) {
            foreach ($this->xml->KeywordList->Keyword as $kw) {
                $keywords[] = (string) $kw;
            }
        }
        return $keywords;
    }

    public function getPubMedId()
    {
        return $this->pmid;
    }

    /**
    * @return string
    */
    public function getDoi()
    {
        foreach ($this->articlexml->ELocationID as $elocation_id) {
            if($elocation_id['EIdType'] == 'doi'){
                return (string) $elocation_id;
            }
        }
    }

    /**
    * @return string
    */
    public function getPii()
    {
        foreach ($this->articlexml->ELocationID as $elocation_id) {
            if($elocation_id['EIdType'] == 'pii'){
                return (string) $elocation_id;
            }
        }
    }

    /**
    * Get the volume from the SimpleXMLElement
    * @return string Journal Volume Number
    */
    public function getVolume()
    {
        return (string) $this->articlexml->Journal->JournalIssue->Volume;
    }

    /**
    * Get the JournalIssue from the SimpleXMLElement
    * @return string JournalIssue
    */
    public function getIssue()
    {
        return (string) $this->articlexml->Journal->JournalIssue->Issue;
    }

    /**
    * Get the PubYear from the SimpleXMLElement
    * @return string PubYear
    */
    public function getPubYear()
    {
        return (string) $this->articlexml->Journal->JournalIssue->PubDate->Year;
    }

    /**
    * Get the PubMonth from the SimpleXMLElement
    * @return string PubMonth
    */
    public function getPubMonth()
    {
        return (string) $this->articlexml->Journal->JournalIssue->PubDate->Month;
    }

    /**
    * Get the PubDay from the SimpleXMLElement
    * @return string PubDay
    */
    public function getPubDay()
    {
        return (string) $this->articlexml->Journal->JournalIssue->PubDate->Day;
    }

    /**
    * Get the ISSN from the SimpleXMLElement
    * @return string Journal ISSN
    */
    public function getISSN()
    {
        return (string) $this->articlexml->Journal->ISSN;
    }

    /**
    * Get the Journal Title from the SimpleXMLElement
    * @return string Journal Title
    */
    public function getJournalTitle()
    {
        return (string) $this->articlexml->Journal->Title;
    }

    /**
    * Get the Journal ISOAbbreviation from the SimpleXMLElement
    * @return string Journal Title
    */
    public function getJournalTitleAbbre()
    {
        return (string) $this->articlexml->Journal->ISOAbbreviation;
    }

    /**
    * Get the Country from the SimpleXMLElement
    * @return string ISOAbbreviation
    */
    public function getJournalCountry()
    {
        return (string) $this->xml->MedlineJournalInfo->Country;
    }

    /**
    * Get the NlmUniqueID from the SimpleXMLElement
    * @return string ISOAbbreviation
    */
    public function getJournalNlmUniqueID()
    {
        return (string) $this->xml->MedlineJournalInfo->NlmUniqueID;
    }

    /**
    * Get the ISSNLinking from the SimpleXMLElement
    * @return string ISOAbbreviation
    */
    public function getJournalISSNLinking()
    {
        return (string) $this->xml->MedlineJournalInfo->ISSNLinking;
    }

    /**
    * Get the Pagination from the SimpleXMLElement
    * @return string Pagination
    */
    public function getPagination()
    {
        return (string) $this->articlexml->Pagination->MedlinePgn;
    }

    /**
    * Get the ArticleTitle from the SimpleXMLElement
    * @return string ArticleTitle
    */
    public function getArticleTitle()
    {
        return (string) $this->articlexml->ArticleTitle;
    }

    /**
    * Get the AbstractText from the SimpleXMLElement
    * @return string AbstractText
    */
    public function getAbstractText()
    {
        return (string) $this->articlexml->Abstract->AbstractText;
    }

    /**
    * Get the CopyrightInformation from the SimpleXMLElement
    * @return string CopyrightInformation
    */

    public function getCopyRightInformation()
    {
        return (string) $this->articlexml->Abstract->CopyrightInformation;
    }
}
