<?xml version="1.0" encoding="UTF-8"?>
<stylesheet version="1.0" xmlns="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema">
<output method="text"/>
<strip-space elements="*"/>
	<template match="/">
        <apply-templates/>		
    </template>
	<template match="xs:complexType">
	   '<value-of select="@name"/>' => '<value-of select="@name"/>Model',
	</template>
	<template match="xs:schema/xs:element">
       '<value-of select="@name"/>' => '<value-of select="@name"/>',
    </template>
    <template match="xs:annotation/xs:documentation"/>
</stylesheet>
