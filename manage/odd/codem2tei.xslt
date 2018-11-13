<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns="http://www.tei-c.org/ns/1.0"
  xmlns:mhs="http://www.masshist.org/ns/1.0"
  xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl"
  xpath-default-namespace="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="#all"
  version="3.0">
  <xd:doc scope="stylesheet">
    <xd:desc>
      <xd:p><xd:b>Created on:</xd:b> Oct 2, 2018</xd:p>
      <xd:p><xd:b>Author:</xd:b> syd</xd:p>
      <xd:p>Convert a CODEM document into vanilla TEI P5 (currently v. 3.4.0)</xd:p>
    </xd:desc>
  </xd:doc>

  <xsl:output method="xml" indent="yes"/>

  <xsl:template match="node()">
    <xsl:if test="not(ancestor::*)">
      <xsl:text>&#x0A;</xsl:text>
    </xsl:if>
    <xsl:copy>
      <xsl:apply-templates select="@* | node()"/>
    </xsl:copy>
  </xsl:template>
  <xsl:template match="@*">
    <xsl:copy/>
  </xsl:template>  
  
  <xsl:template match="mhs:persRef">
    <rs type="person">
      <xsl:apply-templates select="@* except ( @type, @subtype )"/>
      <xsl:choose>
        <xsl:when test="@type and @subtype">
          <xsl:attribute name="subtype" select="@type"/>
          <xsl:message>WARNING: subtype=<xsl:value-of select="@subtype"/> on mhs:persRef #<xsl:value-of select="count( preceding::mhs:persRef ) +1"/> dropped.</xsl:message>
        </xsl:when>
        <xsl:when test="@type or @subtype">
          <xsl:attribute name="subtype" select="@type|@subtype"/>
        </xsl:when>
      </xsl:choose>
      <xsl:apply-templates select="node()"/>
    </rs>
  </xsl:template>
  
  <xsl:template match="div[@mhs:*]">
    <xsl:if test="not(ancestor::*)">
      <xsl:text>&#x0A;</xsl:text>
    </xsl:if>
    <xsl:copy>
      <xsl:apply-templates select="@* except @mhs:*"/>
      <xsl:call-template name="mhsAttrs2PI"/>
      <xsl:apply-templates select="node()"/>
    </xsl:copy>
  </xsl:template>
  
  <xsl:template name="mhsAttrs2PI">
    <xsl:text>&#x0A;</xsl:text>
    <xsl:processing-instruction name="codem-data">
      <xsl:text>&#x20;</xsl:text>
      <xsl:for-each select="@mhs:*">
        <xsl:value-of select="concat( local-name(.),'=', normalize-space(.), ' ')"/>
      </xsl:for-each>
    </xsl:processing-instruction>
  </xsl:template>

</xsl:stylesheet>
