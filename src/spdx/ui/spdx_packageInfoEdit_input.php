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

define("TITLE_spdx_packageInfoEdit_input", _("Package Info Edit"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_packageInfoEdit_input extends FO_Plugin
{
  public $Name       = "spdx_packageInfoEdit_input";
  public $Title      = TITLE_spdx_packageInfoEdit_input;
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
        $V.= "<form name='packageEditAny' method='POST' action='" . $Uri . "?mod=spdx_packageInfoEdit_confirm'>\n";
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
				$Val_pfile = htmlentities(GetParm('pfile', PARM_TEXT), ENT_QUOTES);
				$sql = "select * from spdx_package_info 
		        where pfile_fk = '$Val_pfile'
				and  spdx_fk = '$Val_SpdxId'";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$V.= "<P />\n";
	        $text = _("This option permits editing a single package.");
	        $text1 = _("Fields denoted by an * are required.");
	        $V.= "$text <br>$text1<P />\n";
					$Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
		      $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
		        
	        pg_result_seek($result, 0);
	        while ($packageInfo = pg_fetch_assoc($result))
	        {
	        	$V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'>\n";
						$V.= "<input type='hidden' value='$packageInfo[package_info_pk]' name='packageInfoPk'>\n";
	        	$text = _("Package Name");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[name],htmlentities(GetParm('packagename', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagename' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Version");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[version],htmlentities(GetParm('packageversion', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packageversion' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package FileName");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[filename],htmlentities(GetParm('packagefileName', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagefileName' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Supplier");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $ValType = GetHistoryBackFormValue($packageInfo[supplier_type],htmlentities(GetParm('supplier', PARM_TEXT), ENT_QUOTES));
		        $Val = GetHistoryBackFormValue($packageInfo[supplier],htmlentities(GetParm('packagesupplier', PARM_TEXT), ENT_QUOTES));
				    $V.= "<td><select name='supplier'>".GetPersonOrganization($ValType,$Val,'packagesupplier')."</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Originator");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $ValType = GetHistoryBackFormValue($packageInfo[originator_type],htmlentities(GetParm('originator', PARM_TEXT), ENT_QUOTES));
		        $Val = GetHistoryBackFormValue($packageInfo[originator],htmlentities(GetParm('packageoriginator', PARM_TEXT), ENT_QUOTES));
						$V.= "<td><select name='originator'>".GetPersonOrganization($ValType,$Val,'packageoriginator')."</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Download Location");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[download_location],htmlentities(GetParm('packagedownloadlocation', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagedownloadlocation' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Checksum");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $V.= "<td><input type='hidden' value='$packageInfo[checksum]' name='packagechecksum'>$packageInfo[checksum]</td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Verification Code");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $V.= "<td><input type='hidden' value='$packageInfo[verificationcode]' name='packageverificationcode'>$packageInfo[verificationcode] </td>\n";
		        $V.= "</tr>\n";
		        $text = _("Verification Code Excluded Files");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[verificationcode_excludedfiles],htmlentities(GetParm('verificationcodeexcludedfiles', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='verificationcodeexcludedfiles' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Source Info");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[source_info],htmlentities(GetParm('sourceinfo', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='sourceinfo' size=40></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Declared");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_declared],htmlentities(GetParm('licensedeclared', PARM_TEXT), ENT_QUOTES));
		        //$V.= "<td><input type='text' value='$Val' name='licensedeclared' style='width:95%'></td>\n";
		        $V.= "<td><textarea cols='120' rows='3' id='licensedeclared' name='licensedeclared'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Concluded");
		        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_concluded],htmlentities(GetParm('licenseconcluded', PARM_TEXT), ENT_QUOTES));
		        //$V.= "<td><input type='text' value='$Val' name='licenseconcluded' style='width:95%'></td>\n";
		        $V.= "<td><textarea cols='120' rows='3' id='licenseconcluded' name='licenseconcluded'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Info From Files");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_info_from_files],htmlentities(GetParm('licenseinfofromfiles', PARM_TEXT), ENT_QUOTES));
		        //$V.= "<td><input type='text' value='$Val' name='licenseinfofromfiles' style='width:95%'></td>\n";
		        $V.= "<td><textarea cols='120' rows='3' id='licenseinfofromfiles' name='licenseinfofromfiles'>$Val</textarea></td>\n";
		        $V.= "</tr>\n";
		        $text = _("License Comment");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[license_comment],htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='licensecomment' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Package Copyright Text");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[package_copyright_text],htmlentities(GetParm('packagecopyrighttext', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='packagecopyrighttext' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Summary");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[summary],htmlentities(GetParm('summary', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='summary' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $text = _("Description");
		        $V.= "$Style<th width='25%'>$text</th>";
		        $Val = GetHistoryBackFormValue($packageInfo[description],htmlentities(GetParm('description', PARM_TEXT), ENT_QUOTES));
		        $V.= "<td><input type='text' value='$Val' name='description' style='width:95%'></td>\n";
		        $V.= "</tr>\n";
		        $V.= "<tr><td colspan='2' style='background:black;'></td></tr>\n";
		        $V.= "</tbody></table><p>Extracted Lic Info &amp; File(s) Infro can be seen at next page</p>\n";
	        }
					$V.= "\n<button type='button' onclick='window.close();'>Close</button>\n";
					$text = _("Next");
	        $V.= "\n<input type='hidden' value='$Val_SpdxId' name='spdxId'><input type='submit' value='$text'>\n";
		}
		else{
			$V.= "\nThere is no package info, please back to package list\n";
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
$NewPlugin = new spdx_packageInfoEdit_input;
?>
