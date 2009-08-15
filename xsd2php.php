<?php
define('XSD2PHP_PROJECT_ROOT',dirname(__FILE__).'/');

$xsd = new DOMDocument();
$xsl = new DOMDocument();
$xslt = new XSLTProcessor();
$xslt->registerPHPFunctions();
/**
 * base xsd layer generation
 */
$baseExt = 'Base';
$xslt->setParameter('','ext', $baseExt);

$xsd->load(XSD2PHP_PROJECT_ROOT.'basetypes.xsd');
$xsl->load('xsl/basetypes.xsl');
$xslt->importStylesheet($xsl);
$baseTypes = $xslt->transformToXml($xsd);
$fp = fopen(XSD2PHP_PROJECT_ROOT.'lib/BaseTypes.php', 'w');
fwrite($fp, $baseTypes);
fclose($fp);
