<?php
include_once 'lib/Controller.class.php';
include_once '@PWS-LIBS@/lib/WSDLDocument.class.php';

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
            $doc = new WSDLDocument('@PROJECTNAME@');
            $doc->generate();
            echo $doc->saveXML();
        }
    }
    
}

