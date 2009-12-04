<?php
include_once 'lib/Controller.class.php';
//include_once dirname(__FILE__).'/../lib/addendum/annotations.php';
include_once '@PWS-LIBS@/lib/addendum/annotations.php';
class WSDLDocument extends DOMDocument{
    private
        $annotatedController,
        $definitions,
        $schema,
        $types,
        $service,
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
        $this->_createTypesSection();
        $this->_createPortTypeSection();
        $this->_createServiceSection();
    }
    private function _createServiceSection ()
    {
        $this->service = $this->createElement('wsdl:service');
        $this->service->setAttribute('name', '@PROJECTNAME@');
        $port =  $this->createElement('wsdl:port');

        $port->setAttribute('binding', 'tns:skelSOAP');
        $port->setAttribute('name', 'skelSOAP');
        $soapaddress = $this->createElement('soap:address');
        list($path) = explode('?', $_SERVER['REQUEST_URI']);
        $location = 'http://'.$_SERVER['SERVER_NAME'].$path;
        $soapaddress->setAttribute('location', $location);
        $port->appendChild($soapaddress);
        $this->service->appendChild($port);

    }
    
    public function generate ($defs)
    {
        $methods = $this->annotatedController->getMethods();
        foreach( $methods as $method )
        { 
            $this->currentMethod = $method;
            $this->schema->appendChild($this->_generateRElement('Request'));
            $this->schema->appendChild($this->_generateRElement('Response'));
            $defs->appendChild($this->_generateWSDLMessage('Request'));
            $defs->appendChild($this->_generateWSDLMessage('Response'));
            $this->portType->appendChild($this->_generateOperation());
        }
        $defs->appendChild($this->types); 
        $defs->appendChild($this->portType);
        $defs->appendChild($this->service);

    }
    private function _generateOperation ()
    {
        $operation  = $this->createElement('wsdl:operation');
        $operation->setAttribute('name', $this->currentMethod->name); 
        $operation->appendChild($this->_generateOperationPart('Request'));
        $operation->appendChild($this->_generateOperationPart('Response'));
        return $operation;

    }
    private function _generateOperationPart ($type = 'Request')
    {
        $opPart = $this->createElement('wsdl:'.strtolower($this->namePostfixes[$type]));
        $opPart->setAttribute('message','tns:'.$this->currentMethod->name.$this->namePostfixes[$type]);
        return $opPart;
    }
    

    private function _createTypesSection ()
    {
        $this->schema = $this->createElementNS('http://www.w3.org/2001/XMLSchema', 'xsd:schema');
        $this->schema->setAttribute('targetNamespace', 'http://@PROJECTNAME@/schemas/api'); 
        $this->schema->setAttribute('targetNamespace', 'http://@PROJECTNAME@/schemas/api'); 
        $this->schema->setAttribute('elementFormDefault', 'qualified'); 
        $this->schema->setAttribute('xmlns:tns', 'http://@PROJECTNAME@/schemas/api'); 
        $this->schema->setAttribute('xmlns:base', 'http://@PROJECTNAME@/schemas/basetypes'); 

        $import = $this->createElement('xsd:import');
        $import->setAttribute('namespace', 'http://@PROJECTNAME@/schemas/basetypes');
        $import->setAttribute('schemaLocation', './basetypes.xsd');
        $this->schema->appendChild($import);
        $this->types = $this->createElement('wsdl:types');
        $this->types->appendChild($this->schema);

    }


    private function _createPortTypeSection()
    {
        $this->portType = $this->createElement('wsdl:portType');
        $this->portType->setAttribute('name', '@PROJECTNAME@');
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
    list($path) = explode('?', $_SERVER['REQUEST_URI']);
    $server = new SoapServer('http://'.$_SERVER['SERVER_NAME'].$path.'/?wsdl');
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

            $doc->generate($defs);


            $binding =  $doc->createElement('wsdl:binding');
            $binding->setAttribute('name', 'skelSOAP');
            $binding->setAttribute('type', 'tns:@PROJECTNAME@');
            $soapbinding = $doc->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:binding'); 
            $soapbinding->setAttribute('style', 'document');
            $soapbinding->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
            $binding->appendChild($soapbinding);
            foreach($methods as $method)
            {
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
            $defs->appendChild($binding);

            $doc->appendChild($defs);

            echo $doc->saveXML();
        }
    }
    
}

