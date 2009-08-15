<?xml version="1.0" encoding="UTF-8"?>
<stylesheet version="1.0" xmlns="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema"
	xmlns:php="http://php.net/xsl">
	<output method="text" />
	<strip-space elements="*" />
	<template match="/">
<![CDATA[<?php]]>
		<apply-templates />
<![CDATA[?>]]>
	</template>
	<template match="xs:schema/xs:element">
/**
 * <value-of select="php:functionString('trim',xs:annotation/xs:documentation)"/>
 */
class <value-of select="@name"/>
{<apply-templates/>
}
	</template>
	<template match="xs:attribute|xs:sequence/xs:element|xs:choice/xs:element|xs:all/xs:element">
	public $<value-of select="@name"/>;//<value-of select="@type"/>
	</template>
	<template match="xs:documentation"></template>
</stylesheet>