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

define("TITLE_spdx_main_page_input", _("SPDX Edit"));

/**
 * \class spdx_main_page extend from FO_Plugin
 * \brief 
 */
class spdx_main_page_input extends FO_Plugin
{
  public $Name       = "spdx_main_page_input";
  public $Title      = TITLE_spdx_main_page_input;
  public $MenuList   = "SPDX::Generation";
  public $Version    = "1.1";
  public $DBaccess   = PLUGIN_DB_NONE;
  
  /**
   * \brief Customize submenus.
   */
  function RegisterMenus()
  {

    // micro-menu
    $URL = $this->Name;
    menu_insert($MenuList,0, $URL, $Title);
  } // RegisterMenus()
  
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
        $V.= "<form name='spdxEditAny' method='POST' action='" . $Uri . "?mod=spdx_main_page_confirm'>\n";
        $V.= "<P />\n";
        $text = _("This option permits editing/creating a single SPDX file from analised packages/files by FOSSology.");
        $text1 = _("Fields denoted by an * are required.");
        $V.= "$text <br>$text1<P />\n";

        $Style = "<tr><td colspan=3 style='background:black;'></td></tr><tr>";
        $V.= "<table style='border:1px solid black; text-align:left; background:lightyellow;' width='100%'>";
        $Val = htmlentities(GetParm('spdxVersion', PARM_TEXT), ENT_QUOTES);
        $text = _("SPDX Version");
        $V.= "$Style<th width='25%'>$text</th>";
        $V.= "<td><select name='spdxVersion'><option value='SPDX-1.1'>SPDX-1.1</option></select></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creator', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator");
        $V.= "$Style<th width='25%'>$text <font color='red'>*</font></th>";
        $V.= "<td><input type='text' value='$Val' name='creator' size=20></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorOptional1', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator optional1");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='text' name='creatorOptional1' value='$Val' size=20></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorOptional2', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator optional2");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='text' name='creatorOptional2' value='$Val' size=20></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('createdDate', PARM_TEXT), ENT_QUOTES);
        if( empty($Val)) {
        	//$Val = Date("Y-m-d g:i:s");
        	$ValDate = Date("Y-m-d");
        	$ValTime = Date("H:i:s");
        }
        $text = _("Created Date");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='created_Date' value='$ValDate'><input type='hidden' name='created_Time' value='$ValTime'>$ValDate $ValTime</td>\n";
        $V.= "</tr>\n";
        $Val = "CC0-1.0";
        $text = _("Data License");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='hidden' name='dataLicense' value='$Val'>$Val</td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('creatorComment', PARM_TEXT), ENT_QUOTES);
        $text = _("Creator Comment");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='text' name='creatorComment' value='$Val' size=60></td>\n";
        $V.= "</tr>\n";
        $Val = htmlentities(GetParm('documentComment', PARM_TEXT), ENT_QUOTES);
        $text = _("Document Comment");
        $V.= "$Style<th>$text</th>\n";
        $V.= "<td><input type='text' name='documentComment' value='$Val' size=60></td>\n";
        $V.= "</tr>\n";
        $V.= "</table><P />";
        /* Get the list of packages */
        $sql = "select uploadtree.pfile_fk as pfile_pk, max(upload_origin||'('||upload_desc||')') as pkg_name, max(uploadtree_pk) as uploadtree_pk
								from upload , uploadtree
								where upload_pk = upload_fk
											and parent is null
								GROUP BY pfile_pk";
        $result = pg_query($PG_CONN, $sql);
        DBCheckResult($result, $sql, __FILE__, __LINE__);
        $text = _("Package(s)");
        $V .= "$text<br>\n";
        $V .= "<select id='packages' name='packages'>\n";
        $selectedPackages = htmlentities(GetParm('packages', PARM_RAW), ENT_QUOTES);
        pg_result_seek($result, 0);
        while ($package = pg_fetch_assoc($result))
        {
        	if (!empty($selectedPackages)){
	          $Selected = "";
	          if ($package['uploadtree_pk'] == $selectedPackages)
	          {
	          	$Selected = "selected";
	          }
          }
          $V.= "<option $Selected value='" . $package['uploadtree_pk'] . "'>";
          $V.= htmlentities($package['pkg_name']);
          $V.= "</option>\n";
        }
        pg_free_result($result);
        $V.= "</select><br>";
        $text = _("Next");
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
$NewPlugin = new spdx_main_page_input;
?>
