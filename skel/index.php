<?php
include_once 'lib/Controller.class.php';
include_once dirname(__FILE__).'/../lib/addendum/annotations.php';


if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
    $server = new SoapServer('http://pws.local.net/?wsdl');
    $server->setClass('Controller');
    ob_start();
    $server->handle();
    $response = ob_get_contents();
    ob_clean();
    echo $response;
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
            $schema->setAttributeNode(new DOMAttr('targetNamespace', 'http://skel/schemas/api')); 
            $schema->setAttributeNode(new DOMAttr('elementFormDefault', 'qualified')); 
            $schema->setAttributeNode(new DOMAttr('xmlns:tns', 'http://skel/schemas/api')); 
            $schema->setAttributeNode(new DOMAttr('xmlns:base', 'http://skel/schemas/basetypes')); 

            $import = $doc->createElement('import');
            $import->setAttributeNode(new DOMAttr('namespace', 'http://skel/schemas/basetypes'));
            $import->setAttributeNode(new DOMAttr('schemaLocation', './basetypes.xsd'));
            $schema->appendChild($import);

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
                        if($attrName == 'type')
                        {
                            $attrVal = (strtoupper($attrVal[0]) == $attrVal[0]) ?
                                'base:'.$attrVal : $attrVal;
                        }
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
            $defs->setAttributeNode(new DOMAttr('xmlns:tns', 'http://skel/schemas/api'));
            $defs->setAttributeNode(new DOMAttr('targetNamespace', 'http://skel/schemas/api'));
            $types = $doc->createElement('wsdl:types');
            $schema = $doc->createElementNS('http://www.w3.org/2001/XMLSchema', 'xsd:schema');
            $schema->setAttributeNode(new DOMAttr('targetNamespace', 'http://skel/schemas/api')); 
            $include = $doc->createElement('xsd:include');
            $include->setAttributeNode(new DOMAttr('schemaLocation', 'http://pws.local.net/?api'));
            $schema->appendChild($include);
            $types->appendChild($schema);
            $defs->appendChild($types); 
            $refl = new ReflectionAnnotatedClass('Controller'); 
            $methods = $refl->getMethods();
            $portType = $doc->createElement('wsdl:portType');
            $portType->setAttributeNode(new DOMAttr('name', 'skel'));
            $binding =  $doc->createElement('wsdl:binding');
            $binding->setAttributeNode(new DOMAttr('name', 'skelSOAP'));
            $binding->setAttributeNode(new DOMAttr('type', 'tns:skel'));
            $soapbinding = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:binding'); 
            $soapbinding->setAttributeNode(new DOMAttr('style', 'document'));
            $soapbinding->setAttributeNode(new DOMAttr('transport', 'http://schemas.xmlsoap.org/soap/http'));
            $binding->appendChild($soapbinding);
            foreach($methods as $method)
            {

                $message = $doc->createElement('wsdl:message');
                $message->setAttributeNode(new DOMAttr('name', $method->name.'Input'));
                $part = $doc->createElement('wsdl:part');
                $part->setAttributeNode(new DOMAttr('element', 'tns:'.$method->name.'Request'));
                $part->setAttributeNode(new DOMAttr('name', strtolower($method->name)));
                $message->appendChild($part);
                $defs->appendChild($message);


                $message = $doc->createElement('wsdl:message');
                $message->setAttributeNode(new DOMAttr('name', $method->name.'Output'));
                $part = $doc->createElement('wsdl:part');
                $part->setAttributeNode(new DOMAttr('element', 'tns:'.$method->name.'Response'));
                $part->setAttributeNode(new DOMAttr('name', strtolower($method->name)));
                $message->appendChild($part);
                $defs->appendChild($message);

                $operation  = $doc->createElement('wsdl:operation');
                $operation->setAttributeNode(new DOMAttr('name', $method->name)); 
                $input = $doc->createElement('wsdl:input');
                $input->setAttributeNode(new DOMAttr('message','tns:'.$method->name.'Input'));
                $output =$doc->createElement('wsdl:output');
                $output->setAttributeNode(new DOMAttr('message','tns:'.$method->name.'Output'));
                $operation->appendChild($input);
                $operation->appendChild($output);
                $portType->appendChild($operation);


                $operation = $doc->createElement('wsdl:operation');
                $operation->setAttributeNode(new DOMAttr('name', $method->name));
                $soapoperation =$doc->createElement('soap:operation'); 
                $soapoperation->setAttributeNode(new DOMAttr('soapAction','http://skel/schemas/api/'.$method->name));
                $operation->appendChild($soapoperation);
                $input = $doc->createElement('wsdl:input');
                $output =$doc->createElement('wsdl:output');
                $soapbody = $doc->createElement('soap:body');
                $soapbody->setAttributeNode(new DOMAttr('use','literal'));
                $input->appendChild($soapbody);
                $output->appendChild(clone $soapbody);
                $operation->appendChild($input);
                $operation->appendChild($output);


                
                $binding->appendChild($operation);


            }
            $defs->appendChild($portType);
            $defs->appendChild($binding);

            $service = $doc->createElement('wsdl:service');
            $service->setAttributeNode(new DOMAttr('name', 'skel'));
            $port =  $doc->createElement('wsdl:port');

            $port->setAttributeNode(new DOMAttr('binding', 'tns:skelSOAP'));
            $port->setAttributeNode(new DOMAttr('name', 'skelSOAP'));
            $soapaddress = $doc->createElement('soap:address');
            $soapaddress->setAttributeNode(new DOMAttr('location', 'http://pws.local.net/'));
            $port->appendChild($soapaddress);
            $service->appendChild($port);
            $defs->appendChild($service);


            
            $doc->appendChild($defs);

            echo $doc->saveXML();
        }
    }
    
}

