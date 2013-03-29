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

define("TITLE_spdx_extdLicInfoEdit_confirm", _("Extracted Lic Info Edit"));

/**
 * \class spdx_extdLicInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_extdLicInfoEdit_confirm extends FO_Plugin
{
  public $Name       = "spdx_extdLicInfoEdit_confirm";
  public $Title      = TITLE_spdx_extdLicInfoEdit_confirm;
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
        /* Build HTML form */
        $V.= "<form name='extractedLicEditAny' method='POST' action='" . $Uri . "?mod=spdx_extdLicInfoEdit_accept'>\n";
        $V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'>\n";
				$V.= "<P />\n";
        $text = _("This option permits editing extracted license information.");
        $V.= "$text<P />\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
        $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow; table-layout: fixed;' width='100%'>";
        $text = _("Package Name");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val_packageName' name='packagename'>$Val_packageName</td>\n";
        $V.= "</tr>\n";
        $Val = "LicenseRef-".htmlentities(GetParm('identifier', PARM_TEXT), ENT_QUOTES);
        $text = _("Identifier");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val_Identifier' name='identifier'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('extractedtext', PARM_TEXT), ENT_QUOTES);
        $text = _("Extracted Text");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='extractedtext'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensename', PARM_TEXT), ENT_QUOTES);
        $text = _("License Name");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='licensename'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('crossReferenceURLs', PARM_TEXT), ENT_QUOTES);
        $text = _("Cross Reference URLs");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='crossReferenceURLs'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES);
        $text = _("Comment");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><input type='hidden' value='$Val' name='licensecomment'>$Val</td>\n";
        $V.= "</tr>\n";
        $V.= "<tr><td colspan='3' style='background:black;'></td></tr>\n";
        $V.= "</table><P/>";
        $V.= "\n<button type='button' onclick='history.back();'>Back</button>\n";
        $text = _("Update");
        $V.= "\n<input type='submit' value='$text'>\n";
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
$NewPlugin = new spdx_extdLicInfoEdit_confirm;
?>
