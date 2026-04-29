<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 10:58
 */
class DeploymentExtraction implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    public $proceduresXmlObj = null;
    public $rootXmlObj = null;
    private $version = 0;
    private $description = '';
    private $type = '';

    private function getXML()
    {
        $rootXmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
                <deployment>
                    <version>' . $this->version . '</version>
                    <requiredVersions/>
                    <description>' . $this->description . '</description>
                    <type>' . $this->type . '</type>
                </deployment>';
        $this->rootXmlObj = new SimpleXMLElement($rootXmlString);

        $this->proceduresXmlObj = new SimpleXMLElement('<?xml version="1.0"?><procedures></procedures>');
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setArguments($type = null, $arguments = null)
    {
        if (!$this->proceduresXmlObj) {
            $this->getXML();
        }

        $procedureClassName = $type . "ExtractionProcedure";
        if (class_exists($procedureClassName)) {
            $procedureObject = new $procedureClassName($this->proceduresXmlObj);
            if ($procedureObject instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($procedureObject);
            }
            if ($arguments) {
                $procedureObject->setProcedureArguments($arguments);
            }
            // get the procedure xml obj for that procedure
            $xmlObj = $procedureObject->run();
            // merge procedure xml
            $this->_simplexml_merge($this->proceduresXmlObj, $xmlObj);
        }
    }

    private function _simplexml_merge(SimpleXMLElement &$xml1, SimpleXMLElement $xml2)
    {
        // convert SimpleXML objects into DOM ones
        $dom1 = new DomDocument();
        $dom2 = new DomDocument();
        $dom1->loadXML($xml1->asXML());
        $dom2->loadXML($xml2->asXML());

        // pull all child elements of second XML
        $xpath = new domXPath($dom2);
        $xpathQuery = $xpath->query('/*/*');
        for ($i = 0; $i < $xpathQuery->length; $i++) {
            // and pump them into first one
            $dom1->documentElement->appendChild($dom1->importNode($xpathQuery->item($i), true));
        }
        $xml1 = simplexml_import_dom($dom1);
    }

    public function execute()
    {
        header("Content-type: text/xml");
        $proceduresXmlContent = "<?xml version='1.0'?><deployment>" . substr($this->proceduresXmlObj->asXML(), 21) . "</deployment>";
        $this->proceduresXmlObj = new SimpleXMLElement($proceduresXmlContent);
        $this->_simplexml_merge($this->rootXmlObj, $this->proceduresXmlObj);
        echo $this->rootXmlObj->asXML();
    }
}