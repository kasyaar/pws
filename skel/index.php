<?php
//include_once 'lib/Controller.class.php';
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
            //$refl = new ReflectionAnnotatedClass('Controller');
            header('Content-Type: xsd'); 
echo '<?xml version="1.0" encoding="UTF-8"?>
    <schema xmlns="http://www.w3.org/2001/XMLSchema" targetNamespace="http://starfish/schemas/xmlapi7"
            xmlns:tns="http://starfish/schemas/xmlapi7" xmlns:base="http://starfish/schemas/basetypes"
                    elementFormDefault="qualified">
                            <!--import namespace="http://starfish/schemas/basetypes" schemaLocation="./basetypes.xsd" /-->
</schema>';
        }
        elseif( isset($_GET['wsdl']))
        {
            echo 'Starting to generate wsdl...';
        }
    }
    
}

