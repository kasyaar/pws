<?php
include_once 'addendum/annotations.php';
class WSDLDocument extends DOMDocument{
    private
        $projectName,
        $definitions,
        $schema,
        $service,
        $binding,
        $currentMethod,
        $namePostfixes = array(
            'Request' => 'Input',
            'Response' => 'Output'
        )
        ;
    function __construct($projectName, $controllerClassName = 'Controller')
    {
        parent::__construct('1.0', 'utf-8');
        $this->projectName = $projectName;
        $this->annotatedController = new ReflectionAnnotatedClass($controllerClassName);
        $this->_createDocumentStructure();
    }
    private function _createServiceSection ()
    {
        $service = $this->createElement('wsdl:service');
        $service->setAttribute('name', $this->projectName);
        $port =  $this->createElement('wsdl:port');

        $port->setAttribute('binding', 'tns:'.$this->projectName.'SOAP');
        $port->setAttribute('name', $this->projectName.'SOAP');
        $soapaddress = $this->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:address');
        list($path) = explode('?', $_SERVER['REQUEST_URI']);
        $location = 'http://'.$_SERVER['SERVER_NAME'].$path;
        $soapaddress->setAttribute('location', $location);
        $port->appendChild($soapaddress);
        $service->appendChild($port);
        $this->definitions->appendChild($service);

    }
    private function _createBindingSection ()
    {
        $this->binding =  $this->createElement('wsdl:binding');
        $this->binding->setAttribute('name', $this->projectName.'SOAP');
        $this->binding->setAttribute('type', 'tns:'.$this->projectName);
        $soapbinding = $this->createElementNS('http://schemas.xmlsoap.org/wsdl/soap/', 'soap:binding'); 
        $soapbinding->setAttribute('style', 'document');
        $soapbinding->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
        $this->binding->appendChild($soapbinding);
        $this->definitions->appendChild($this->binding);

    }
    private function _generateBindingOperation ()
    {
        $operation = $this->createElement('wsdl:operation');
        $operation->setAttribute('name', $this->currentMethod->name);
        $soapoperation =$this->createElement('soap:operation'); 
        $soapoperation->setAttribute('soapAction','http://'.$this->projectName.'/schemas/api/'.$this->currentMethod->name);
        $operation->appendChild($soapoperation);
        $input = $this->createElement('wsdl:input');
        $output =$this->createElement('wsdl:output');
        $soapbody = $this->createElement('soap:body');
        $soapbody->setAttribute('use','literal');
        $input->appendChild($soapbody);
        $output->appendChild(clone $soapbody);
        $operation->appendChild($input);
        $operation->appendChild($output);
        return $operation;

    }
    private function _createDocumentStructure ()
    {
        $this->definitions = $this->createElementNS('http://schemas.xmlsoap.org/wsdl/', 'wsdl:definitions');
        $this->definitions->setAttribute('xmlns:tns', 'http://'.$this->projectName.'/schemas/api');
        $this->definitions->setAttribute('targetNamespace', 'http://'.$this->projectName.'/schemas/api');
        $this->appendChild($this->definitions);
        $this->_createTypesSection();
        $this->_createPortTypeSection();
        $this->_createBindingSection();
        $this->_createServiceSection();
    }
    
    
    public function generate ()
    {
        $methods = $this->annotatedController->getMethods();
        foreach( $methods as $method )
        { 
            $this->currentMethod = $method;
            $this->schema->appendChild($this->_generateRElement('Request'));
            $this->schema->appendChild($this->_generateRElement('Response'));
            $this->definitions->appendChild($this->_generateWSDLMessage('Request'));
            $this->definitions->appendChild($this->_generateWSDLMessage('Response'));
            $this->portType->appendChild($this->_generateOperation());
            $this->binding->appendChild($this->_generateBindingOperation());
        }

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
        $this->schema->setAttribute('targetNamespace', 'http://'.$this->projectName.'/schemas/api'); 
        $this->schema->setAttribute('targetNamespace', 'http://'.$this->projectName.'/schemas/api'); 
        $this->schema->setAttribute('elementFormDefault', 'qualified'); 
        $this->schema->setAttribute('xmlns:tns', 'http://'.$this->projectName.'/schemas/api'); 
        $this->schema->setAttribute('xmlns:base', 'http://'.$this->projectName.'/schemas/basetypes'); 

        $import = $this->createElement('xsd:import');
        $import->setAttribute('namespace', 'http://'.$this->projectName.'/schemas/basetypes');
        $import->setAttribute('schemaLocation', './basetypes.xsd');
        $this->schema->appendChild($import);
        $types = $this->createElement('wsdl:types');
        $types->appendChild($this->schema);
        $this->definitions->appendChild($types);

    }


    private function _createPortTypeSection()
    {
        $this->portType = $this->createElement('wsdl:portType');
        $this->portType->setAttribute('name', $this->projectName);
        $this->definitions->appendChild($this->portType);
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
