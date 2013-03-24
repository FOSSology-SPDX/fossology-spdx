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

define("TITLE_spdx_packageInfoEdit_accept", _("Package Info Edit"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_packageInfoEdit_accept extends FO_Plugin
{
  public $Name       = "spdx_packageInfoEdit_accept";
  public $Title      = TITLE_spdx_packageInfoEdit_accept;
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
				$Val_PackageInfoPk = htmlentities(GetParm('packageInfoPk', PARM_TEXT), ENT_QUOTES);
				Spdx_update_package($Val_SpdxId,$Val_PackageInfoPk);
        /* Build HTML form */
        $V.= "<form name='packageEditAny' method='POST'>\n"; // no url = this url
        $V.= "<P />\n";
        $text = _("Package Info has been updated.");
        $V.= "$text<P />\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
        $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow; table-layout: fixed;' width='100%'>";
        $Val = htmlentities(GetParm('packagename', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Name");
        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packageversion', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Version");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packagefileName', PARM_TEXT), ENT_QUOTES);
        $Val_PackageName = $Val;
        $text = _("Package FileName");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val1 = htmlentities(GetParm('supplier', PARM_TEXT), ENT_QUOTES);
				$Val = htmlentities(GetParm('packagesupplier', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Supplier");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val1$Val</td>\n";
        $V.= "</tr>\n";
        $Val1 = htmlentities(GetParm('originator', PARM_TEXT), ENT_QUOTES);
				$Val = htmlentities(GetParm('packageoriginator', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Originator");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val1$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packagedownloadlocation', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Download Location");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packagechecksum', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Checksum");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packageverificationcode', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Verification Code");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('vcExcludedfiles', PARM_TEXT), ENT_QUOTES);
        $text = _("Verification Code Excluded Files");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('sourceinfo', PARM_TEXT), ENT_QUOTES);
        $text = _("Source Info");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensedeclared', PARM_TEXT), ENT_QUOTES);
        $text = _("License Declared");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licenseconcluded', PARM_TEXT), ENT_QUOTES);
        $text = _("License Concluded");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licenseinfofromfiles', PARM_TEXT), ENT_QUOTES);
        $text = _("License Info From Files");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('licensecomment', PARM_TEXT), ENT_QUOTES);
        $text = _("License Comment");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('packagecopyrighttext', PARM_TEXT), ENT_QUOTES);
        $text = _("Package Copyright Text");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('summary', PARM_TEXT), ENT_QUOTES);
        $text = _("Summary");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('description', PARM_TEXT), ENT_QUOTES);
        $text = _("Description");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td>$Val</td>\n";
        $V.= "</tr>\n";
        $V.= "</table><P />";
        
        $V .= "<a href=\"".$Uri."?mod=spdx_fileInfoEdit_list&spdxId=$Val_SpdxId&packageInfoPk=$Val_PackageInfoPk\" target='_blank')>Detail/Edit Files</a></br>\n";
        /* Get extracted lic info of the package */
        
        $sql = "select identifier, license_ref.rf_text as extractedtext, licensename, cross_ref_url, lic_comment, rf_text from spdx_extracted_lic_info, license_ref
				where spdx_fk = $Val_SpdxId
				and licensename = rf_shortname
				order by identifier";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$text = _("Extracted Lic Info");
	        $V .= "$text<br>\n";
	        $V.= "<table border='1' width='100%'>";
	        $V.= "<tbody><tr><th width='10%'>Identifier</th><th width='15%'>Extracted Text</th><th width='15%'>License Name</th><th width='15%'>Cross Reference URLs</th><th width='40%'>Comment</th><th>&nbsp;</th></tr>";
	        pg_result_seek($result, 0);
	        while ($extractLic = pg_fetch_assoc($result))
	        {
	        	$Val_Identifier = $extractLic['identifier'];
	        	$V.= "<tr><td align='left'>" . "LicenseRef-" . $extractLic['identifier'] . "</td><td align='left'>" . $extractLic['extractedtext'] . "</td><td align='left'>" . $extractLic['licensename'] . "</td><td align='left'>" . $extractLic['cross_ref_url'] . "</td><td align='left'style='overflow: hidden;'>" . $extractLic['lic_comment'] . "</td><td><a href=\"".$Uri."?mod=spdx_extdLicInfoEdit_input&spdxId=$Val_SpdxId&packageName=$Val_PackageName&identifier=$Val_Identifier\" target='_blank')>edit</a></td></tr>";
	        }
	        $V.= "</tbody></table><br>";
	        pg_result_seek($result, 0);
	      }
	      $text = _("File: ");
	      $filelistTest = _("File List");
	      // file info edit
		  	$filelistURI = "";
	      $V .= "$text <a href=\"".$Uri."?mod=spdx_fileInfoEdit_list&spdxId=$Val_SpdxId&packageInfoPk=$Val_PackageInfoPk\" target='_blank')>$filelistTest</a>\n";
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
$NewPlugin = new spdx_packageInfoEdit_accept;
?>
