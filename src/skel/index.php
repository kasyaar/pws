<?php
include_once 'lib/Controller.class.php';
//include_once dirname(__FILE__).'/../lib/addendum/annotations.php';
include_once '@PWS-LIBS@/lib/addendum/annotations.php';
class WSDLDocument extends DOMDocument{
    private
        $annotatedController,
        $definitions,
        $schema,
        $currentMethod,
        $namePostfixes = array(
            'Request' => 'Input',
            'Response' => 'Output'
        )
        ;
    function __construct(ReflectionAnnotatedClass $annotatedController)
    {
        parent::__construct('1.0', 'utf-8');
        $this->annotatedController = $annotatedController;
    }
    public function generate ()
    {
        
    }
    public function generateTypes ($defs)
    {
        $schema = $this->createElementNS('http://www.w3.org/2001/XMLSchema', 'xsd:schema');
        $schema->setAttribute('targetNamespace', 'http://@PROJECTNAME@/schemas/api'); 
        $schema->setAttribute('targetNamespace', 'http://@PROJECTNAME@/schemas/api'); 
        $schema->setAttribute('elementFormDefault', 'qualified'); 
        $schema->setAttribute('xmlns:tns', 'http://@PROJECTNAME@/schemas/api'); 
        $schema->setAttribute('xmlns:base', 'http://@PROJECTNAME@/schemas/basetypes'); 

        $import = $this->createElement('xsd:import');
        $import->setAttribute('namespace', 'http://@PROJECTNAME@/schemas/basetypes');
        $import->setAttribute('schemaLocation', './basetypes.xsd');
        $schema->appendChild($import);
        $methods = $this->annotatedController->getMethods();
        foreach( $methods as $method )
        { 
            $this->currentMethod = $method;
            $schema->appendChild($this->_generateRElement('Request'));
            $schema->appendChild($this->_generateRElement('Response'));
            $defs->appendChild($this->_generateWSDLMessage('Request'));
            $defs->appendChild($this->_generateWSDLMessage('Response'));
        }
        $types = $this->createElement('wsdl:types');
        $types->appendChild($schema);
        $defs->appendChild($types); 

    }
    private function _generateWSDLMessage ($type = 'Request')
    {
                $message = $this->createElement('wsdl:message');
                $message->setAttribute('name', $this->currentMethod->name.$this->namePostfixes[$type]);
                $part = $this->createElement('wsdl:part');
                $part->setAttribute('element', 'tns:'.$this->currentMethod->name.$type);
                $part->setAttribute('name', strtolower($this->currentMethod->name));
                $message->appendChild($part);
                return $message;
    }
    
    private function _generateRElement ($type = 'Request')
    {
        $rElement = $this->createElement('xsd:element');
        $rElement->setAttribute('name', $this->currentMethod->name.$type);
        $complexType = $this->createElement('xsd:complexType');
        $sequence = $this->createElement('xsd:sequence');
        foreach( $this->currentMethod->getAnnotation($type)->value as $elementName => $elementAttrs )
        {
            $element = $this->createElement('xsd:element');
            $element->setAttribute('name', $elementName);
            $this->_setAttributes($element, $elementAttrs);
            $sequence->appendChild($element);
        }
        $complexType->appendChild($sequence);
        $rElement->appendChild($complexType);
        return $rElement; 
    }
    
    private function _setAttributes (&$element, $attrs)
    {
        foreach($attrs as $attrName => $attrVal)
        {
            if($attrName == 'type')
            {
                $attrVal = (strtoupper($attrVal[0]) == $attrVal[0]) ?
                    'base:'.$attrVal : 'xsd:'.$attrVal;
            }
            $element->setAttribute($attrName, $attrVal);
        }

    }
}

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
    }else{
        if( isset($_GET['wsdl']))
        {
            header('Content-Type: wsdl'); 
            $annotatedController = new ReflectionAnnotatedClass('Controller'); 
            $methods = $annotatedController->getMethods();
            $doc = new WSDLDocument($annotatedController);
            $defs = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/', 'wsdl:definitions');
            $defs->setAttribute('xmlns:tns', 'http://@PROJECTNAME@/schemas/api');
            $defs->setAttribute('targetNamespace', 'http://@PROJECTNAME@/schemas/api');

            $doc->generateTypes($defs);


            $portType = $doc->createElement('wsdl:portType');
            $portType->setAttribute('name', '@PROJECTNAME@');
            $binding =  $doc->createElement('wsdl:binding');
            $binding->setAttribute('name', 'skelSOAP');
            $binding->setAttribute('type', 'tns:@PROJECTNAME@');
            $soapbinding = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:binding'); 
            $soapbinding->setAttribute('style', 'document');
            $soapbinding->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
            $binding->appendChild($soapbinding);
            foreach($methods as $method)
            {


                $operation  = $doc->createElement('wsdl:operation');
                $operation->setAttribute('name', $method->name); 

                $input = $doc->createElement('wsdl:input');
                $input->setAttribute('message','tns:'.$method->name.'Input');

                $output =$doc->createElement('wsdl:output');
                $output->setAttribute('message','tns:'.$method->name.'Output');

                $operation->appendChild($input);
                $operation->appendChild($output);
                $portType->appendChild($operation);


                $operation = $doc->createElement('wsdl:operation');
                $operation->setAttribute('name', $method->name);
                $soapoperation =$doc->createElement('soap:operation'); 
                $soapoperation->setAttribute('soapAction','http://@PROJECTNAME@/schemas/api/'.$method->name);
                $operation->appendChild($soapoperation);
                $input = $doc->createElement('wsdl:input');
                $output =$doc->createElement('wsdl:output');
                $soapbody = $doc->createElement('soap:body');
                $soapbody->setAttribute('use','literal');
                $input->appendChild($soapbody);
                $output->appendChild(clone $soapbody);
                $operation->appendChild($input);
                $operation->appendChild($output);


                
                $binding->appendChild($operation);


            }
            $defs->appendChild($portType);
            $defs->appendChild($binding);

            $service = $doc->createElement('wsdl:service');
            $service->setAttribute('name', '@PROJECTNAME@');
            $port =  $doc->createElement('wsdl:port');

            $port->setAttribute('binding', 'tns:skelSOAP');
            $port->setAttribute('name', 'skelSOAP');
            $soapaddress = $doc->createElement('soap:address');
            list($path) = explode('?', $_SERVER['REQUEST_URI']);
            $location = 'http://'.$_SERVER['SERVER_NAME'].$path;
            $soapaddress->setAttribute('location', $location);
            $port->appendChild($soapaddress);
            $service->appendChild($port);
            $defs->appendChild($service);
            $doc->appendChild($defs);

            echo $doc->saveXML();
        }
    }
    
}

