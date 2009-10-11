<?php
include 'lib/Controller.php';
echo 1;
$server = new SoapServer('xmlapi7.wsdl', array('classmap'=>$classmap));
$server->setClass('Controller');
