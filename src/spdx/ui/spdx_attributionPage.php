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

define("TITLE_spdx_attributionPage", _("License Attribution Page"));

/**
 * \class spdx_packageInfoEdit extend from FO_Plugin
 * \brief 
 */
class spdx_attributionPage extends FO_Plugin
{
  public $Name       = "spdx_attributionPage";
  public $Title      = "License Attribution Page";
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
        
		$lastLicense = "";
		
        /* Build HTML form */
        $Uri = Traceback_uri();
        $V.= "<form name='fileListAll' method='post' action='" . $Uri . "?mod=spdx_fileInfoEdit_input'>\n";
        $Val_SpdxId = htmlentities(GetParm('spdxId', PARM_TEXT), ENT_QUOTES);
		$Val_packageInfoPk = htmlentities(GetParm('packageInfoPk',PARM_TEXT),ENT_QUOTES);
			
		$_SESSION['spdxId'] = $Val_SpdxId;
		$_SESSION['packageInfoPk'] = $Val_packageInfoPk;
		
			//getting package name
		$sql = "select name from spdx_package_info 
		where package_info_pk = '$Val_packageInfoPk'";
		
		$result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
			while ($packageInfo = pg_fetch_assoc($result)){
			$VAL_packageName = $packageInfo['name'];
	        }
		}
		
		// getting file name
		$sql = "select * from spdx_file_info 
		where package_info_fk = '$Val_packageInfoPk'
		and  spdx_fk = '$Val_SpdxId'
		ORDER By license_concluded DESC , filename";
				
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        if (pg_num_rows($result) > 0){
			$inital = 1;
			
			$V.= "<input type='hidden' value='$Val_PackageInfoPk' name='packageInfoPk'/>\n";
	        $V.= "<input type='hidden' value='$Val_SpdxId' name='spdxId'/>\n";
		    $V.= "<p>Package: ".$VAL_packageName."</p>";
			pg_result_seek($result, 0);
	        while ($fileInfo = pg_fetch_assoc($result))
	        {
				if($fileInfo['license_concluded'] != $lastLicense){
					
					$V.= "</tbody></table><br>";		
					$V.="<h2>".$fileInfo['license_concluded']."</h2>";					
					$V.= "<table border='1' style='width:700px;'>";
					$V.= "<tbody><tr><th width='10%'>File Name</th><th width='10%'>File Type</th><th width='10%'>Liscense Concluded</th><th width='10%'>License Info in File</th><th width='10%'>Liscense Comments</th><th width='15%'>File Copyright Text</th><th width='15%'>File Comment</th></tr>";
				}
				
		        $V.= "<tr><td width='10%'>".$fileInfo['filename']."</td>";
						$V.= "<td width='10%'>".$fileInfo['filetype']."</td>";
						$V.= "<td width='10%'>".$fileInfo['license_concluded']."</td>";
						$V.= "<td width='10%'>".$fileInfo['license_info_in_file']."</td>";
						$V.= "<td width='10%'>".$fileInfo['license_comment']."</td>";
						$V.= "<td width='15%'>".substr($fileInfo['file_copyright_text'],0,60)." ... </td>";
						$V.= "<td width='15%' align='left' style='overflow:hidden;'>".$fileInfo['file_comment']."</td>";
						    
				$lastLicense =$fileInfo['license_concluded'];
			}
			$V.= "</tbody></table><br>";			
	      }
	    pg_free_result($result);
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
$NewPlugin = new spdx_attributionPage;
?>
