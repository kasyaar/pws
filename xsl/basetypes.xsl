<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:strip-space elements="*"/>
    <xsl:output method="text" indent="yes"/>
	
    <xsl:template match="/">
        <![CDATA[<?php]]>
        <!-- Clases that extends some other classes must placed after classes that it extends -->
        <xsl:apply-templates select="//xsd:complexType[not(xsd:complexContent/xsd:extension)]"/>
        <xsl:apply-templates select="//xsd:complexType[xsd:complexContent/xsd:extension]"/>
        <![CDATA[?>]]>
    </xsl:template>
	
    <xsl:template match="xsd:complexType">
    	<xsl:variable name="base" select="xsd:complexContent/xsd:extension/@base"/>
        <xsl:variable name="documentation" select="xsd:annotation/xsd:documentation"/>
    /**
     * <xsl:value-of select="php:functionString('trim', $documentation)"/>
    <xsl:apply-templates select="xsd:annotation/xsd:appinfo"/>
     */
    class <xsl:value-of select="@name"/><xsl:value-of select="$ext"/>
            <!-- A condition that add extends sentence into class declaration if it exist -->
			<xsl:if test="xsd:complexContent/xsd:extension"> extends <xsl:value-of select="php:functionString('substr', $base, php:functionString('strpos', $base, ':')+1)"/><xsl:value-of select="$ext"/>
			</xsl:if>
    {<xsl:apply-templates/>
    }
    </xsl:template>    
   
    <xsl:template match="xsd:attribute|//xsd:sequence/xsd:element|//xsd:all/xsd:element">
        <xsl:variable name="methodName" select="php:functionString('ucwords', @name)"/>
        private $<xsl:value-of select="@name"/>;//<xsl:value-of select="@type"/>
        public function get<xsl:value-of select="$methodName"/>() {
            return $this-><xsl:value-of select="@name"/>;
        }
        public function set<xsl:value-of select="$methodName"/>($<xsl:value-of select="@name"/>) { 
            $this-><xsl:value-of select="@name"/> = $<xsl:value-of select="@name"/>;
        }
    </xsl:template>
    <xsl:template match="xsd:annotation/xsd:appinfo">
     * @</xsl:template>
    <xsl:template match="xsd:annotation"/>
    <xsl:template match="xsd:simpleType"/>
    <xsl:template match="xsd:attributeGroup"/>
    
</xsl:stylesheet>
