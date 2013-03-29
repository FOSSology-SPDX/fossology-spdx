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

define("TITLE_spdx_extdLicInfoEdit_list", _("Extracted Lic Info List"));

/**
 * \class spdx_extdLicInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_extdLicInfoEdit_list extends FO_Plugin
{
  public $Name       = "spdx_extdLicInfoEdit_list";
  public $Title      = "Extracted Lic Info List";
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
        $V.= "<form name='fileListAll' method='post' action='" . $Uri . "?mod=spdx_fileInfoEdit_input'>\n";
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
				$Val_packageInfoPk = htmlentities(GetParm('packageInfoPk',PARM_TEXT),ENT_QUOTES);
		    //$Val_packageName = htmlentities(GetParm('packageName', PARM_TEXT), ENT_QUOTES);
			
			//$_SESSION['spdxId'] = $Val_SpdxId;
			//$_SESSION['packageInfoPk'] = $Val_packageInfoPk;
			
				//getting package name
				$sql = "select name from spdx_package_info 
				where package_info_pk = '$Val_packageInfoPk'";
			
				$result = pg_query($PG_CONN, $sql);
	  		DBCheckResult($result, $sql, __FILE__, __LINE__);
	  		if (pg_num_rows($result) > 0){
					while ($packageInfo = pg_fetch_assoc($result))
					{
						$VAL_packageName = $packageInfo['name'];
			    }
				}
			// getting file name
			/* Get extracted lic info of the package */
        
        $sql = "select identifier, license_ref.rf_text as extractedtext, license_display_name as licensename, cross_ref_url, lic_comment, rf_text from spdx_extracted_lic_info, license_ref
					where spdx_fk = $Val_SpdxId
					and licensename = rf_shortname
					order by identifier";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
        	$V.= "<p>Package: ".$VAL_packageName."</p>";
	        $V.= "<table border='1' width='100%'>";
	        $V.= "<tbody><tr><th width='10%'>Identifier</th><th width='15%'>Extracted Text</th><th width='15%'>License Name</th><th width='15%'>Cross Reference URLs</th><th width='40%'>Comment</th><th>&nbsp;</th></tr>";
	        pg_result_seek($result, 0);
	        while ($extractLic = pg_fetch_assoc($result))
	        {
	        	$Val_Identifier = $extractLic['identifier'];
	        	$V.= "<tr><td align='left'>" . "LicenseRef-" . $extractLic['identifier'] . "</td><td align='left'>" . $extractLic['extractedtext'] . "</td><td align='left'>" . $extractLic['licensename'] . "</td><td align='left'>" . $extractLic['cross_ref_url'] . "</td><td align='left'style='overflow: hidden;'>" . $extractLic['lic_comment'] . "</td><td><a href=\"".$Uri."?mod=spdx_extdLicInfoEdit_input&spdxId=$Val_SpdxId&packageName=$VAL_packageName&identifier=$Val_Identifier\" target='newExtLicEdit')>edit</a></td></tr>";
	        }
	        $V.= "</tbody></table><br>";
	        pg_free_result($result);
	      }
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
$NewPlugin = new spdx_extdLicInfoEdit_list;
?>
