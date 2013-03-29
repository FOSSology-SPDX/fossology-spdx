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

define("TITLE_spdx_extdLicInfoEdit_input", _("Extracted Lic Info Edit"));

/**
 * \class spdx_extdLicInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_extdLicInfoEdit_input extends FO_Plugin
{
  public $Name       = "spdx_extdLicInfoEdit_input";
  public $Title      = TITLE_spdx_extdLicInfoEdit_input;
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
        /* If this is a POST, then process the request. */
        $UserId = GetParm('userid', PARM_INTEGER);
        
        /* Build HTML form */
        $Uri = Traceback_uri();
        $V.= "<form name='extractedLicEditAny' method='POST' action='" . $Uri . "?mod=spdx_extdLicInfoEdit_confirm'>\n";
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
        $Val_packageName = htmlentities(GetParm('packageName', PARM_TEXT), ENT_QUOTES);
        $Val_Identifier = htmlentities(GetParm('identifier', PARM_TEXT), ENT_QUOTES);
				$sql = "select identifier, cross_ref_url, lic_comment, license_display_name, rf_text as extractedtext from spdx_extracted_lic_info, license_ref
		        where spdx_fk = '$Val_SpdxId'
		        	and identifier = '$Val_Identifier'
		        	and licensename = rf_shortname";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$V.= "<P/>\n";
	        $text = _("This option permits editing extracted license information.");
	        $V.= "$text <P/>\n";
					$Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
		      $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
		        
	        pg_result_seek($result, 0);
	        while ($extractedLicInfo = pg_fetch_assoc($result))
	        {
	        	$V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'>\n";
						$text = _("Package Name");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $V.= "<td><input type='hidden' value='$Val_packageName' name='packageName'>$Val_packageName</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Identifier");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = "LicenseRef-".$extractedLicInfo[identifier];
		        $V.= "<td><input type='hidden' value='$Val_Identifier' name='identifier'>$Val</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Extracted Text");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = $extractedLicInfo[extractedtext];
		        $V.= "<td><input type='hidden' value='$Val' name='extractedtext'>$Val</td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Name");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($extractedLicInfo[license_display_name],htmlentities(GetParm('licensename', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licensename' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Cross Reference URLs");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($extractedLicInfo[cross_ref_url],htmlentities(GetParm('crossReferenceURLs', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='crossReferenceURLs' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Comment");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($extractedLicInfo[lic_comment],htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licensecomment' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $V.= "<tr><td colspan='3' style='background:black;'></td></tr>\n";
		        $V.= "</table><P/>";
	        }
	        
					$V.= "\n<button type='button' onclick='window.close();'>Close</button>\n";
					$text = _("Next");
	        $V.= "\n<input type='submit' value='$text'>\n";
				}
				else{
					$V.= "\nThere is no extracted Lic info, please back to package list\n";
					// close window
					$V.= "\n<button type='button' onclick='window.close()'>Close</button>\n";
				}
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
$NewPlugin = new spdx_extdLicInfoEdit_input;
?>
