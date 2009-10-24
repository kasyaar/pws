<?php
include_once 'lib/Controller.class.php';
include_once dirname(__FILE__).'/../lib/addendum/annotations.php';

//$server = new SoapServer('xmlapi7.wsdl', array('classmap'=>$classmap));
//$server->setClass('Controller');

if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
    
}
else
{
    if((isset($_GET['api']) && isset($_GET['wsdl'])) || empty($_GET))
    {
        echo 'Wrong request!!!';
    }else{

        if ( isset($_GET['api'] ))
        {    
            header('Content-Type: xsd'); 
            $doc = new DOMDocument('1.0', 'utf-8');
            $schema = $doc->createElementNS('http://www.w3.org/2001/XMLSchema', 'schema');
            $schema->setAttributeNode(new DOMAttr('targetNamespace', 'http://starfish/schemas/xmlapi7')); 
            $schema->setAttributeNode(new DOMAttr('elementFormDefault', 'qualified')); 
            $schema->setAttributeNode(new DOMAttr('xmlns:tns', 'http://starfish/schemas/xmlapi7')); 
            $schema->setAttributeNode(new DOMAttr('xmlns:base', 'http://starfish/schemas/base')); 

            $refl = new ReflectionAnnotatedClass('Controller'); 

            $methods = $refl->getMethods();
            foreach( $methods as $method )
            {
                $requestElement = $doc->createElement('element');
                $requestElement->setAttributeNode(new DOMAttr('name', $method->name.'Request'));
                $complexType = $doc->createElement('complexType');
                $sequence = $doc->createElement('sequence');
                foreach( $method->getAnnotation('Request')->value as $elementName => $elementAttrs )
                {
                    $element = $doc->createElement('element');
                    $element->setAttributeNode(new DOMAttr('name', $elementName));
                    foreach($elementAttrs as $attrName => $attrVal)
                    {
                        $element->setAttributeNode(new DOMAttr($attrName, $attrVal));
                    }
                    $sequence->appendChild($element);
                }
                
                $complexType->appendChild($sequence);
                $requestElement->appendChild($complexType);

                $schema->appendChild($requestElement);
                
                $responseElement = $doc->createElement('element');
                $responseElement->setAttributeNode(new DOMAttr('name', $method->name.'Response'));
                $complexType = $doc->createElement('complexType');
                $sequence = $doc->createElement('sequence');
                foreach( $method->getAnnotation('Response')->value as $elementName => $elementAttrs )
                {
                    $element = $doc->createElement('element');
                    $element->setAttributeNode(new DOMAttr('name', $elementName));
                    foreach($elementAttrs as $attrName => $attrVal)
                    {
                        $element->setAttributeNode(new DOMAttr($attrName, $attrVal));
                    }
                    $sequence->appendChild($element);
                }
                
                $complexType->appendChild($sequence);
                $responseElement->appendChild($complexType);

                $schema->appendChild($responseElement);
            }
            

            $doc->appendChild($schema);
            echo $doc->saveXML();
        }
        elseif( isset($_GET['wsdl']))
        {
            header('Content-Type: wsdl'); 
            $doc = new DOMDocument('1.0', 'utf-8');
            $defs = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/', 'wsdl:definitions');
            $types = $doc->createElement('wsdl:types');
            $schema = $doc->createElementNS('http://www.w3.org/2001/XMLSchema', 'xsd:schema');
            $schema->setAttributeNode(new DOMAttr('targetNamespace', 'http://starfish/schemas/xmlapi7')); 
            $include = $doc->createElement('xsd:include');
            $include->setAttributeNode(new DOMAttr('schemaLocation', 'http://'.$_SERVER['HTTP_HOST'].'/?api'));
            $schema->appendChild($include);
            $types->appendChild($schema);
            $defs->appendChild($types); 
            $refl = new ReflectionAnnotatedClass('Controller'); 
            $methods = $refl->getMethods();
            $portType = $doc->createElement('wsdl:portType');
            $portType->setAttributeNode(new DOMAttr('name', '@PROJECT-NAME@'));
            foreach($methods as $method)
            {

                $message = $doc->createElement('wsdl:message');
                $message->setAttributeNode(new DOMAttr('name', $method->name.'Input'));
                $part = $doc->createElement('wsdl:part');
                $part->setAttributeNode(new DOMAttr('element', 'tns:'.$method->name.'Request'));
                $part->setAttributeNode(new DOMAttr('name', strtolower($method->name)));
                $message->appendChild($part);
                $defs->appendChild($message);
            }
            $defs->appendChild($portType);
            $doc->appendChild($defs);

            echo $doc->saveXML();
        }
    }
    
}

