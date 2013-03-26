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

define("TITLE_spdx_extdLicInfoEdit_accept", _("Extracted Info Edit"));

/**
 * \class spdx_extdLicInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_extdLicInfoEdit_accept extends FO_Plugin
{
  public $Name       = "spdx_extdLicInfoEdit_accept";
  public $Title      = TITLE_spdx_extdLicInfoEdit_accept;
  public $Version    = "1.1";
  public $DBaccess   = PLUGIN_DB_NONE;
  /**
   * \brief Generate the text for this plugin.
   */
  function Output() {
    if ($this->State != PLUGIN_STATE_READY) {
      return;
    }
    global $PG_CONN;
    $V = "";

    switch($this->OutputType) {
      case "XML":
        break;
      case "HTML":
        $Uri = Traceback_uri();
				$Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
				$Val_packageName = htmlentities(GetParm('packageName', PARM_TEXT), ENT_QUOTES);
				$Val_Identifier = htmlentities(GetParm('identifier', PARM_TEXT), ENT_QUOTES);
				Spdx_update_extractedLic($Val_SpdxId,$Val_Identifier);
        /* Build HTML form */
        $V.= "<form name='extractedLicEditAny' method='POST'>\n"; // no url = this url
        $V.= "<P />\n";
        $text = _("Extracted Lic Info has been updated.");
        $V.= "$text<P/>\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
        $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
        $Val = htmlentities(GetParm('packagename', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Name");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = "LicenseRef-".htmlentities(GetParm('identifier', PARM_TEXT), ENT_QUOTES);
        $text = _("Identifier");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('extractedtext', PARM_TEXT), ENT_QUOTES);
        $text = _("Extracted Text");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensename', PARM_TEXT), ENT_QUOTES);
        $text = _("License Name");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('crossReferenceURLs', PARM_TEXT), ENT_QUOTES);
        $text = _("Cross Reference URLs");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES);
        $text = _("Comment");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $V.= "</table><P/>";
        $V.= "\n<button type='button' onclick='window.close()'>Close</button>\n";
        $V.= "</form>\n";
        break;
    case "Text":
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
$NewPlugin = new spdx_extdLicInfoEdit_accept;
?>
