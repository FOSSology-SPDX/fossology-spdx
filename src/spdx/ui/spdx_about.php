<?php
/***********************************************************
 Copyright (C) 2013 University of Nebraska at Omaha.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the Apache License, Version 2.0
 as published by the Apache Software Foundation.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 Apache License for more details.
 
 You should have received a copy of the Apache License along
 with this program; if not, contact to the Apache Software Foundation.
***********************************************************/

define("TITLE_spdx_about", _("About FOSSology+SPDX"));

/**
 * \class spdx_about extend from FO_Plugin
 * \brief 
 */
class spdx_about extends FO_Plugin
{
  var $Name       = "spdx_about";
  var $Title      = TITLE_spdx_about;
  var $MenuList   = "SPDX::About";
  var $Version    = "1.1";
  var $DBaccess   = PLUGIN_DB_NONE;
  var $LoginFlag  = 0;
  var $_Project 	= "FOSSology+SPDX";
  var $_Copyright	= "Copyright (C) 2013 University of Nebraska at Omaha.";
  var $_Text			= "The FOSSology+SPDX Project is a Free Open Source Software (FOSS) project built from FOSSology project. The goals are integrating the FOSSology output with the SPDX standard. Existing modules include creating an SPDX file in TAG format, licenses/copyrights information in NOTICE format.";
  var $_Website_comment	= "There is much more information available on the website";
  var $_Website_link		= "https://sites.google.com/site/fossologyunospdx/";
  
  /**
   * \brief Generate the text for this plugin.
   */
  function Output() {
    if ($this->State != PLUGIN_STATE_READY) {
      return;
    }

    $V = "";

    switch($this->OutputType) {
      case "XML":
        $V .= "<project>$this->_Project</project>\n";
        $V .= "<copyright>$this->_Copyright</copyright>\n";
        $V .= "<text>$this->_Text</text>\n";
        break;
      case "HTML":
 		    $V .= "<P/>\n";
        $V .= "$this->_Copyright\n";
        $V .= "<P/>\n";
        $V .= str_replace("\n","\n<P>\n",$this->_Text);
        $V .= "<P/>\n";
        $V .= "$this->_Website_comment\n";
        $V .= "<a href='$this->_Website_link'target='_blank'>$this->_Website_link</a> \n";
        $V .= "<P/>\n";
        break;
    	case "Text":
		    $V .= "$this->_Project\n";
        $V .= "$this->_Copyright\n";
        $V .= str_replace("\n","\n\n",$this->_Text) . "\n";
      	break;
	    default:
	      break;
  }
  if (!$this->OutputToStdout) {
    return ($V);
  }
  print ("$V");
  return;
 }
};
$NewPlugin = new spdx_about;
$NewPlugin->Initialize();
?>
