<?php
namespace Alaxos\Lib;

use DOMDocument;
use DOMXPath;
use Alaxos\Lib\DebugTool;

/**
 *
 * @author rodn
 *
 */
class XmlTool
{
    /**
     * Returns the first $subnode occurence of a $node.
     * The subnode is identified by its name.
     *
     * @param DOMNode $node
     * @param string $subnode_name
     * @return DOMNode
     */
    public static function getFirstElementByTagName($node, $subnode_name)
    {
        $nodes = $node->getElementsByTagName($subnode_name);
        if ($nodes->length > 0)
        {
            return $nodes->item(0);
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns the first $subnode occurence value of a $node
     * The subnode is identified by its name.
     *
     * @param DOMNode $node
     * @param string $subnode_name
     * @return string
     */
    public static function getFirstElementValueByTagName($node, $subnode_name)
    {
        $node = XMLTool :: getFirstElementByTagName($node, $subnode_name);
        if (isset($node))
        {
            return $node->nodeValue;
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns the first element found by the XPATH query.
     * The first subnode is searched by using a XPATH query relative to the document containing the $node.
     *
     * @param DOMNode $node
     * @param string $xpath_query
     * @return DOMNode
     */
    public static function getFirstElementByXpath($node, $xpath_query, $namespaces = array())
    {
        if(is_a($node, 'DOMDocument'))
        {
            $xpath = new DOMXPath($node);
        }
        elseif(is_a($node, 'DOMNode'))
        {
            $xpath = new DOMXPath($node->ownerDocument);
        }
        else
        {
            return null;
        }
        
        foreach($namespaces as $prefix => $namespaceURI)
        {
            $xpath->registerNamespace($prefix, $namespaceURI);
        }
        
        $node_list = $xpath->query($xpath_query, $node);
        
        if ($node_list->length > 0)
        {
            return $node_list->item(0);
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns the value of the first element found by the XPATH query.
     * The first subnode is searched by using a XPATH query relative to the document containing the $node.
     *
     * @param DOMNode $node
     * @param string $xpath_query
     * @return string
     */
    public static function getFirstElementValueByXpath($node, $xpath_query, $namespaces = array())
    {
        $node = XMLTool :: getFirstElementByXpath($node, $xpath_query, $namespaces);
        
        if (isset($node))
        {
            return $node->nodeValue;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Returns all the values of a list of nodes under a given node.
     * The subnodes are searched by using a XPATH query relative to the document containing the $node.
     *
     * @param DOMNode $node
     * @param string $xpath_query
     * @return array of string
     */
    public static function getAllValuesByXpath($node, $xpath_query, $namespaces = array())
    {
        $node_list = XMLTool :: getAllElementsByXpath($node, $xpath_query, $namespaces);
        
        $values = array();
        
        if (isset($node_list))
        {
            foreach ($node_list as $node_found)
            {
                $values[] = $node_found->nodeValue;
            }
        }
        
        return $values;
    }
    
    /**
     * Returns a nodes list under a given node.
     * The subnodes are searched by using a XPATH query relative to the document containing the $node.
     *
     * @param DOMNode $node
     * @param string $xpath_query
     * @return DOMNodeList
     */
    public static function getAllElementsByXpath($node, $xpath_query, $namespaces = array())
    {
        if(is_a($node, 'DOMDocument'))
        {
            $xpath = new DOMXPath($node);
        }
        elseif(is_a($node, 'DOMNode'))
        {
            $xpath = new DOMXPath($node->ownerDocument);
        }
        else
        {
            return null;
        }
        
        foreach($namespaces as $prefix => $namespaceURI)
        {
            $xpath->registerNamespace($prefix, $namespaceURI);
        }
        
        $node_list = $xpath->query($xpath_query, $node);
        
        return $node_list;
    }

    /**
     * Get an attribute value, or the default value if the attribute is null or empty
     *
     * @param DOMNode $node The node to search the attribute on
     * @param string $attribute_name The name of the attribute to get the value from
     * @param string $default_value A default value if the attribute doesn't exist or is empty
     */
    public static function getAttribute($node, $attribute_name, $default_value = null)
    {
        $value = $node->getAttribute($attribute_name);
        
        if (! isset($value) || strlen($value) == 0)
        {
            $value = $default_value;
        }
        
        return $value;
    }

    /**
     * Delete all the nodes from a DOMDocument that are found with the given xpath query
     *
     * @param DOMNode $node The DOMNode from which nodes must be removed
     * @param string $xpath_query
     */
    public static function deleteElementsByXpath($node, $xpath_query, $namespaces = array())
    {
        if(is_a($node, 'DOMDocument'))
        {
            $xpath = new DOMXPath($node);
        }
        elseif(is_a($node, 'DOMNode'))
        {
            $xpath = new DOMXPath($node->ownerDocument);
        }
        else
        {
            return false;
        }
        
        foreach($namespaces as $prefix => $namespaceURI)
        {
            $xpath->registerNamespace($prefix, $namespaceURI);
        }
        
        $node_list = $xpath->query($xpath_query, $node);
        
        if($node_list->length > 0)
        {
            foreach($node_list as $node_to_delete)
            {
                if(isset($node_to_delete->parentNode))
                {
                    $node_to_delete->parentNode->removeChild($node_to_delete);
                }
            }
        }
    }
    
    /**
     *
     * @param string $text
     * @return string
     */
    public static function cleanText($text)
    {
        $text = str_replace(" & "," &amp; ", $text);
        
        return $text;
    }
    
    /**
     * Not all UTF-8 chars are allowed in XML. This function replaces invalid chars by a valid UTF-8 char representing a question mark
     *
     * @param string $text
     * @return string
     */
    public static function stripInvalidUTF8Chars($text)
    {
        $ret = '';
        $current;
        if (empty($text))
        {
            return $ret;
        }
        
        $length = strlen($text);
        for ($i=0; $i < $length; $i++)
        {
            $current = ord($text{$i});
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF)))
            {
                $ret .= chr($current);
            }
            else
            {
                $ret .= '⍰';
            }
        }
        
        return $ret;
    }
        
    /**
     * Replace the content of a node by a TextNode containing the given text
     * 
     * Note:
     *         compared to using $node->nodeValue, this method automatically encodes the text
     *         to support characters such as ampersand
     * 
     * @param DOMDocument $dom_document
     * @param DOMNode $element
     * @param string $text
     * @return DOMNode the newly created TextNode
     */
    public static function setNodeText($dom_document, $node, $text)
    {
       /*
        * Clear existing child nodes
        */
        foreach($node->childNodes as $child_node)
        {
            $node->removeChild($child_node);
        }
        
       /*
        * Add a new child TextNode
        */
        return $node->appendChild($dom_document->createTextNode($text));
    }
    
    /**********************************************************************************/
    
    /**
     * Test XmlTool functions
     */
    public static function test()
    {
        $xml = '<root>
                    <document type="thesis">
                        <title>Document 1</title>
                        <year>1995</year>
                    </document>
                    <document type="book">
                        <title>Document 2</title>
                        <year>1997</year>
                    </document>
                    <document type="book">
                        <title>Document 3</title>
                        <year>2010</year>
                    </document>
                </root>';
        
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML($xml);
        
        DebugTool::show($doc);
        
        /****/
        
        $xpath  = '/root/document';
        $result = XmlTool::getFirstElementByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<document type="thesis">
  <title>Document 1</title>
  <year>1995</year>
</document>');
        
        $xpath  = '/root/document[@type="book"]';
        $result = XmlTool::getFirstElementByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<document type="book">
  <title>Document 2</title>
  <year>1997</year>
</document>');
        
        $xpath  = '/root/document[year="2010"]';
        $result = XmlTool::getFirstElementByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<document type="book">
  <title>Document 3</title>
  <year>2010</year>
</document>');
        
        /****/
        
        $xpath  = '/root/document';
        $result = XmlTool::getAllElementsByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllElementsByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<document type="thesis">
  <title>Document 1</title>
  <year>1995</year>
</document>
<document type="book">
  <title>Document 2</title>
  <year>1997</year>
</document>
<document type="book">
  <title>Document 3</title>
  <year>2010</year>
</document>');
        
        $xpath  = '/root/document/title';
        $result = XmlTool::getAllElementsByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllElementsByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<title>Document 1</title>
<title>Document 2</title>
<title>Document 3</title>');
        
        $xpath  = '/root/document[@type="book"]';
        $result = XmlTool::getAllElementsByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllElementsByXpath($doc, \'' . $xpath . '\')', $result, $doc, '<document type="book">
  <title>Document 2</title>
  <year>1997</year>
</document>
<document type="book">
  <title>Document 3</title>
  <year>2010</year>
</document>');
        
        /****/
        
        $result = XmlTool::getFirstElementByTagName($doc, 'document');
        XmlTool::checkTestResult('XmlTool::getFirstElementByTagName($doc, \'document\')', $result, $doc, '<document type="thesis">
  <title>Document 1</title>
  <year>1995</year>
</document>');
        
        $result = XmlTool::getFirstElementByTagName($doc, 'title');
        XmlTool::checkTestResult('XmlTool::getFirstElementByTagName($doc, \'title\')', $result, $doc, '<title>Document 1</title>');
        
        /****/
        
        $xpath  = '/root/document/title';
        $result = XmlTool::getFirstElementValueByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementValueByXpath($doc, \'' . $xpath . '\')', $result, $doc, 'Document 1');
        
        $xpath  = '/root/document[@type="book"]/year';
        $result = XmlTool::getFirstElementValueByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementValueByXpath($doc, \'' . $xpath . '\')', $result, $doc, '1997');
        
        $xpath  = '/root/document[year="2010"]/title';
        $result = XmlTool::getFirstElementValueByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementValueByXpath($doc, \'' . $xpath . '\')', $result, $doc, 'Document 3');
        
        /****/
        
        $xpath  = '/root/document/title';
        $result = XmlTool::getAllValuesByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllValuesByXpath($doc, \'' . $xpath . '\')', $result, $doc, array('Document 1', 'Document 2', 'Document 3'));
        
        $xpath  = '/root/document[@type="book"]/title';
        $result = XmlTool::getAllValuesByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllValuesByXpath($doc, \'' . $xpath . '\')', $result, $doc, array('Document 2', 'Document 3'));
        
        $xpath  = '/root/document[year="2010"]/title';
        $result = XmlTool::getAllValuesByXpath($doc, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllValuesByXpath($doc, \'' . $xpath . '\')', $result, $doc, array('Document 3'));
        
        /****/
        /****/
        /****/
        
        $document_node = XmlTool::getFirstElementByXpath($doc, '/root/document');
        
        $xpath  = './title';
        $result = XmlTool::getFirstElementByXpath($document_node, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementByXpath($document_node, \'' . $xpath . '\')', $result, $doc, '<title>Document 1</title>');
        
        $xpath  = './title';
        $result = XmlTool::getAllElementsByXpath($document_node, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllElementsByXpath($document_node, \'' . $xpath . '\')', $result, $doc, '<title>Document 1</title>');
        
        $xpath  = './title';
        $result = XmlTool::getFirstElementValueByXpath($document_node, $xpath);
        XmlTool::checkTestResult('XmlTool::getFirstElementValueByXpath($document_node, \'' . $xpath . '\')', $result, $doc, 'Document 1');
        
        $xpath  = './title';
        $result = XmlTool::getAllValuesByXpath($document_node, $xpath);
        XmlTool::checkTestResult('XmlTool::getAllValuesByXpath($document_node, \'' . $xpath . '\')', $result, $doc, array('Document 1'));
        
        /****/
        /****/
        /****/
        
        $doc_to_delete = new DOMDocument();
        $doc_to_delete->loadXML($doc->saveXML());
        $doc_to_delete->preserveWhiteSpace = false;
        $doc_to_delete->formatOutput = true;
        
        $xpath  = '/root/document/year[. = "1997"]';
        $result = XmlTool::deleteElementsByXpath($doc_to_delete, $xpath);
        XmlTool::checkTestResult('XmlTool::deleteElementsByXpath($doc_to_delete, \'' . $xpath . '\')', $doc_to_delete->documentElement, $doc_to_delete, '<root>
  <document type="thesis">
    <title>Document 1</title>
    <year>1995</year>
  </document>
  <document type="book">
    <title>Document 2</title>
    
  </document>
  <document type="book">
    <title>Document 3</title>
    <year>2010</year>
  </document>
</root>');
        
    }
    
    private static function checkTestResult($test, $result, $doc, $expected_result)
    {
        if(is_a($result, 'DOMNode'))
        {
            $output = $doc->saveXML($result);
            
            /*
             * Normalize new lines
             */
            $output          = str_replace("\r\n", "\n", $output);
            $expected_result = str_replace("\r\n", "\n", $expected_result);
            
            if($expected_result == $output)
            {
                echo '<div style="background-color:#CFF8D1;padding:10px;margin:2px;">';
            }
            else
            {
                echo '<div style="background-color:#ED766B;color:#fff;padding:10px;margin:2px;">';
            }
                
                echo '<div style="float:left;margin-right:50px;">';
                echo '<pre>';
                echo $test;
                echo '</pre>';
                echo '</div>';
                
                echo '<div style="float:left;margin-right:50px;">';
                echo '<pre>';
                echo htmlentities($expected_result);
                echo '</pre>';
                echo '</div>';
                
                echo '<div style="float:left;margin-right:50px;">';
                echo '<pre>';
                echo htmlentities($output);
                echo '</pre>';
                echo '</div>';
                
                echo '<div style="clear:both;"></div>';
                
            echo '</div>';
            
        }
        elseif(is_a($result, 'DOMNodeList'))
        {
            $outputs = array();
            foreach($result as $node)
            {
                $output    = $doc->saveXML($node);
                $output    = str_replace("\r\n", "\n", $output);
                $outputs[] = $output;
            }
            
            $output = implode("\n", $outputs);
            
            $expected_result_comp = str_replace("\r\n",   "\n", $expected_result);
            $expected_result_comp = str_replace("\n    ", "\n", $expected_result_comp);
            $expected_result_comp = str_replace("\n   ",  "\n", $expected_result_comp);
            $expected_result_comp = str_replace("\n  ",   "\n", $expected_result_comp);
            $expected_result_comp = str_replace("\n ",    "\n", $expected_result_comp);
            
            $output_comp = str_replace("\r\n",   "\n", $output);
            $output_comp = str_replace("\n    ", "\n", $output_comp);
            $output_comp = str_replace("\n   ",  "\n", $output_comp);
            $output_comp = str_replace("\n  ",   "\n", $output_comp);
            $output_comp = str_replace("\n ",    "\n", $output_comp);
            
            if($expected_result_comp == $output_comp)
            {
                echo '<div style="background-color:#CFF8D1;padding:10px;margin:2px;">';
            }
            else
            {
                echo '<div style="background-color:#ED766B;color:#fff;padding:10px;margin:2px;">';
            }
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo $test;
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo htmlentities($expected_result);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo htmlentities($output);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="clear:both;"></div>';
            
            echo '</div>';
        }
        elseif(is_string($result))
        {
            if($expected_result == $result)
            {
                echo '<div style="background-color:#CFF8D1;padding:10px;margin:2px;">';
            }
            else
            {
                echo '<div style="background-color:#ED766B;color:#fff;padding:10px;margin:2px;">';
            }
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo $test;
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo htmlentities($expected_result);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo htmlentities($result);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="clear:both;"></div>';
            
            echo '</div>';
        }
        elseif(is_array($result))
        {
            $result_str = '';
            foreach($result as $k => $v)
            {
                $result_str .= $k . '___' . $v . '-_-_-';
            }
            
            $expected_result_str = '';
            foreach($expected_result as $k => $v)
            {
                $expected_result_str .= $k . '___' . $v . '-_-_-';
            }
            
            if($expected_result_str == $result_str)
            {
                echo '<div style="background-color:#CFF8D1;padding:10px;margin:2px;">';
            }
            else
            {
                echo '<div style="background-color:#ED766B;color:#fff;padding:10px;margin:2px;">';
            }
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo $test;
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            print_r($expected_result);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="clear:both;"></div>';
            
            echo '</div>';
        }
        else
        {
            XmlTool::showTestResult($test, $result, $doc);
        }
    }
    
    private static function showTestResult($test, $result, $doc)
    {
        echo '<div style="background-color:#fff;color:#444;padding:10px;margin:2px;">';
        
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            echo $test;
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="float:left;margin-right:50px;">';
            echo '<pre>';
            if(is_a($result, 'DOMNode'))
            {
                echo htmlentities($doc->saveXML($result));
            }
            else
            {
                DebugTool::show($result);
            }
            echo '</pre>';
            echo '</div>';
            
            echo '<div style="clear:both;"></div>';
        
        echo '</div>';
    }
}

?>