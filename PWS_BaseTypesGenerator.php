<?php
class PWS_BaseTypesGenerator
{
    private $schema;
    private $stylesheet;
    function __construct($schemaPath)
    {
        $this->schema = new DOMDocument();
        $this->schema->load($schemaPath);
        $this->stylesheet = new DOMDocument();
        $this->stylesheet->load('@DATA-DIR@/PWS/xsl/basetypes.xsl');
    }
    public function process()
    {
        $xslt = new XSLTProcessor();
        $xslt->registerPHPFunctions();
        $baseExt = 'Base';
        $xslt->setParameter('','ext', $baseExt);
        $xslt->importStylesheet($this->stylesheet);
        $baseTypes = $xslt->transformToXml($this->schema);
        $fp = fopen('BaseTypes.php', 'w');
        fwrite($fp, $baseTypes);
        fclose($fp);
    }

}
