<?php 
/*
 * Module:      process.inc.php
 * Description: This module does all the heavy lifting for any DB updates; new entries,
 *              new users, organization, etc.
 */
require('../paths.php');
require(DB.'common.db.php');
require(INCLUDES.'url_variables.inc.php');

// Generate the Judging Number for the entry

function generate_judging_num($style_cat_num) {
	include(CONFIG.'config.php');	
	mysql_select_db($database, $brewing);
	$query_brewing_styles = sprintf("SELECT brewJudgingNumber FROM brewing WHERE brewCategory='%s' ORDER BY brewJudgingNumber DESC LIMIT 1", $style_cat_num);
	$brewing_styles = mysql_query($query_brewing_styles, $brewing) or die(mysql_error());
	$row_brewing_styles = mysql_fetch_assoc($brewing_styles);
	$totalRows_brewing_styles = mysql_num_rows($brewing_styles);
	if (($totalRows_brewing_styles == 0) || ($row_brewing_styles['brewJudgingNumber'] == "")) $return = $style_cat_num."001";
	else $return = $row_brewing_styles['brewJudgingNumber'] + 1;
	return $return;
}

function capitalize($string) {
	$lowercase = strtolower($string);
	$capitalize = ucwords($lowercase);
	return $capitalize;
}

function relocate($referer,$page) {
	// determine if referrer has any msg=X or id=X variables attached and remove
	if (strstr($referer,"&msg")) { 
	$pattern = array("/[0-9]/", "/&msg=/");
	$referer = preg_replace($pattern, "", $referer);
	$pattern = array("/[0-9]/", "/&id=/");
	$referer = preg_replace($pattern, "", $referer);
	if ($page != "default") { 
		$pattern = array("/[0-9]/", "/&pg=/"); 
		$referer = preg_replace($pattern, "", $referer); 
		$referer .= "&pg=".$page; 
		}
	if (strpos($referer,"?")===false) $referer = $referer."?";
	}
	return $referer;
}

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;
  require ('scrubber.inc.php');
  switch ($theType) {
  
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
	case "scrubbed":
	  $theValue = ($theValue != "") ? "'" . strtr($theValue, $html_string) . "'" : "NULL";
  }
  return $theValue;
}

// Global Variables

if (strpos($_POST['relocate'],"?") === false) { 
	$insertGoTo = $_POST['relocate']."?msg=1"; 
	$updateGoTo = $_POST['relocate']."?msg=2";
	$massUpdateGoTo = $_POST['relocate']."?msg=9";
}

else { 
	$insertGoTo = $_POST['relocate']."&msg=1";
	$updateGoTo = $_POST['relocate']."&msg=2";
	$massUpdateGoTo = $_POST['relocate']."&msg=9";
}

$deleteGoTo = relocate($_SERVER['HTTP_REFERER'],"default")."&msg=5";

session_start(); 
//require ('authentication.inc.php'); session_start(); sessionAuthenticate();

if ($action == "delete") {

  if ($go == "judging_location") {
  // remove relational location ids from affected rows in brewer's table
  mysql_select_db($database, $brewing);
  $query_loc = "SELECT * from brewer WHERE brewerJudgeLocation='$id'";
  $loc = mysql_query($query_loc, $brewing) or die(mysql_error());
  $row_loc = mysql_fetch_assoc($loc);
  $totalRows_loc = mysql_num_rows($loc);
  if ($totalRows_loc > 0) {
  	do  {
  		$updateSQL = "UPDATE brewer SET brewerJudgeLocation=NULL WHERE id='".$row_loc['id']."'; ";
		mysql_select_db($database, $brewing);
  		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
  	while($row_loc = mysql_fetch_assoc($loc));
  }
  
  $query_loc = "SELECT * from brewer WHERE brewerJudgeLocation2='$id'";
  $loc = mysql_query($query_loc, $brewing) or die(mysql_error());
  $row_loc = mysql_fetch_assoc($loc);
  $totalRows_loc = mysql_num_rows($loc);
  if ($totalRows_loc > 0) {
  	do  {
  		$updateSQL = "UPDATE brewer SET brewerJudgeLocation2=NULL WHERE id='".$row_loc['id']."'; ";
		mysql_select_db($database, $brewing);
  		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
  	while($row_loc = mysql_fetch_assoc($loc));
  }
  
  $query_loc = "SELECT * from brewer WHERE brewerStewardLocation='$id'";
  $loc = mysql_query($query_loc, $brewing) or die(mysql_error());
  $row_loc = mysql_fetch_assoc($loc);
  $totalRows_loc = mysql_num_rows($loc);
  if ($totalRows_loc > 0) {
  	do  {
  		$updateSQL = "UPDATE brewer SET brewerStewardLocation=NULL WHERE id='".$row_loc['id']."'; ";
		mysql_select_db($database, $brewing);
  		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
  	while($row_loc = mysql_fetch_assoc($loc));
  }
  
  $query_loc = "SELECT * from brewer WHERE brewerStewardLocation2='$id'";
  $loc = mysql_query($query_loc, $brewing) or die(mysql_error());
  $row_loc = mysql_fetch_assoc($loc);
  $totalRows_loc = mysql_num_rows($loc);
  if ($totalRows_loc > 0) {
  	do  {
  		$updateSQL = "UPDATE brewer SET brewerStewardLocation2=NULL WHERE id='".$row_loc['id']."'; ";
		mysql_select_db($database, $brewing);
  		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
  	while($row_loc = mysql_fetch_assoc($loc));
  }
  
  } // end remove location relational ids from brewer table


  if ($go == "participants") {
  
  mysql_select_db($database, $brewing);
  $query_delete_brewer = sprintf("SELECT id FROM users WHERE user_name='%s'", $username);
  $delete_brewer = mysql_query($query_delete_brewer, $brewing) or die(mysql_error()); 
  $row_delete_brewer = mysql_fetch_assoc($delete_brewer);
  
  $deleteUser = sprintf("DELETE FROM users WHERE id='%s'", $row_delete_brewer['id']);
  mysql_select_db($database, $brewing);
  $Result = mysql_query($deleteUser, $brewing) or die(mysql_error());
  
  $deleteBrewer = sprintf("DELETE FROM brewer WHERE uid='%s'", $row_delete_brewer['id']);
  mysql_select_db($database, $brewing);
  $Result = mysql_query($deleteBrewer, $brewing) or die(mysql_error());
  
  $query_entries = sprintf("SELECT id from brewing WHERE brewBrewerID='%s'", $row_delete_brewer['id']);
  $entries = mysql_query($query_entries, $brewing) or die(mysql_error());
  $row_entries = mysql_fetch_assoc($entries);
  
  do { $a[] = $row_entries['id']; } while ($row_entries = mysql_fetch_assoc($entries));

  sort($a);
  
  	foreach ($a as $id) { 
  	$deleteEntries = sprintf("DELETE FROM brewing WHERE id='%s'", $id);
  	mysql_select_db($database, $brewing);
  	$Result = mysql_query($deleteEntries, $brewing) or die(mysql_error());
  	}
  
  }
  
  if ($go == "entries") {
  mysql_select_db($database, $brewing);
  $query_delete_entry = sprintf("SELECT id FROM judging_scores WHERE eid='%s'", $id);
  $delete_entry = mysql_query($query_delete_entry, $brewing) or die(mysql_error()); 
  $row_delete_entry = mysql_fetch_assoc($delete_entry);
  
  $deleteScore = sprintf("DELETE FROM judging_scores WHERE id='%s'", $row_delete_entry['id']);
  $Result = mysql_query($deleteScore, $brewing) or die(mysql_error());
  }
  
  if ($go == "judging_tables") {
	mysql_select_db($database, $brewing);
	
	$query_delete_assign = sprintf("SELECT id FROM judging_scores WHERE scoreTable='%s'", $id);
  	$delete_assign = mysql_query($query_delete_assign, $brewing) or die(mysql_error()); 
  	$row_delete_assign = mysql_fetch_assoc($delete_assign);
	$totalRows_delete_assign = mysql_num_rows($delete_assign);
	
	if ($totalRows_delete_assign > 0) {
	do { $z[] = $row_delete_assign['id']; } while (mysql_fetch_assoc($delete_assign));
	
	foreach ($z as $aid) {
		$deleteAssign = sprintf("DELETE FROM judging_assignments WHERE id='%s'", $aid);
		$Result = mysql_query($deleteAssign, $brewing) or die(mysql_error());
		}
	
  	$query_delete_scores = sprintf("SELECT id,eid FROM judging_scores WHERE scoreTable='%s'", $id);
  	$delete_scores = mysql_query($query_delete_scores, $brewing) or die(mysql_error()); 
  	$row_delete_scores = mysql_fetch_assoc($delete_scores);
	
	do { $a[] = $row_delete_scores['id']; $c[] = $row_delete_scores['eid']; } while ($row_delete_scores = mysql_fetch_assoc($delete_scores));
	
	foreach ($a as $sid) {
		$deleteScore = sprintf("DELETE FROM judging_scores WHERE id='%s'", $sid);
		$Result = mysql_query($deleteScore, $brewing) or die(mysql_error());
		}
	}
	$query_delete_flights = sprintf("SELECT id,flightTable FROM judging_flights WHERE flightTable='%s'", $id);
  	$delete_flights = mysql_query($query_delete_flights, $brewing) or die(mysql_error()); 
  	$row_delete_flights = mysql_fetch_assoc($delete_flights);
	$totalRows_delete_flights = mysql_num_rows($delete_flights);
	
	if ($totalRows_delete_flights > 0) {
	do { $b[] = $row_delete_flights['id']; } while ($row_delete_flights = mysql_fetch_assoc($delete_flights));
	
	foreach ($b as $fid) {
		$deleteFlight = sprintf("DELETE FROM judging_flights WHERE id='%s'", $fid);
		$Result = mysql_query($deleteFlight, $brewing) or die(mysql_error());
		}
	if ($c != "") {
	foreach ($c as $eid) {
		
		$query_delete_bos = sprintf("SELECT id,eid FROM judging_scores_bos WHERE eid='%s'", $eid);
  		$delete_bos = mysql_query($query_delete_bos, $brewing) or die(mysql_error()); 
  		$row_delete_bos = mysql_fetch_assoc($delete_bos);
		if ($eid == $row_delete_bos['eid']) {
			$deleteBOS = sprintf("DELETE FROM judging_scores_bos WHERE id='%s'", $row_delete_bos['id']);
			$Result = mysql_query($deleteScore, $brewing) or die(mysql_error());
			}
		  }
		}
	 }
  } 
  
  $deleteSQL = sprintf("DELETE FROM $dbTable WHERE id='%s'", $id);
  $Result1 = mysql_query($deleteSQL, $brewing) or die(mysql_error());
  
  if ($dbTable == "archive") { 
  $dropTable = "DROP TABLE users_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE brewing_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE brewer_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE sponsors_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE judging_assignments_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE judging_flights_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE judging_scores_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE judging_scores_bos_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE judging_tables_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  $dropTable = "DROP TABLE style_types_$filter";
  $Result = mysql_query($dropTable, $brewing) or die(mysql_error());
  
  header(sprintf("Location: %s", $deleteGoTo));
  }
  
  if ($dbTable != "archive") { 
  header(sprintf("Location: %s", $deleteGoTo));
  }

}

// --------------------------- If Adding an Entry ------------------------------- //

if (($action == "add") && ($dbTable == "brewing")) { 

$query_user = sprintf("SELECT * FROM users WHERE user_name = '%s'", $_SESSION["loginUsername"]);
$user = mysql_query($query_user, $brewing) or die(mysql_error());
$row_user = mysql_fetch_assoc($user);

if ($row_user['userLevel'] == 1) { 
$nameBreak = $_POST['brewBrewerID'];
$name = explode('*', $nameBreak);
$brewBrewerID = $name[0];
$brewBrewerLastName = $name[1];
$brewBrewerFirstName = $name[2];
}
else {
$brewBrewerID = $_POST['brewBrewerID'];
$brewBrewerLastName = $_POST['brewBrewerLastName']; 
$brewBrewerFirstName = $_POST['brewBrewerFirstName'];
}

$styleBreak = $_POST['brewStyle'];
$style = explode('-', $styleBreak);
$styleTrim = ltrim($style[0], "0"); 
if ($style [0] < 10) $styleFix = "0".$style[0]; else $styleFix = $style[0];

switch($styleBreak) {
	case "6-D":
	$special = "1";
	break;
	
	case "16-E":
	$special = "1";
	break;
	
	case "17-F":
	$special = "1";
	break;
	
	case "20-A":
	$special = "1";
	break;
	
	case "21-A":
	$special = "1";
	break;
	
	case "22-B":
	$special = "1";
	break;
	
	case "6-D":
	$special = "1";
	break;
	
	case "22-C":
	$special = "1";
	break;
	
	case "23-A":
	$special = "1";
	break;
	
	case "25-C":
	$special = "1";
	break;
	
	case "26-A":
	$special = "1";
	break;
	
	case "27-E":
	$special = "1";
	break;
	
	case "28-B":
	$special = "1";
	break;
	
	case "28-C":
	$special = "1";
	break;
	
	case "28-D":
	$special = "1";
	break;
	
	default:
	$special = "2";
	break;

}

// Get style name from broken parts
mysql_select_db($database, $brewing);
$query_style_name = "SELECT * FROM styles WHERE brewStyleGroup='$styleFix' AND brewStyleNum='$style[1]'";
$style_name = mysql_query($query_style_name, $brewing) or die(mysql_error());
$row_style_name = mysql_fetch_assoc($style_name);
$check = $row_style_name['brewStyleOwn'];

if ($row_prefs['prefsDisplaySpecial'] == "Y") { 
	switch($check) {
		case "custom":
		$custom = "1";
		break;
	
		case "bcoe":
		$custom = "2"; 
		break;
	}
} else $custom = "2";

$insertSQL = sprintf("
INSERT INTO brewing (
brewName,
brewStyle,
brewCategory, 
brewCategorySort, 
brewSubCategory, 
brewBottleDate, 
brewDate, 
brewYield, 
brewInfo, 
brewMead1, 
brewMead2, 
brewMead3, 
brewExtract1, 
brewExtract1Weight, 
brewExtract2, 
brewExtract2Weight, 
brewExtract3, 
brewExtract3Weight, 
brewExtract4, 
brewExtract4Weight, 
brewExtract5, brewExtract5Weight, 
brewGrain1, 
brewGrain1Weight, 
brewGrain2, 
brewGrain2Weight, 
brewGrain3, 
brewGrain3Weight, 
brewGrain4, 
brewGrain4Weight, 
brewGrain5, 
brewGrain5Weight, 
brewGrain6, 
brewGrain6Weight, 
brewGrain7, 
brewGrain7Weight, 
brewGrain8, 
brewGrain8Weight, 
brewGrain9, 
brewGrain9Weight, 
brewAddition1, 
brewAddition1Amt, 
brewAddition2, 
brewAddition2Amt, 
brewAddition3, 
brewAddition3Amt, 
brewAddition4, 
brewAddition4Amt, 
brewAddition5, 
brewAddition5Amt, 
brewAddition6, 
brewAddition6Amt, 
brewAddition7, 
brewAddition7Amt, 
brewAddition8, 
brewAddition8Amt, 
brewAddition9, 
brewAddition9Amt, 
brewHops1, 
brewHops1Weight, 
brewHops1IBU, 
brewHops1Time, 
brewHops2, 
brewHops2Weight, 
brewHops2IBU, 
brewHops2Time, 
brewHops3, 
brewHops3Weight, 
brewHops3IBU, 
brewHops3Time, 
brewHops4, 
brewHops4Weight, 
brewHops4IBU, 
brewHops4Time, 
brewHops5, 
brewHops5Weight, 
brewHops5IBU, 
brewHops5Time, 
brewHops6, 
brewHops6Weight, 
brewHops6IBU, 
brewHops6Time, 
brewHops7, 
brewHops7Weight, 
brewHops7IBU, 
brewHops7Time, 
brewHops8, 
brewHops8Weight, 
brewHops8IBU, 
brewHops8Time, 
brewHops9, 
brewHops9Weight, 
brewHops9IBU, 
brewHops9Time, 
brewHops1Use, 
brewHops2Use, 
brewHops3Use, 
brewHops4Use, 
brewHops5Use, 
brewHops6Use, 
brewHops7Use, 
brewHops8Use, 
brewHops9Use, 
brewHops1Type, 
brewHops2Type, 
brewHops3Type, 
brewHops4Type, 
brewHops5Type, 
brewHops6Type, 
brewHops7Type, 
brewHops8Type, 
brewHops9Type, 
brewHops1Form, 
brewHops2Form, 
brewHops3Form, 
brewHops4Form, 
brewHops5Form, 
brewHops6Form, 
brewHops7Form, 
brewHops8Form, 
brewHops9Form, 
brewYeast, 
brewYeastMan, 
brewYeastForm, 
brewYeastType, 
brewYeastAmount, 
brewYeastStarter, 
brewYeastNutrients, 
brewOG, 
brewFG, 
brewPrimary, 
brewPrimaryTemp, 
brewSecondary, 
brewSecondaryTemp, 
brewOther, 
brewOtherTemp, 
brewComments, 
brewMashStep1Name, 
brewMashStep1Temp, 
brewMashStep1Time, 
brewMashStep2Name, 
brewMashStep2Temp, 
brewMashStep2Time, 
brewMashStep3Name, 
brewMashStep3Temp, 
brewMashStep3Time, 
brewMashStep4Name, 
brewMashStep4Temp, 
brewMashStep4Time, 
brewMashStep5Name, 
brewMashStep5Temp, 
brewMashStep5Time, 
brewFinings, 
brewWaterNotes, 
brewBrewerID, 
brewCarbonationMethod, 
brewCarbonationVol, 
brewCarbonationNotes, 
brewBoilHours, 
brewBoilMins, 
brewBrewerFirstName, 
brewBrewerLastName, 
brewExtract1Use, 
brewExtract2Use, 
brewExtract3Use, 
brewExtract4Use, 
brewExtract5Use, 
brewGrain1Use, 
brewGrain2Use, 
brewGrain3Use, 
brewGrain4Use, 
brewGrain5Use, 
brewGrain6Use, 
brewGrain7Use, 
brewGrain8Use, 
brewGrain9Use, 
brewAddition1Use, 
brewAddition2Use, 
brewAddition3Use, 
brewAddition4Use, 
brewAddition5Use, 
brewAddition6Use, 
brewAddition7Use, 
brewAddition8Use, 
brewAddition9Use, 
brewJudgingLocation, 
brewCoBrewer,
brewJudgingNumber) VALUES 
(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['brewName']), "scrubbed"),
                       GetSQLValueString($row_style_name['brewStyle'], "text"),
					   GetSQLValueString($styleTrim, "text"),
					   GetSQLValueString($styleFix, "text"),
					   GetSQLValueString($style[1], "text"),
                       GetSQLValueString($_POST['brewBottleDate'], "date"),
                       GetSQLValueString($_POST['brewDate'], "date"),
                       GetSQLValueString($_POST['brewYield'], "text"),
                       GetSQLValueString($_POST['brewInfo'], "scrubbed"),
                       GetSQLValueString($_POST['brewMead1'], "text"),
                       GetSQLValueString($_POST['brewMead2'], "text"),
                       GetSQLValueString($_POST['brewMead3'], "text"),
                       GetSQLValueString($_POST['brewExtract1'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract1Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract2'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract2Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract3'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract3Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract4'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract4Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract5'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract5Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain1'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain1Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain2'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain2Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain3'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain3Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain4'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain4Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain5'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain5Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain6'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain6Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain7'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain7Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain8'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain8Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain9'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain9Weight'], "text"),
                       GetSQLValueString($_POST['brewAddition1'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition1Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition2'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition2Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition3'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition3Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition4'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition4Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition5'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition5Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition6'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition6Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition7'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition7Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition8'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition8Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition9'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition9Amt'], "text"),
                       GetSQLValueString($_POST['brewHops1'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops1Weight'], "text"),
                       GetSQLValueString($_POST['brewHops1IBU'], "text"),
                       GetSQLValueString($_POST['brewHops1Time'], "text"),
                       GetSQLValueString($_POST['brewHops2'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops2Weight'], "text"),
                       GetSQLValueString($_POST['brewHops2IBU'], "text"),
                       GetSQLValueString($_POST['brewHops2Time'], "text"),
                       GetSQLValueString($_POST['brewHops3'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops3Weight'], "text"),
                       GetSQLValueString($_POST['brewHops3IBU'], "text"),
                       GetSQLValueString($_POST['brewHops3Time'], "text"),
                       GetSQLValueString($_POST['brewHops4'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops4Weight'], "text"),
                       GetSQLValueString($_POST['brewHops4IBU'], "text"),
                       GetSQLValueString($_POST['brewHops4Time'], "text"),
                       GetSQLValueString($_POST['brewHops5'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops5Weight'], "text"),
                       GetSQLValueString($_POST['brewHops5IBU'], "text"),
                       GetSQLValueString($_POST['brewHops5Time'], "text"),
                       GetSQLValueString($_POST['brewHops6'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops6Weight'], "text"),
                       GetSQLValueString($_POST['brewHops6IBU'], "text"),
                       GetSQLValueString($_POST['brewHops6Time'], "text"),
                       GetSQLValueString($_POST['brewHops7'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops7Weight'], "text"),
                       GetSQLValueString($_POST['brewHops7IBU'], "text"),
                       GetSQLValueString($_POST['brewHops7Time'], "text"),
                       GetSQLValueString($_POST['brewHops8'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops8Weight'], "text"),
                       GetSQLValueString($_POST['brewHops8IBU'], "text"),
                       GetSQLValueString($_POST['brewHops8Time'], "text"),
                       GetSQLValueString($_POST['brewHops9'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops9Weight'], "text"),
                       GetSQLValueString($_POST['brewHops9IBU'], "text"),
                       GetSQLValueString($_POST['brewHops9Time'], "text"),
                       GetSQLValueString($_POST['brewHops1Use'], "text"),
                       GetSQLValueString($_POST['brewHops2Use'], "text"),
                       GetSQLValueString($_POST['brewHops3Use'], "text"),
                       GetSQLValueString($_POST['brewHops4Use'], "text"),
                       GetSQLValueString($_POST['brewHops5Use'], "text"),
                       GetSQLValueString($_POST['brewHops6Use'], "text"),
                       GetSQLValueString($_POST['brewHops7Use'], "text"),
                       GetSQLValueString($_POST['brewHops8Use'], "text"),
                       GetSQLValueString($_POST['brewHops9Use'], "text"),
                       GetSQLValueString($_POST['brewHops1Type'], "text"),
                       GetSQLValueString($_POST['brewHops2Type'], "text"),
                       GetSQLValueString($_POST['brewHops3Type'], "text"),
                       GetSQLValueString($_POST['brewHops4Type'], "text"),
                       GetSQLValueString($_POST['brewHops5Type'], "text"),
                       GetSQLValueString($_POST['brewHops6Type'], "text"),
                       GetSQLValueString($_POST['brewHops7Type'], "text"),
                       GetSQLValueString($_POST['brewHops8Type'], "text"),
                       GetSQLValueString($_POST['brewHops9Type'], "text"),
                       GetSQLValueString($_POST['brewHops1Form'], "text"),
                       GetSQLValueString($_POST['brewHops2Form'], "text"),
                       GetSQLValueString($_POST['brewHops3Form'], "text"),
                       GetSQLValueString($_POST['brewHops4Form'], "text"),
                       GetSQLValueString($_POST['brewHops5Form'], "text"),
                       GetSQLValueString($_POST['brewHops6Form'], "text"),
                       GetSQLValueString($_POST['brewHops7Form'], "text"),
                       GetSQLValueString($_POST['brewHops8Form'], "text"),
                       GetSQLValueString($_POST['brewHops9Form'], "text"),
                       GetSQLValueString($_POST['brewYeast'], "scrubbed"),
                       GetSQLValueString($_POST['brewYeastMan'], "scrubbed"),
                       GetSQLValueString($_POST['brewYeastForm'], "text"),
                       GetSQLValueString($_POST['brewYeastType'], "text"),
                       GetSQLValueString($_POST['brewYeastAmount'], "text"),
                       GetSQLValueString($_POST['brewYeastStarter'], "text"),
                       GetSQLValueString($_POST['brewYeastNutrients'], "scrubbed"),
                       GetSQLValueString($_POST['brewOG'], "text"),
                       GetSQLValueString($_POST['brewFG'], "text"),
                       GetSQLValueString($_POST['brewPrimary'], "text"),
                       GetSQLValueString($_POST['brewPrimaryTemp'], "text"),
                       GetSQLValueString($_POST['brewSecondary'], "text"),
                       GetSQLValueString($_POST['brewSecondaryTemp'], "text"),
                       GetSQLValueString($_POST['brewOther'], "text"),
                       GetSQLValueString($_POST['brewOtherTemp'], "text"),
                       GetSQLValueString($_POST['brewComments'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep1Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep1Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep1Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep2Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep2Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep2Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep3Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep3Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep3Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep4Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep4Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep4Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep5Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep5Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep5Time'], "text"),
                       GetSQLValueString($_POST['brewFinings'], "scrubbed"),
                       GetSQLValueString($_POST['brewWaterNotes'], "scrubbed"),
                       GetSQLValueString($brewBrewerID, "text"),
					   GetSQLValueString($_POST['brewCarbonationMethod'], "text"),
					   GetSQLValueString($_POST['brewCarbonationVol'], "text"),
					   GetSQLValueString($_POST['brewCarbonationNotes'], "scrubbed"),
					   GetSQLValueString($_POST['brewBoilHours'], "text"),
					   GetSQLValueString($_POST['brewBoilMins'], "text"),
					   GetSQLValueString($brewBrewerFirstName, "text"),
					   GetSQLValueString($brewBrewerLastName, "text"),
					   GetSQLValueString($_POST['brewExtract1Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract2Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract3Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract4Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract5Use'], "text"),
					   GetSQLValueString($_POST['brewGrain1Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain2Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain3Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain4Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain5Use'], "text"),
					   GetSQLValueString($_POST['brewGrain6Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain7Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain8Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain9Use'], "text"),
					   GetSQLValueString($_POST['brewAddition1Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition2Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition3Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition4Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition5Use'], "text"),
					   GetSQLValueString($_POST['brewAddition6Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition7Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition8Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition9Use'], "text"),
					   GetSQLValueString($row_style_name['brewStyleJudgingLoc'], "int"),
					   GetSQLValueString($_POST['brewCoBrewer'], "text"),
					   GetSQLValueString(generate_judging_num($styleTrim), "text")
					   );

  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());

  if (($_POST['brewInfo'] == "") && (($special == "1") || ($custom == "1")) && ($id == "default")) {
	mysql_select_db($database, $brewing);
	$query_brew_id = "SELECT id FROM brewing WHERE brewBrewerID='$brewBrewerID' ORDER BY id DESC LIMIT 1";
	$brew_id = mysql_query($query_brew_id, $brewing) or die(mysql_error());
	$row_brew_id = mysql_fetch_assoc($brew_id);
	$id = $row_brew_id['id'];
	}
  
  if (($section == "admin") && (($_POST['brewInfo'] != "") && (($special == "1") || ($custom == "1"))))  $insertGoTo = "../index.php?section=admin&go=entries";
  elseif (($_POST['brewInfo'] == "") && (($special == "1") || ($custom == "1"))) {
		if ($section == "admin") $insertGoTo = "../index.php?section=brew&go=entries&filter=$filter&action=edit&id=$id&msg=1";
		else $insertGoTo = "../index.php?section=brew&action=edit&id=$id&msg=1";
  }
  //elseif (($row_user['userLevel'] == "1") && ($filter != $row_user['id'])) $insertGoTo = "../index.php?section=admin&go=entries&msg=1";
 else $insertGoTo = "../index.php?section=list";
 header(sprintf("Location: %s", $insertGoTo));
}

// --------------------------- If Editing an Entry ------------------------------- //

if (($action == "edit") && ($dbTable == "brewing")) {

$query_user = sprintf("SELECT * FROM users WHERE user_name = '%s'", $_SESSION["loginUsername"]);
$user = mysql_query($query_user, $brewing) or die(mysql_error());
$row_user = mysql_fetch_assoc($user);
$totalRows_user = mysql_num_rows($user);

if ($row_user['userLevel'] == 1) { 
$nameBreak = $_POST['brewBrewerID'];
$name = explode('*', $nameBreak);
$brewBrewerID = $name[0];
$brewBrewerLastName = $name[1];
$brewBrewerFirstName = $name[2];
}
else {
$brewBrewerID = $_POST['brewBrewerID'];
$brewBrewerLastName = $_POST['brewBrewerLastName']; 
$brewBrewerFirstName = $_POST['brewBrewerFirstName'];
}

$styleBreak = $_POST['brewStyle'];
$style = explode('-', $styleBreak);
$styleTrim = ltrim($style[0], "0"); 
if ($style [0] < 10) $styleFix = "0".$style[0]; else $styleFix = $style[0];

switch($styleBreak) {
	case "6-D":
	$special = "1";
	break;
	
	case "16-E":
	$special = "1";
	break;
	
	case "17-F":
	$special = "1";
	break;
	
	case "20-A":
	$special = "1";
	break;
	
	case "21-A":
	$special = "1";
	break;
	
	case "22-B":
	$special = "1";
	break;
	
	case "6-D":
	$special = "1";
	break;
	
	case "22-C":
	$special = "1";
	break;
	
	case "23-A":
	$special = "1";
	break;
	
	case "25-C":
	$special = "1";
	break;
	
	case "26-A":
	$special = "1";
	break;
	
	case "27-E":
	$special = "1";
	break;
	
	case "28-B":
	$special = "1";
	break;
	
	case "28-C":
	$special = "1";
	break;
	
	case "28-D":
	$special = "1";
	break;
	
	default:
	$special = "2";
	break;

}

// Get style name from broken parts
mysql_select_db($database, $brewing);
$query_style_name = "SELECT * FROM styles WHERE brewStyleGroup='$styleFix' AND brewStyleNum='$style[1]'";
$style_name = mysql_query($query_style_name, $brewing) or die(mysql_error());
$row_style_name = mysql_fetch_assoc($style_name);
$check = $row_style_name['brewStyleOwn'];

if ($row_prefs['prefsDisplaySpecial'] == "Y") { 
	switch($check) {
		case "custom":
		$custom = "1";
		break;
	
		case "bcoe":
		$custom = "2"; 
		break;
	}
} else $custom = "2";

$updateSQL = sprintf("UPDATE brewing SET brewName=%s, brewStyle=%s, brewCategory=%s, brewCategorySort=%s, brewSubCategory=%s, brewBottleDate=%s, brewDate=%s, brewYield=%s, brewInfo=%s, brewMead1=%s, brewMead2=%s, brewMead3=%s, brewExtract1=%s, brewExtract1Weight=%s, brewExtract2=%s, brewExtract2Weight=%s, brewExtract3=%s, brewExtract3Weight=%s, brewExtract4=%s, brewExtract4Weight=%s, brewExtract5=%s, brewExtract5Weight=%s, brewGrain1=%s, brewGrain1Weight=%s, brewGrain2=%s, brewGrain2Weight=%s, brewGrain3=%s, brewGrain3Weight=%s, brewGrain4=%s, brewGrain4Weight=%s, brewGrain5=%s, brewGrain5Weight=%s, brewGrain6=%s, brewGrain6Weight=%s, brewGrain7=%s, brewGrain7Weight=%s, brewGrain8=%s, brewGrain8Weight=%s, brewGrain9=%s, brewGrain9Weight=%s, brewAddition1=%s, brewAddition1Amt=%s, brewAddition2=%s, brewAddition2Amt=%s, brewAddition3=%s, brewAddition3Amt=%s, brewAddition4=%s, brewAddition4Amt=%s, brewAddition5=%s, brewAddition5Amt=%s, brewAddition6=%s, brewAddition6Amt=%s, brewAddition7=%s, brewAddition7Amt=%s, brewAddition8=%s, brewAddition8Amt=%s, brewAddition9=%s, brewAddition9Amt=%s, brewHops1=%s, brewHops1Weight=%s, brewHops1IBU=%s, brewHops1Time=%s, brewHops2=%s, brewHops2Weight=%s, brewHops2IBU=%s, brewHops2Time=%s, brewHops3=%s, brewHops3Weight=%s, brewHops3IBU=%s, brewHops3Time=%s, brewHops4=%s, brewHops4Weight=%s, brewHops4IBU=%s, brewHops4Time=%s, brewHops5=%s, brewHops5Weight=%s, brewHops5IBU=%s, brewHops5Time=%s, brewHops6=%s, brewHops6Weight=%s, brewHops6IBU=%s, brewHops6Time=%s, brewHops7=%s, brewHops7Weight=%s, brewHops7IBU=%s, brewHops7Time=%s, brewHops8=%s, brewHops8Weight=%s, brewHops8IBU=%s, brewHops8Time=%s, brewHops9=%s, brewHops9Weight=%s, brewHops9IBU=%s, brewHops9Time=%s, brewHops1Use=%s, brewHops2Use=%s, brewHops3Use=%s, brewHops4Use=%s, brewHops5Use=%s, brewHops6Use=%s, brewHops7Use=%s, brewHops8Use=%s, brewHops9Use=%s, brewHops1Type=%s, brewHops2Type=%s, brewHops3Type=%s, brewHops4Type=%s, brewHops5Type=%s, brewHops6Type=%s, brewHops7Type=%s, brewHops8Type=%s, brewHops9Type=%s, brewHops1Form=%s, brewHops2Form=%s, brewHops3Form=%s, brewHops4Form=%s, brewHops5Form=%s, brewHops6Form=%s, brewHops7Form=%s, brewHops8Form=%s, brewHops9Form=%s, brewYeast=%s, brewYeastMan=%s, brewYeastForm=%s, brewYeastType=%s, brewYeastAmount=%s, brewYeastStarter=%s, brewYeastNutrients=%s, brewOG=%s, brewFG=%s, brewPrimary=%s, brewPrimaryTemp=%s, brewSecondary=%s, brewSecondaryTemp=%s, brewOther=%s, brewOtherTemp=%s, brewComments=%s, brewMashStep1Name=%s, brewMashStep1Temp=%s, brewMashStep1Time=%s, brewMashStep2Name=%s, brewMashStep2Temp=%s, brewMashStep2Time=%s, brewMashStep3Name=%s, brewMashStep3Temp=%s, brewMashStep3Time=%s, brewMashStep4Name=%s, brewMashStep4Temp=%s, brewMashStep4Time=%s, brewMashStep5Name=%s, brewMashStep5Temp=%s, brewMashStep5Time=%s, brewFinings=%s, brewWaterNotes=%s, brewBrewerID=%s, brewCarbonationMethod=%s, brewCarbonationVol=%s, brewCarbonationNotes=%s, brewBoilHours=%s, brewBoilMins=%s, brewBrewerFirstName=%s, brewBrewerLastName=%s, brewExtract1Use=%s, brewExtract2Use=%s, brewExtract3Use=%s, brewExtract4Use=%s, brewExtract5Use=%s, brewGrain1Use=%s, brewGrain2Use=%s, brewGrain3Use=%s, brewGrain4Use=%s, brewGrain5Use=%s, brewGrain6Use=%s, brewGrain7Use=%s, brewGrain8Use=%s, brewGrain9Use=%s, brewAddition1Use=%s, brewAddition2Use=%s, brewAddition3Use=%s, brewAddition4Use=%s, brewAddition5Use=%s, brewAddition6Use=%s, brewAddition7Use=%s, brewAddition8Use=%s, brewAddition9Use=%s, brewJudgingLocation=%s, brewCoBrewer=%s WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['brewName']), "scrubbed"),
                       GetSQLValueString($row_style_name['brewStyle'], "text"),
					   GetSQLValueString($styleTrim, "text"),
					   GetSQLValueString($styleFix, "text"),
					   GetSQLValueString($style[1], "text"),
                       GetSQLValueString($_POST['brewBottleDate'], "date"),
                       GetSQLValueString($_POST['brewDate'], "date"),
                       GetSQLValueString($_POST['brewYield'], "text"),
                       GetSQLValueString($_POST['brewInfo'], "text"),
                       GetSQLValueString($_POST['brewMead1'], "text"),
                       GetSQLValueString($_POST['brewMead2'], "text"),
                       GetSQLValueString($_POST['brewMead3'], "text"),
                       GetSQLValueString($_POST['brewExtract1'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract1Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract2'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract2Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract3'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract3Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract4'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract4Weight'], "text"),
                       GetSQLValueString($_POST['brewExtract5'], "scrubbed"),
                       GetSQLValueString($_POST['brewExtract5Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain1'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain1Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain2'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain2Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain3'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain3Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain4'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain4Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain5'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain5Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain6'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain6Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain7'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain7Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain8'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain8Weight'], "text"),
                       GetSQLValueString($_POST['brewGrain9'], "scrubbed"),
                       GetSQLValueString($_POST['brewGrain9Weight'], "text"),
                       GetSQLValueString($_POST['brewAddition1'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition1Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition2'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition2Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition3'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition3Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition4'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition4Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition5'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition5Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition6'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition6Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition7'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition7Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition8'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition8Amt'], "text"),
                       GetSQLValueString($_POST['brewAddition9'], "scrubbed"),
                       GetSQLValueString($_POST['brewAddition9Amt'], "text"),
                       GetSQLValueString($_POST['brewHops1'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops1Weight'], "text"),
                       GetSQLValueString($_POST['brewHops1IBU'], "text"),
                       GetSQLValueString($_POST['brewHops1Time'], "text"),
                       GetSQLValueString($_POST['brewHops2'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops2Weight'], "text"),
                       GetSQLValueString($_POST['brewHops2IBU'], "text"),
                       GetSQLValueString($_POST['brewHops2Time'], "text"),
                       GetSQLValueString($_POST['brewHops3'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops3Weight'], "text"),
                       GetSQLValueString($_POST['brewHops3IBU'], "text"),
                       GetSQLValueString($_POST['brewHops3Time'], "text"),
                       GetSQLValueString($_POST['brewHops4'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops4Weight'], "text"),
                       GetSQLValueString($_POST['brewHops4IBU'], "text"),
                       GetSQLValueString($_POST['brewHops4Time'], "text"),
                       GetSQLValueString($_POST['brewHops5'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops5Weight'], "text"),
                       GetSQLValueString($_POST['brewHops5IBU'], "text"),
                       GetSQLValueString($_POST['brewHops5Time'], "text"),
                       GetSQLValueString($_POST['brewHops6'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops6Weight'], "text"),
                       GetSQLValueString($_POST['brewHops6IBU'], "text"),
                       GetSQLValueString($_POST['brewHops6Time'], "text"),
                       GetSQLValueString($_POST['brewHops7'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops7Weight'], "text"),
                       GetSQLValueString($_POST['brewHops7IBU'], "text"),
                       GetSQLValueString($_POST['brewHops7Time'], "text"),
                       GetSQLValueString($_POST['brewHops8'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops8Weight'], "text"),
                       GetSQLValueString($_POST['brewHops8IBU'], "text"),
                       GetSQLValueString($_POST['brewHops8Time'], "text"),
                       GetSQLValueString($_POST['brewHops9'], "scrubbed"),
                       GetSQLValueString($_POST['brewHops9Weight'], "text"),
                       GetSQLValueString($_POST['brewHops9IBU'], "text"),
                       GetSQLValueString($_POST['brewHops9Time'], "text"),
                       GetSQLValueString($_POST['brewHops1Use'], "text"),
                       GetSQLValueString($_POST['brewHops2Use'], "text"),
                       GetSQLValueString($_POST['brewHops3Use'], "text"),
                       GetSQLValueString($_POST['brewHops4Use'], "text"),
                       GetSQLValueString($_POST['brewHops5Use'], "text"),
                       GetSQLValueString($_POST['brewHops6Use'], "text"),
                       GetSQLValueString($_POST['brewHops7Use'], "text"),
                       GetSQLValueString($_POST['brewHops8Use'], "text"),
                       GetSQLValueString($_POST['brewHops9Use'], "text"),
                       GetSQLValueString($_POST['brewHops1Type'], "text"),
                       GetSQLValueString($_POST['brewHops2Type'], "text"),
                       GetSQLValueString($_POST['brewHops3Type'], "text"),
                       GetSQLValueString($_POST['brewHops4Type'], "text"),
                       GetSQLValueString($_POST['brewHops5Type'], "text"),
                       GetSQLValueString($_POST['brewHops6Type'], "text"),
                       GetSQLValueString($_POST['brewHops7Type'], "text"),
                       GetSQLValueString($_POST['brewHops8Type'], "text"),
                       GetSQLValueString($_POST['brewHops9Type'], "text"),
                       GetSQLValueString($_POST['brewHops1Form'], "text"),
                       GetSQLValueString($_POST['brewHops2Form'], "text"),
                       GetSQLValueString($_POST['brewHops3Form'], "text"),
                       GetSQLValueString($_POST['brewHops4Form'], "text"),
                       GetSQLValueString($_POST['brewHops5Form'], "text"),
                       GetSQLValueString($_POST['brewHops6Form'], "text"),
                       GetSQLValueString($_POST['brewHops7Form'], "text"),
                       GetSQLValueString($_POST['brewHops8Form'], "text"),
                       GetSQLValueString($_POST['brewHops9Form'], "text"),
                       GetSQLValueString($_POST['brewYeast'], "scrubbed"),
                       GetSQLValueString($_POST['brewYeastMan'], "scrubbed"),
                       GetSQLValueString($_POST['brewYeastForm'], "text"),
                       GetSQLValueString($_POST['brewYeastType'], "text"),
                       GetSQLValueString($_POST['brewYeastAmount'], "scrubbed"),
                       GetSQLValueString($_POST['brewYeastStarter'], "text"),
                       GetSQLValueString($_POST['brewYeastNutrients'], "scrubbed"),
                       GetSQLValueString($_POST['brewOG'], "text"),
                       GetSQLValueString($_POST['brewFG'], "text"),
                       GetSQLValueString($_POST['brewPrimary'], "text"),
                       GetSQLValueString($_POST['brewPrimaryTemp'], "text"),
                       GetSQLValueString($_POST['brewSecondary'], "text"),
                       GetSQLValueString($_POST['brewSecondaryTemp'], "text"),
                       GetSQLValueString($_POST['brewOther'], "text"),
                       GetSQLValueString($_POST['brewOtherTemp'], "text"),
                       GetSQLValueString($_POST['brewComments'], "text"),
                       GetSQLValueString($_POST['brewMashStep1Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep1Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep1Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep2Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep2Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep2Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep3Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep3Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep3Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep4Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep4Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep4Time'], "text"),
                       GetSQLValueString($_POST['brewMashStep5Name'], "scrubbed"),
                       GetSQLValueString($_POST['brewMashStep5Temp'], "text"),
                       GetSQLValueString($_POST['brewMashStep5Time'], "text"),
                       GetSQLValueString($_POST['brewFinings'], "scrubbed"),
                       GetSQLValueString($_POST['brewWaterNotes'], "scrubbed"),
					   GetSQLValueString($brewBrewerID, "text"),
					   GetSQLValueString($_POST['brewCarbonationMethod'], "text"),
					   GetSQLValueString($_POST['brewCarbonationVol'], "text"),
					   GetSQLValueString($_POST['brewCarbonationNotes'], "scrubbed"),
					   GetSQLValueString($_POST['brewBoilHours'], "text"),
					   GetSQLValueString($_POST['brewBoilMins'], "text"),
					   GetSQLValueString($brewBrewerFirstName, "text"),
					   GetSQLValueString($brewBrewerLastName, "text"),
					   GetSQLValueString($_POST['brewExtract1Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract2Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract3Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract4Use'], "text"), 
					   GetSQLValueString($_POST['brewExtract5Use'], "text"),
					   GetSQLValueString($_POST['brewGrain1Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain2Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain3Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain4Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain5Use'], "text"),
					   GetSQLValueString($_POST['brewGrain6Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain7Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain8Use'], "text"), 
					   GetSQLValueString($_POST['brewGrain9Use'], "text"),
					   GetSQLValueString($_POST['brewAddition1Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition2Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition3Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition4Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition5Use'], "text"),
					   GetSQLValueString($_POST['brewAddition6Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition7Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition8Use'], "text"), 
					   GetSQLValueString($_POST['brewAddition9Use'], "text"),
					   GetSQLValueString($row_style_name['brewStyleJudgingLoc'], "int"),
					   GetSQLValueString($_POST['brewCoBrewer'], "text"),
                       GetSQLValueString($id, "int"));
  
  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  
  if (($section == "admin") && (($_POST['brewInfo'] != "") && (($special == "1") || ($custom == "1")))) $updateGoTo = "../index.php?section=admin&go=entries";
  elseif ($go == "beerXML") $updateGoTo = "../index.php?section=".$section."&go=".$go."&filter=".$filter."&msg=3";
  elseif (($_POST['brewInfo'] == "") && (($special == "1") || ($custom == "1"))) {
		if ($section == "admin") $updateGoTo = "../index.php?section=brew&go=entries&filter=$filter&action=edit&id=$id&msg=1";
		else $updateGoTo = "../index.php?section=brew&action=edit&id=$id&msg=1";
  }
  //elseif (($row_user['userLevel'] == "1") && ($filter != $row_user['id'])) $updateGoTo = "../index.php?section=admin&go=entries&msg=2";
  else  $updateGoTo = "../index.php?section=list";
 /*
 echo $special."<br>";
 echo $check."<br>";
 echo $custom."<br>";
 echo $updateGoTo."<br>";
 */
 header(sprintf("Location: %s", $updateGoTo));
}

// --------------------------- If Adding a Participant (User) ------------------------------- //

if (($action == "add") && ($dbTable == "users") && (($section == "register") || ($section == "admin"))) {
// Check to see if email address is already in the system. If so, redirect.
$username = $_POST['user_name'];

if ((strstr($username,'@')) && (strstr($username,'.'))) {
mysql_select_db($database, $brewing);
$query_userCheck = "SELECT user_name FROM users WHERE user_name = '$username'";
$userCheck = mysql_query($query_userCheck, $brewing) or die(mysql_error());
$row_userCheck = mysql_fetch_assoc($userCheck);
$totalRows_userCheck = mysql_num_rows($userCheck);

if ($totalRows_userCheck > 0) {
  if ($section == "admin") $msg = "10"; else $msg = "2";
  header("Location: ../index.php?section=".$section."&go=".$go."&action=".$action."&msg=".$msg);
  }
  else 
  {
  $password = md5($_POST['password']);
  $insertSQL = sprintf("INSERT INTO users (user_name, userLevel, password, userQuestion, userQuestionAnswer) VALUES (%s, %s, %s, %s, %s)", 
                       GetSQLValueString($username, "text"),
					   GetSQLValueString($_POST['userLevel'], "text"),
                       GetSQLValueString($password, "text"),
					   GetSQLValueString($_POST['userQuestion'], "text"),
					   GetSQLValueString($_POST['userQuestionAnswer'], "text"));
  	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	
	if ($section != "admin") {

	mysql_select_db($database, $brewing);
	$query_login = "SELECT password FROM users WHERE user_name = '$username' AND password = '$password'";
	$login = mysql_query($query_login, $brewing) or die(mysql_error());
	$row_login = mysql_fetch_assoc($login);
	$totalRows_login = mysql_num_rows($login);

	session_start();
		// Authenticate the user
		if ($totalRows_login == 1)
			{
  			// Register the loginUsername
  			$_SESSION["loginUsername"] = $username;

  			// If the username/password combo is OK, relocate to the "protected" content index page
  			header("Location: ../index.php?action=add&section=brewer&go=".$go."&msg=1");
  			exit;
			}
		else
			{
  			// If the username/password combo is incorrect or not found, relocate to the login error page
  			header("Location: ../index.php?section=login&go=".$go."&msg=1");
  			session_destroy();
  			exit;
			}
	}
	
	if ($section == "admin") {
	header("Location: ../index.php?section=".$section."&go=".$go."&action=".$action."&filter=info&msg=1&username=".$username);
	
	}
	
/*
  $insertGoTo = "../index.php?section=login&username=".$username;
  header(sprintf("Location: %s", $insertGoTo));
*/
  }
 }
 else 
 {
 header("Location: ../index.php?section=".$section."&go=".$go."&action=".$action."&msg=3");
 }
}

// --------------------------- If Editing a Participant ------------------------------- //


if (($action == "edit") && ($dbTable == "users")) {
// Check to see if email address is already in the system. If so, redirect.
$username = $_POST['user_name'];
$usernameOld = $_POST['user_name_old'];
if ((strstr($username,'@')) && (strstr($username,'.'))) {

mysql_select_db($database, $brewing);
$query_brewerCheck = "SELECT brewerEmail FROM brewer WHERE brewerEmail = '$usernameOld'";
$brewerCheck = mysql_query($query_brewerCheck, $brewing) or die(mysql_error());
$row_brewerCheck = mysql_fetch_assoc($brewerCheck);
$totalRows_brewerCheck = mysql_num_rows($brewerCheck);

mysql_select_db($database, $brewing);
$query_userCheck = "SELECT * FROM users WHERE user_name = '$username'";
$userCheck = mysql_query($query_userCheck, $brewing) or die(mysql_error());
$row_userCheck = mysql_fetch_assoc($userCheck);
$totalRows_userCheck = mysql_num_rows($userCheck);

// --------------------------- If Changing a Participant's User Level ------------------------------- //
if ($go == "make_admin") {
$updateSQL = sprintf("UPDATE users SET userLevel=%s WHERE user_name=%s", 
					   GetSQLValueString($_POST['userLevel'], "text"),
                       GetSQLValueString($_POST['user_name'], "text"));
					   
  mysql_select_db($database, $brewing);
  $Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
  header(sprintf("Location: %s", $updateGoTo));  
}

// --------------------------- If Changing a Participant's User Name ------------------------------- //
if ($go == "username") {
if ($totalRows_userCheck > 0) {
  header("Location: ../index.php?section=user&action=username&id=".$id."&msg=1");
  }
  else 
  {  
  $updateSQL = sprintf("UPDATE users SET user_name=%s, userLevel=%s WHERE id=%s", 
                       GetSQLValueString($_POST['user_name'], "text"),
					   GetSQLValueString($_POST['userLevel'], "text"),
                       GetSQLValueString($id, "text")); 

  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());

  $update2SQL = sprintf("UPDATE brewer SET brewerEmail=%s WHERE brewerEmail=%s", 
                       GetSQLValueString($_POST['user_name'], "text"),
                       GetSQLValueString($usernameOld, "text")); 

  mysql_select_db($database, $brewing);
  $Result2 = mysql_query($update2SQL, $brewing) or die(mysql_error());

  	mysql_select_db($database, $brewing);
  	$query_login = "SELECT user_name FROM users WHERE user_name = '$username'";
  	$login = mysql_query($query_login, $brewing) or die(mysql_error());
	$row_login = mysql_fetch_assoc($login);
	$totalRows_login = mysql_num_rows($login);
	
	session_destroy;
	session_start();
		// Authenticate the user
		if ($totalRows_login == 1)
			{
  			// Register the loginUsername
  			$_SESSION["loginUsername"] = $username;

  			// If the username/password combo is OK, relocate to the "protected" content index page
  			header("Location: ../index.php?section=list&msg=3");
  			exit;
			}
		else
			{
  			// If the username/password combo is incorrect or not found, relocate to the login error page
  			header("Location: ../index.php?section=user&action=username&msg=2");
  			session_destroy();
  			exit;
			}
/*
  $insertGoTo = "../index.php?section=login&username=".$username;
  header(sprintf("Location: %s", $insertGoTo));
*/
  }
 }
}
 else 
 {
 header("Location: ../index.php?section=user&action=username&msg=4&id=".$id);
 }

// --------------------------- If Changing a Paricipant's Password ------------------------------- //
if ($go == "password") {

// Check if old password is correct; if not redirect
$passwordOld = md5($_POST['passwordOld']);
$passwordNew = md5($_POST['password']);
mysql_select_db($database, $brewing);
$query_userPass = "SELECT password FROM users WHERE password = '$passwordOld'";
$userPass = mysql_query($query_userPass, $brewing) or die(mysql_error());
$row_userPass = mysql_fetch_assoc($userPass);
$totalRows_userPass = mysql_num_rows($userPass);

if ($passwordOld != $row_userPass['password']) {
  header("Location: ../index.php?section=user&action=password&msg=3&id=".$id);
  }
  else 
  {  
  $updateSQL = sprintf("UPDATE users SET password=%s WHERE id=%s", 
                       GetSQLValueString($passwordNew, "text"),
                       GetSQLValueString($id, "text")); 

  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  
  header("Location: ../index.php?section=list&id=".$id."&msg=4");
  }
 }
}

// --------------------------- SETUP: Adding the Admin Participant ------------------------------- //
if (($action == "add") && ($dbTable == "users") && ($section == "setup")) {
  	
	$username = $_POST['user_name'];
  	if ((strstr($username,'@')) && (strstr($username,'.'))) {
	
	$password = md5($_POST['password']);
    $insertSQL = sprintf("INSERT INTO users (user_name, userLevel, password, userQuestion, userQuestionAnswer) VALUES (%s, %s, %s, %s, %s)", 
                       GetSQLValueString($_POST['user_name'], "text"),
					   GetSQLValueString($_POST['userLevel'], "text"),
                       GetSQLValueString($password, "text"), 
					   GetSQLValueString($_POST['userQuestion'], "text"),
					   GetSQLValueString($_POST['userQuestionAnswer'], "text"));

  	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());

	$insertGoTo = "../setup.php?section=step2&go=".$username;
	header(sprintf("Location: %s", $insertGoTo));	
	
	session_start();
  	$_SESSION["loginUsername"] = $username;
	
	}
	else header("Location: ../setup.php?section=step1&msg=1");
}

// --------------------------- Adding Participant's or Admin's Info ------------------------------- //

if (($action == "add") && ($dbTable == "brewer")) {
require(DB.'judging_locations.db.php');
if ($totalRows_judging < 2) {  
$location_pref1 = $_POST['brewerJudgeLocation'];
$location_pref2 = $_POST['brewerStewardLocation'];
} else { 
if ($go == "judge") {
	$location_pref1 = implode(",",$_POST['brewerJudgeLocation']);
	$location_pref2 = implode(",",$_POST['brewerStewardLocation']);
	}
}

  $insertSQL = sprintf("INSERT INTO brewer (
  uid,
  brewerFirstName, 
  brewerLastName, 
  brewerAddress, 
  brewerCity, 
  brewerState, 
  
  brewerZip,
  brewerCountry,
  brewerPhone1, 
  brewerPhone2, 
  brewerClubs, 
  brewerEmail, 
  
  brewerSteward, 
  brewerJudge,
  brewerJudgeID,
  brewerJudgeMead,
  brewerJudgeRank,
  brewerJudgeLocation,
  brewerStewardLocation,
  brewerAHA
  ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['uid'], "int"),
					   GetSQLValueString(capitalize($_POST['brewerFirstName']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerLastName']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerAddress']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerCity']), "text"),
                       GetSQLValueString($_POST['brewerState'], "text"),
                       GetSQLValueString($_POST['brewerZip'], "text"),
                       GetSQLValueString($_POST['brewerCountry'], "text"),
					   GetSQLValueString($_POST['brewerPhone1'], "text"),
                       GetSQLValueString($_POST['brewerPhone2'], "text"),
                       GetSQLValueString(capitalize($_POST['brewerClubs']), "text"),
                       GetSQLValueString($_POST['brewerEmail'], "text"),
					   GetSQLValueString($_POST['brewerSteward'], "text"),
					   GetSQLValueString($_POST['brewerJudge'], "text"),
					   GetSQLValueString($_POST['brewerJudgeID'], "text"),
					   GetSQLValueString($_POST['brewerJudgeMead'], "text"),
					   GetSQLValueString($_POST['brewerJudgeRank'], "text"),
					   GetSQLValueString($location_pref1, "text"),
					   GetSQLValueString($location_pref2, "text"),
					   GetSQLValueString($_POST['brewerAHA'], "int")
					   );

	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	//echo $insertSQL;
	if ($section == "setup") $insertGoTo = "../setup.php?section=step3";
	elseif ($_POST['brewerJudge'] == "Y") $insertGoTo = "../index.php?section=judge&go=judge";
    elseif ($section == "admin") $insertGoTo = "../index.php?section=admin&go=participants&msg=1&username=".$username;
	else $insertGoTo = $insertGoTo; 
	header(sprintf("Location: %s", $insertGoTo));
}

// --------------------------- If Editing a Participant's Information ------------------------------- //

if (($action == "edit") && ($dbTable == "brewer")) {
require(DB.'judging_locations.db.php');
if ($totalRows_judging > 1) {  
	if (($_POST['brewerJudgeLocation'] != "") && (!strstr($_POST['brewerJudgeLocation'],","))) $location_pref1 = implode(",",$_POST['brewerJudgeLocation']); 
	elseif (($_POST['brewerJudgeLocation'] != "") && (strstr($_POST['brewerJudgeLocation'],","))) $location_pref1 = $_POST['brewerJudgeLocation']; 
	else $location_pref1 = "";
	if (($_POST['brewerStewardLocation'] != "") && (!strstr($_POST['brewerStewardLocation'],","))) $location_pref2 = implode(",",$_POST['brewerStewardLocation']); 
	elseif (($_POST['brewerJudgeLocation'] != "") && (strstr($_POST['brewerStewardLocation'],","))) $location_pref2 = $_POST['brewerStewardLocation'];
	else $location_pref2 = "";
} 
else
{ 
$location_pref1 = $_POST['brewerJudgeLocation'];
$location_pref2 = $_POST['brewerStewardLocation'];
}
if ($_POST['brewerJudgeLikes'] != "") $likes = implode(",",$_POST['brewerJudgeLikes']); else $likes = "";
if ($_POST['brewerJudgeDislikes'] != "") $dislikes = implode(",",$_POST['brewerJudgeDislikes']); else $dislikes = "";

$updateSQL = sprintf("UPDATE brewer SET 
uid=%s,
brewerFirstName=%s, 
brewerLastName=%s, 
brewerAddress=%s, 
brewerCity=%s, 
brewerState=%s, 

brewerZip=%s, 
brewerCountry=%s, 
brewerPhone1=%s, 
brewerPhone2=%s, 
brewerClubs=%s, 
brewerEmail=%s, 

brewerSteward=%s, 
brewerJudge=%s, 
brewerJudgeID=%s, 
brewerJudgeMead=%s, 
brewerJudgeRank=%s, 
brewerJudgeLikes=%s, 
brewerJudgeDislikes=%s, 
brewerJudgeLocation=%s, 
brewerStewardLocation=%s,

brewerAHA=%s
WHERE id=%s",
                       GetSQLValueString($_POST['uid'], "int"),
					   GetSQLValueString(capitalize($_POST['brewerFirstName']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerLastName']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerAddress']), "text"),
                       GetSQLValueString(capitalize($_POST['brewerCity']), "text"),
                       GetSQLValueString($_POST['brewerState'], "text"),
                       GetSQLValueString($_POST['brewerZip'], "text"),
                       GetSQLValueString($_POST['brewerCountry'], "text"),
					   GetSQLValueString($_POST['brewerPhone1'], "text"),
                       GetSQLValueString($_POST['brewerPhone2'], "text"),
                       GetSQLValueString(capitalize($_POST['brewerClubs']), "text"),
                       GetSQLValueString($_POST['brewerEmail'], "text"),
                       GetSQLValueString($_POST['brewerSteward'], "text"),
                       GetSQLValueString($_POST['brewerJudge'], "text"),
                       GetSQLValueString($_POST['brewerJudgeID'], "text"),
					   GetSQLValueString($_POST['brewerJudgeMead'], "text"),
                       GetSQLValueString($_POST['brewerJudgeRank'], "text"),
                       GetSQLValueString($likes, "text"),
                       GetSQLValueString($dislikes, "text"),
					   GetSQLValueString($location_pref1, "text"),
					   GetSQLValueString($location_pref2, "text"),
					   //GetSQLValueString($_POST['brewerAssignment'], "text"),
					   //GetSQLValueString($_POST['brewerAssignmentStaff'], "text"),
					   GetSQLValueString($_POST['brewerAHA'], "text"),
                       GetSQLValueString($id, "int"));
  
  if ($_POST['brewerAssignment'] == "J") $updateSQL2 = "UPDATE brewer SET brewerNickname='judge' WHERE id='".$id."'"; 
  elseif ($_POST['brewerAssignment'] == "S") $updateSQL2 = "UPDATE brewer SET brewerNickname='steward' WHERE id='".$id."'"; 
  else $updateSQL2 = "UPDATE brewer SET brewerNickname=NULL WHERE id='".$id."'"; 

 //echo $updateSQL."<br>";
  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  $Result2 = mysql_query($updateSQL2, $brewing) or die(mysql_error());
  
  if ($go == "register") $updateGoTo = "../index.php?section=brew&msg=2";	
  elseif ($go == "judge") $updateGoTo = "../index.php?section=list&go=".$go."&filter=default&msg=7";
  elseif ($go == "default") $updateGoTo = "../index.php?section=list&go=".$go."&filter=default&msg=2";
  else $updateGoTo = $updateGoTo;

header(sprintf("Location: %s", $updateGoTo));
}

// --------------------------- SETUP: Adding General Contest Info ------------------------------- // 

if (($action == "add") && ($dbTable == "contest_info")) {

if (($_POST['contestEntryFee2'] == "") || ($_POST['contestEntryFeeDiscountNum'] == "")) $contestEntryFeeDiscount = "N"; 
if (($_POST['contestEntryFee2'] != "") && ($_POST['contestEntryFeeDiscountNum'] != "")) $contestEntryFeeDiscount = "Y"; 

$insertSQL = sprintf("INSERT INTO contest_info (
contestName,
contestID,
contestHost, 
contestHostWebsite, 
contestHostLocation,
contestRegistrationOpen,
contestRegistrationDeadline, 
contestEntryOpen,
contestEntryDeadline, 
contestRules, 
contestAwardsLocation, 
contestContactName, 
contestContactEmail, 

contestEntryFee, 
contestBottles, 
contestShippingAddress, 
contestShippingName, 
contestAwards,

contestWinnersComplete,
contestEntryCap,
contestAwardsLocName,
contestAwardsLocDate,
contestAwardsLocTime,

contestEntryFee2,
contestEntryFeeDiscount,
contestEntryFeeDiscountNum,
contestLogo,
contestBOSAward,
contestEntryFeePassword,
contestEntryFeePasswordNum,
contestCircuit,
id
) 
VALUES 
(
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s,
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s,
%s, %s, %s, %s, %s,
%s)",
                       GetSQLValueString(capitalize($_POST['contestName']), "text"),
					   GetSQLValueString($_POST['contestID'], "text"),
                       GetSQLValueString(capitalize($_POST['contestHost']), "text"),
                       GetSQLValueString($_POST['contestHostWebsite'], "text"),
                       GetSQLValueString(capitalize($_POST['contestHostLocation']), "text"),
                       GetSQLValueString($_POST['contestRegistrationOpen'], "date"),
					   GetSQLValueString($_POST['contestRegistrationDeadline'], "date"),
					   GetSQLValueString($_POST['contestEntryOpen'], "date"),
					   GetSQLValueString($_POST['contestEntryDeadline'], "date"),
                       GetSQLValueString($_POST['contestRules'], "text"),
                       GetSQLValueString(capitalize($_POST['contestAwardsLocation']), "text"),
                       GetSQLValueString($_POST['contestContactName'], "text"),
                       GetSQLValueString($_POST['contestContactEmail'], "text"),
                       GetSQLValueString($_POST['contestEntryFee'], "text"),
                       GetSQLValueString($_POST['contestBottles'], "text"),
                       GetSQLValueString($_POST['contestShippingAddress'], "text"),
                       GetSQLValueString(capitalize($_POST['contestShippingName']), "text"),
                       GetSQLValueString($_POST['contestAwards'], "text"),
					   GetSQLValueString($_POST['contestWinnersComplete'], "text"),
					   GetSQLValueString($_POST['contestEntryCap'], "text"),
					   GetSQLValueString(capitalize($_POST['contestAwardsLocName']), "text"),
					   GetSQLValueString($_POST['contestAwardsLocDate'], "text"),
					   GetSQLValueString($_POST['contestAwardsLocTime'], "text"),
					   GetSQLValueString($_POST['contestEntryFee2'], "text"),
					   GetSQLValueString($contestEntryFeeDiscount, "text"),
					   GetSQLValueString($_POST['contestEntryFeeDiscountNum'], "text"),
					   GetSQLValueString($_POST['contestLogo'], "text"),
					   GetSQLValueString($_POST['contestBOSAward'], "text"),
					   GetSQLValueString($_POST['contestEntryFeePassword'], "text"),
					   GetSQLValueString($_POST['contestEntryFeePasswordNum'], "text"),
					   GetSQLValueString($_POST['contestCircuit'], "text"),
                       GetSQLValueString($id, "int"));

  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
  
  $insertSQL = sprintf("INSERT INTO contacts (
	contactFirstName, 
	contactLastName, 
	contactPosition, 
	contactEmail
	) 
	VALUES 
	(%s, %s, %s, %s)",
                       GetSQLValueString($_POST['contactFirstName'], "text"),
                       GetSQLValueString($_POST['contactLastName'], "text"),
                       GetSQLValueString($_POST['contactPosition'], "text"),
					   GetSQLValueString($_POST['contactEmail'], "text"));
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
  	$insertGoTo = "../setup.php?section=step5";
	header(sprintf("Location: %s", $insertGoTo));

}

// --------------------------- If Editing General Contest Info ------------------------------- // 

if (($action == "edit") && ($dbTable == "contest_info")) {
	
if (($_POST['contestEntryFee2'] == "") || ($_POST['contestEntryFeeDiscountNum'] == "")) $contestEntryFeeDiscount = "N"; 
if (($_POST['contestEntryFee2'] != "") && ($_POST['contestEntryFeeDiscountNum'] != "")) $contestEntryFeeDiscount = "Y"; 

$updateSQL = sprintf("UPDATE contest_info SET 
contestName=%s,
contestID=%s,
contestHost=%s, 
contestHostWebsite=%s, 
contestHostLocation=%s,
contestRegistrationOpen=%s, 
contestRegistrationDeadline=%s, 
contestEntryOpen=%s,
contestEntryDeadline=%s, 
contestRules=%s, 
contestAwardsLocation=%s, 
contestContactName=%s, 
contestContactEmail=%s, 

contestEntryFee=%s, 
contestBottles=%s, 
contestShippingAddress=%s, 
contestShippingName=%s, 

contestAwards=%s,
contestWinnersComplete=%s,
contestEntryCap=%s,
contestAwardsLocName=%s,
contestAwardsLocDate=%s,

contestAwardsLocTime=%s,
contestEntryFee2=%s,
contestEntryFeeDiscount=%s,
contestEntryFeeDiscountNum=%s,
contestLogo=%s,
contestBOSAward=%s,
contestEntryFeePassword=%s,
contestEntryFeePasswordNum=%s,
contestCircuit=%s
WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['contestName']), "text"),
					   GetSQLValueString($_POST['contestID'], "text"),
                       GetSQLValueString(capitalize($_POST['contestHost']), "text"),
                       GetSQLValueString($_POST['contestHostWebsite'], "text"),
                       GetSQLValueString(capitalize($_POST['contestHostLocation']), "text"),
                       GetSQLValueString($_POST['contestRegistrationOpen'], "date"),
					   GetSQLValueString($_POST['contestRegistrationDeadline'], "date"),
					   GetSQLValueString($_POST['contestEntryOpen'], "date"),
					   GetSQLValueString($_POST['contestEntryDeadline'], "date"),
                       GetSQLValueString($_POST['contestRules'], "text"),
                       GetSQLValueString(capitalize($_POST['contestAwardsLocation']), "text"),
                       GetSQLValueString($_POST['contestContactName'], "text"),
                       GetSQLValueString($_POST['contestContactEmail'], "text"),
                       GetSQLValueString($_POST['contestEntryFee'], "text"),
                       GetSQLValueString($_POST['contestBottles'], "text"),
                       GetSQLValueString($_POST['contestShippingAddress'], "text"),
                       GetSQLValueString(capitalize($_POST['contestShippingName']), "text"),
                       GetSQLValueString($_POST['contestAwards'], "text"),
					   GetSQLValueString($_POST['contestWinnersComplete'], "text"),
					   GetSQLValueString($_POST['contestEntryCap'], "text"),
					   GetSQLValueString(capitalize($_POST['contestAwardsLocName']), "text"),
					   GetSQLValueString($_POST['contestAwardsLocDate'], "text"),
					   GetSQLValueString($_POST['contestAwardsLocTime'], "text"),
					   GetSQLValueString($_POST['contestEntryFee2'], "text"),
					   GetSQLValueString($contestEntryFeeDiscount, "text"),
					   GetSQLValueString($_POST['contestEntryFeeDiscountNum'], "text"),
					   GetSQLValueString($_POST['contestLogo'], "text"),
					   GetSQLValueString($_POST['contestBOSAward'], "text"),
					   GetSQLValueString($_POST['contestEntryFeePassword'], "text"),
					   GetSQLValueString($_POST['contestEntryFeePasswordNum'], "text"),
					   GetSQLValueString($_POST['contestCircuit'], "text"),
                       GetSQLValueString($id, "int"));

  mysql_select_db($database, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  header(sprintf("Location: %s", $updateGoTo));

}

// --------------------------- SETUP: Adding Preferences ------------------------------- //

if (($action == "add") && ($dbTable == "preferences")) {

$insertSQL = sprintf("INSERT INTO preferences (
prefsTemp, 
prefsWeight1, 
prefsWeight2, 
prefsLiquid1, 
prefsLiquid2,

prefsPaypal, 
prefsPaypalAccount, 
prefsCurrency, 
prefsCash, 
prefsCheck,

prefsCheckPayee, 
prefsTransFee,
prefsSponsors,
prefsSponsorLogos,
prefsSponsorLogoSize,

prefsCompLogoSize,
prefsDisplayWinners,
prefsDisplaySpecial,
prefsCompOrg,
prefsEntryForm,

prefsRecordLimit,
prefsRecordPaging,
prefsTheme,
prefsDateFormat,
prefsContact,
id) VALUES (
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s, 
%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['prefsTemp'], "text"),
					   GetSQLValueString($_POST['prefsWeight1'], "text"),
                       GetSQLValueString($_POST['prefsWeight2'], "text"),
                       GetSQLValueString($_POST['prefsLiquid1'], "text"),
                       GetSQLValueString($_POST['prefsLiquid2'], "text"),
					   
                       GetSQLValueString($_POST['prefsPaypal'], "text"),
                       GetSQLValueString($_POST['prefsPaypalAccount'], "text"),
					   GetSQLValueString($_POST['prefsCurrency'], "text"),
                       GetSQLValueString($_POST['prefsCash'], "text"),
					   GetSQLValueString($_POST['prefsCheck'], "text"),
					   
					   GetSQLValueString($_POST['prefsCheckPayee'], "text"),
					   GetSQLValueString($_POST['prefsTransFee'], "text"),
					   GetSQLValueString($_POST['prefsSponsors'], "text"),
					   GetSQLValueString($_POST['prefsSponsorLogos'], "text"),
					   GetSQLValueString($_POST['prefsSponsorLogoSize'], "int"),
					   
					   GetSQLValueString($_POST['prefsCompLogoSize'], "int"),
					   GetSQLValueString($_POST['prefsDisplayWinners'], "text"),
					   GetSQLValueString($_POST['prefsDisplaySpecial'], "text"),
					   GetSQLValueString($_POST['prefsCompOrg'], "text"),
					   GetSQLValueString($_POST['prefsEntryForm'], "text"),
					   
					   GetSQLValueString($_POST['prefsRecordLimit'], "int"),
					   GetSQLValueString($_POST['prefsRecordPaging'], "int"),
					   GetSQLValueString($_POST['prefsTheme'], "text"),
					   GetSQLValueString($_POST['prefsDateFormat'], "text"),
					   GetSQLValueString($_POST['prefsContact'], "text"),
                       GetSQLValueString($id, "int"));
					   
	//echo $insertSQL;
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());

  	$insertGoTo = "../setup.php?section=step4";
  	header(sprintf("Location: %s", $insertGoTo));
}


// --------------------------- If Editing Preferences ------------------------------- //

if (($action == "edit") && ($dbTable == "preferences")) {

$updateSQL = sprintf("UPDATE preferences SET 
prefsTemp=%s, 
prefsWeight1=%s, 
prefsWeight2=%s, 
prefsLiquid1=%s, 
prefsLiquid2=%s, 
prefsPaypal=%s, 
prefsPaypalAccount=%s, 
prefsCurrency=%s, 
prefsCash=%s, 
prefsCheck=%s, 
prefsCheckPayee=%s, 
prefsTransFee=%s, 
prefsSponsors=%s, 
prefsSponsorLogos=%s, 
prefsSponsorLogoSize=%s, 
prefsCompLogoSize=%s, 
prefsDisplayWinners=%s, 
prefsDisplaySpecial=%s, 
prefsCompOrg=%s, 
prefsEntryForm=%s,
prefsRecordLimit=%s,
prefsRecordPaging=%s,
prefsTheme=%s,
prefsDateFormat=%s,
prefsContact=%s
WHERE id=%s",
                       GetSQLValueString($_POST['prefsTemp'], "text"),
					   GetSQLValueString($_POST['prefsWeight1'], "text"),
                       GetSQLValueString($_POST['prefsWeight2'], "text"),
                       GetSQLValueString($_POST['prefsLiquid1'], "text"),
                       GetSQLValueString($_POST['prefsLiquid2'], "text"),
                       GetSQLValueString($_POST['prefsPaypal'], "text"),
                       GetSQLValueString($_POST['prefsPaypalAccount'], "text"),
                       GetSQLValueString($_POST['prefsCurrency'], "text"),
					   GetSQLValueString($_POST['prefsCash'], "text"),
					   GetSQLValueString($_POST['prefsCheck'], "text"),
					   GetSQLValueString($_POST['prefsCheckPayee'], "text"),
					   GetSQLValueString($_POST['prefsTransFee'], "text"),
					   GetSQLValueString($_POST['prefsSponsors'], "text"),
					   GetSQLValueString($_POST['prefsSponsorLogos'], "text"),
					   GetSQLValueString($_POST['prefsSponsorLogoSize'], "int"),
					   GetSQLValueString($_POST['prefsCompLogoSize'], "int"),
					   GetSQLValueString($_POST['prefsDisplayWinners'], "text"),
					   GetSQLValueString($_POST['prefsDisplaySpecial'], "text"),
					   GetSQLValueString($_POST['prefsCompOrg'], "text"),
					   GetSQLValueString($_POST['prefsEntryForm'], "text"),
					   GetSQLValueString($_POST['prefsRecordLimit'], "int"),
					   GetSQLValueString($_POST['prefsRecordPaging'], "int"),
					   GetSQLValueString($_POST['prefsTheme'], "text"),
					   GetSQLValueString($_POST['prefsDateFormat'], "text"),
					   GetSQLValueString($_POST['prefsContact'], "text"),
                       GetSQLValueString($id, "int"));
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $updateGoTo));
}

// --------------------------- If updating records in the brewing table en masse ------------------------------- //

if (($action == "update") && ($dbTable == "brewing")) {

foreach($_POST['id'] as $id)

	{ 
		$updateSQL = "UPDATE brewing SET 
		brewPaid='".$_POST["brewPaid".$id]."',
		brewWinner='".$_POST["brewWinner".$id]."',
		brewWinnerCat='".$_POST["brewWinnerCat".$id]."', 
		brewWinnerSubCat='".$_POST["brewWinnerSubCat".$id]."', 
		brewWinnerPlace='".$_POST["brewWinnerPlace".$id]."',
		brewBOSRound='".$_POST["brewBOSRound".$id]."',
		brewBOSPlace='".$_POST["brewBOSPlace".$id]."',
		brewReceived='".$_POST["brewReceived".$id]."'
		WHERE id='".$id.";'"; 
		mysql_select_db($database, $brewing);
		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());	
		//echo $updateSQL
	} 

if($result1){ 
	header(sprintf("Location: %s", $massUpdateGoTo)); 
	}
}

// --------------------------- If updating records in the brewer table en masse ------------------------------- //

if (($action == "update") && ($dbTable == "brewer") && ($row_prefs['prefsCompOrg'] == "N")) {

	foreach($_POST['id'] as $id){ 
		mysql_select_db($database, $brewing);		
		if (($bid == "default") && ($_POST["brewerAssignment".$id] != "") && ($filter != "bos")) {
		$updateSQL = "UPDATE brewer SET brewerAssignment='";
		$updateSQL .= $_POST["brewerAssignment".$id];
		$updateSQL .= "' WHERE id='".$id.";'";
		}
		elseif (($bid == "default") && ($_POST["brewerAssignment".$id] != "") && ($filter == "bos")) {
		$updateSQL = "UPDATE brewer SET brewerJudgeBOS='";
		$updateSQL .= $_POST["brewerAssignment".$id];
		$updateSQL .= "' WHERE id='".$id.";'";
		}
		else $updateSQL = "SELECT id FROM brewer WHERE id='$id'";
		
		if ($bid != "default") { 
			$query_update = "SELECT brewerStewardAssignedLocation, brewerJudgeAssignedLocation FROM brewer WHERE id='$id'";
			$update = mysql_query($query_update, $brewing) or die(mysql_error());
			$row_update = mysql_fetch_assoc($update);
		
			if ($row_update['brewerStewardAssignedLocation'] != "") { 
			$trimmed = rtrim($row_update['brewerStewardAssignedLocation'], ",");
					if  (!strstr($trimmed, $bid)) $sal = $trimmed.", "; 
					elseif (strstr($trimmed, $bid)) { 
						$sal = str_replace($bid,"",$trimmed).", "; 
						}
					else $sal = ""; 
					}
			if ($row_update['brewerJudgeAssignedLocation'] != "") { 
			$trimmed = rtrim($row_update['brewerJudgeAssignedLocation'], ",");
					if  (!strstr($trimmed, $bid)) $jal = $trimmed.", "; 
					elseif (strstr($trimmed, $bid)) { 
						$jal = str_replace($bid,"",$trimmed).", "; 
						}
					else $jal = ""; 
					}
																																				  
			if ($filter == "stewards") {  
			$sal = str_replace(" ,","",$sal); // clean up
			if (substr($sal, 0, 2) == ", ") $sal = substr_replace($sal, "", 0, 2); else $sal = $sal;
			$updateSQL = "UPDATE brewer SET brewerStewardAssignedLocation='";
			$updateSQL .= $sal.$_POST["brewerStewardAssignedLocation".$id];
			$updateSQL .= "' WHERE id='".$id."';"; 
			}
			elseif ($filter == "judges") {
			$jal = str_replace(" ,","",$jal); // clean up
			if (substr($jal, 0, 2) == ", ") $jal = substr_replace($jal, "", 0, 2); else $jal = $jal;
			$updateSQL = "UPDATE brewer SET brewerJudgeAssignedLocation='";
			$updateSQL .= $jal.$_POST["brewerJudgeAssignedLocation".$id];
			$updateSQL .= "' WHERE id='".$id."';";
			}
			else $updateSQL = "SELECT id FROM brewer WHERE id='$id'";
		}
		
		if ($_POST["brewerAssignment".$id] == "J") $updateSQL2 = "UPDATE brewer SET brewerNickname='judge' WHERE id='".$id."'"; 
  		elseif ($_POST["brewerAssignment".$id] == "S") $updateSQL2 = "UPDATE brewer SET brewerNickname='steward' WHERE id='".$id."'"; 
  		else $updateSQL2 = "SELECT id FROM brewer WHERE id='$id'";
		
		if ($filter == "stewards") $field = "brewerStewardAssignedLocation";
		elseif ($filter == "judges") $field = "brewerJudgeAssignedLocation";
		else $field = "brewerJudgeAssignedLocation";
		
		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		$result2 = mysql_query($updateSQL2, $brewing) or die(mysql_error());
		
		if (($filter == "judges") || ($filter == "stewards")) {
			$query_clean = "SELECT $field FROM brewer WHERE id = '$id'";
			$clean = mysql_query($query_clean, $brewing) or die(mysql_error());
			$row_clean = mysql_fetch_assoc($clean);
		
			if ($filter == "stewards") { 
				if (substr($row_clean['brewerStewardAssignedLocation'], 0, 2) == ", ")  $cleaned = substr_replace($row_clean['brewerStewardAssignedLocation'], "", 0, 2); else $cleaned = $row_clean['brewerStewardAssignedLocation']; 
				} 
			if ($filter == "judges") { 
				if  (substr($row_clean['brewerJudgeAssignedLocation'], 0, 2) == ", ") $cleaned = substr_replace($row_clean['brewerJudgeAssignedLocation'], "", 0, 2); else $cleaned = $row_clean['brewerJudgeAssignedLocation']; 
			}
			$cleaned = rtrim($cleaned, " ");
			$cleaned = rtrim($cleaned, ",");
			$updateSQL3 = "UPDATE brewer SET ";
			$updateSQL3 .= $field."=";
			$updateSQL3 .= "'".$cleaned."' ";
			$updateSQL3 .= " WHERE id='".$id."';";
		$result3 = mysql_query($updateSQL3, $brewing) or die(mysql_error());	
		}
		// Debug
		//echo "<p>".$updateSQL."<br>";
		//echo $updateSQL2."<br>";
		//echo $updateSQL3."</p>";
		if ($filter == "staff") {
		$updateSQL = sprintf("UPDATE brewer SET brewerAssignment='O' WHERE uid='%s'", $_POST['Organizer']);
		$result = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
	} 
if($result1){ header(sprintf("Location: %s", $massUpdateGoTo));  }
}



// --------------------------- If updating records in the brewer table en masse ------------------------------- //

if (($action == "update") && ($dbTable == "brewer") && ($row_prefs['prefsCompOrg'] == "Y")) {

		

	foreach($_POST['id'] as $id){ 
		$query_assignment = "SELECT id,brewerAssignment FROM brewer WHERE id='".$id."'";
		$assignment = mysql_query($query_assignment, $brewing) or die(mysql_error());
		$row_assignment = mysql_fetch_assoc($assignment);
		
		if (($_POST["brewerAssignment".$id] != "") && ($filter != "bos")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		elseif (($_POST["brewerAssignment".$id] != "") && ($filter == "bos")) {
			$updateSQL = "UPDATE brewer SET brewerJudgeBOS='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		
		elseif (($_POST["brewerAssignment".$id] == "") && ($row_assignment['brewerAssignment'] == "")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		
		elseif (($_POST["brewerAssignment".$id] == "") && ($filter == "judges") && ($row_assignment['brewerAssignment'] == "J")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		
		elseif (($_POST["brewerAssignment".$id] == "") && ($filter == "stewards") && ($row_assignment['brewerAssignment'] == "S")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		
		elseif (($_POST["brewerAssignment".$id] == "") && ($filter == "staff") && ($row_assignment['brewerAssignment'] == "X")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='";
			$updateSQL .= $_POST["brewerAssignment".$id];
			$updateSQL .= "' WHERE id='".$id."';";
		}
		
		elseif (($_POST["brewerAssignment".$id] == "") && ($row_assignment['brewerAssignment'] == "O")) {
			$updateSQL = "UPDATE brewer SET brewerAssignment='O' WHERE id='".$id."';";
		}
		
		else $updateSQL = "SELECT id from brewer WHERE id='".$id."';";
		$result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	} 
	
	if (($_POST['Organizer'] != "") && ($filter == "staff")) {
		$query_org = "SELECT uid,brewerAssignment FROM brewer WHERE brewerAssignment='O'";
		$org = mysql_query($query_org, $brewing) or die(mysql_error());
		$row_org = mysql_fetch_assoc($org);
		
		if ($_POST['Organizer'] != $row_org['uid']) {
			
			$updateSQL = sprintf("UPDATE brewer SET brewerAssignment='' WHERE uid='%s'", $row_org['uid']);
			$result = mysql_query($updateSQL, $brewing) or die(mysql_error());
			
			$updateSQL = sprintf("UPDATE brewer SET brewerAssignment='O' WHERE uid='%s'", $_POST['Organizer']);
			$result = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
		
		if ($_POST['Organizer'] == $row_assignment['uid']) {
			$updateSQL = sprintf("UPDATE brewer SET brewerAssignment='O' WHERE uid='%s'", $_POST['Organizer']);
			$result = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
	}
	
if($result1){ header(sprintf("Location: %s", $massUpdateGoTo));  }
}

// --------------------------- If updating records in the styles table en masse ------------------------------- //

if (($action == "update") && ($dbTable == "styles")) {

foreach($_POST['id'] as $id)	{ 

		if ($filter == "default") {
		 $updateSQL = "UPDATE styles SET brewStyleActive='".$_POST["brewStyleActive".$id]."' WHERE id='".$id."'";
		 mysql_select_db($database, $brewing);
		 $result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());	
		 }
		 
		if (($filter == "judging") && ($bid == $_POST["brewStyleJudgingLoc".$id])) { 
		 $updateSQL = "UPDATE styles SET brewStyleJudgingLoc='".$_POST["brewStyleJudgingLoc".$id]."' WHERE id='".$id."';";
		 $result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		 //echo $updateSQL."<br>"; 
		 
		 // Also need to find all records in the "brewing" table (entries) that are null or have either old judging location associated with the style and update them with the new judging location.		 
		 mysql_select_db($database, $brewing);
		 $query_style_name = "SELECT *FROM styles WHERE id='".$id."'";
		 $style_name = mysql_query($query_style_name, $brewing) or die(mysql_error());
		 $row_style_name = mysql_fetch_assoc($style_name);
		 
		 $query_loc = sprintf("SELECT * FROM brewing WHERE brewCategorySort='%s' AND brewSubCategory='%s'", $row_style_name['brewStyleGroup'], $row_style_name['brewStyleNum']);
		 $loc = mysql_query($query_loc, $brewing) or die(mysql_error());
		 $row_loc = mysql_fetch_assoc($loc);
		 $totalRows_loc = mysql_num_rows($loc);
		 //echo $query_loc."<br>";
		 	if ($totalRows_loc > 0) {
		 		do { 
				if ($row_loc['brewJudgingLocation'] != $_POST["brewStyleJudgingLoc".$id]) {
				$updateSQL2 = sprintf("UPDATE brewing SET brewJudgingLocation='%s' WHERE id='%s';", $_POST["brewStyleJudgingLoc".$id], $row_loc['id']); 
				$result2 = mysql_query($updateSQL2, $brewing) or die(mysql_error());
				//echo $updateSQL2."<br>"; 
				}
				}
				 
				while($row_loc = mysql_fetch_assoc($loc));
		 	}
		 }
		 
		 
		
	}
		 
if($result1){ 
	if (($section == "step7") && ($row_prefs['prefsCompOrg'] == "N")) header("location:../index.php?msg=success");
	elseif (($section == "step7") && ($row_prefs['prefsCompOrg'] == "Y")) header("location:../setup.php?section=step8");
	else header(sprintf("Location: %s", $massUpdateGoTo));
	}

}


// --------------------------- If Editing Sponsors ------------------------------- //

if (($action == "edit") && ($dbTable == "sponsors")) {

  $updateSQL = sprintf("UPDATE sponsors SET sponsorName=%s, sponsorURL=%s, sponsorImage=%s, sponsorText=%s, sponsorLocation=%s , sponsorLevel=%s WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['sponsorName']), "text"),
                       GetSQLValueString($_POST['sponsorURL'], "text"),
                       GetSQLValueString($_POST['sponsorImage'], "text"),
                       GetSQLValueString($_POST['sponsorText'], "text"),
					   GetSQLValueString($_POST['sponsorLocation'], "text"),
					   GetSQLValueString($_POST['sponsorLevel'], "int"),
					   GetSQLValueString($id, "int"));

	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $updateGoTo));					   
}

// --------------------------- If Add Sponsors ------------------------------- //

if (($action == "add") && ($dbTable == "sponsors")) {

  $insertSQL = sprintf("INSERT INTO sponsors (sponsorName, sponsorURL, sponsorImage, sponsorText, sponsorLocation, sponsorLevel) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['sponsorName']), "text"),
                       GetSQLValueString($_POST['sponsorURL'], "text"),
                       GetSQLValueString($_POST['sponsorImage'], "text"),
                       GetSQLValueString($_POST['sponsorText'], "text"),
					   GetSQLValueString($_POST['sponsorLocation'], "text"),
					   GetSQLValueString($_POST['sponsorLevel'], "int")
					   );

	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $insertGoTo));					   
}

// --------------------------- If Adding Judging Locations ------------------------------- //

if (($action == "add") && ($dbTable == "judging_locations")) {

  $insertSQL = sprintf("INSERT INTO judging_locations (judgingDate, judgingTime, judgingLocation, judgingLocName, judgingRounds) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['judgingDate'], "text"),
                       GetSQLValueString($_POST['judgingTime'], "text"),
                       GetSQLValueString(capitalize($_POST['judgingLocation']), "text"),
                       GetSQLValueString(capitalize($_POST['judgingLocName']), "text"),
					   GetSQLValueString($_POST['judgingRounds'], "text")
					   );

	//echo $insertSQL;
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	if ($section == "step5") $insertGoTo = "../setup.php?section=step5&msg=9"; else $insertGoTo = $insertGoTo;
	header(sprintf("Location: %s", $insertGoTo));					   
}

// --------------------------- If Editing Judging Locations ------------------------------- //

if (($action == "edit") && ($dbTable == "judging_locations")) {

  $updateSQL = sprintf("UPDATE judging_locations SET judgingDate=%s, judgingTime=%s, judgingLocation=%s, judgingLocName=%s, judgingRounds=%s WHERE id=%s",
                       GetSQLValueString($_POST['judgingDate'], "text"),
                       GetSQLValueString($_POST['judgingTime'], "text"),
                       GetSQLValueString(capitalize($_POST['judgingLocation']), "text"),
                       GetSQLValueString(capitalize($_POST['judgingLocName']), "text"),
					   GetSQLValueString($_POST['judgingRounds'], "text"),
					   GetSQLValueString($id, "int"));   
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $updateGoTo));					   
}


// --------------------------- If Adding Drop-off Locations ------------------------------- //

if (($action == "add") && ($dbTable == "drop_off")) {

  $insertSQL = sprintf("INSERT INTO drop_off (dropLocationName, dropLocation, dropLocationPhone, dropLocationWebsite, dropLocationNotes) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['dropLocationName']), "text"),
                       GetSQLValueString($_POST['dropLocation'], "text"),
                       GetSQLValueString($_POST['dropLocationPhone'], "text"),
					   GetSQLValueString($_POST['dropLocationWebsite'], "text"),
					   GetSQLValueString($_POST['dropLocationNotes'], "text")
					   );

	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	if ($section == "step6") $insertGoTo = "../setup.php?section=$section&msg=11"; else $insertGoTo = $insertGoTo;
	header(sprintf("Location: %s", $insertGoTo));					   
}

// --------------------------- If Editing Drop-Off Locations ------------------------------- //

if (($action == "edit") && ($dbTable == "drop_off")) {

  $updateSQL = sprintf("UPDATE drop_off SET dropLocationName=%s, dropLocation=%s, dropLocationPhone=%s, dropLocationWebsite=%s, dropLocationNotes=%s WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['dropLocationName']), "text"),
                       GetSQLValueString($_POST['dropLocation'], "text"),
                       GetSQLValueString($_POST['dropLocationPhone'], "text"),
					   GetSQLValueString($_POST['dropLocationWebsite'], "text"),
					   GetSQLValueString($_POST['dropLocationNotes'], "text"),
					   GetSQLValueString($id, "int"));   
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $updateGoTo));					   
}


// --------------------------- If Adding a Style --------------------------- //

if (($action == "add") && ($dbTable == "styles")) {
mysql_select_db($database, $brewing);
$query_style_name = "SELECT brewStyleGroup FROM `styles` ORDER BY id DESC LIMIT 1";
$style_name = mysql_query($query_style_name, $brewing) or die(mysql_error());
$row_style_name = mysql_fetch_assoc($style_name);
$style_add_one = $row_style_name['brewStyleGroup'] + 1;

  $insertSQL = sprintf("INSERT INTO styles (
  brewStyleNum, 
  brewStyle, 
  brewStyleOG, 
  brewStyleOGMax, 
  brewStyleFG, 
  
  brewStyleFGMax, 
  brewStyleABV, 
  brewStyleABVMax, 
  brewStyleIBU, 
  brewStyleIBUMax, 
  
  brewStyleSRM, 
  brewStyleSRMMax, 
  brewStyleType, 
  brewStyleInfo, 
  brewStyleLink, 
  
  brewStyleGroup, 
  brewStyleActive, 
  brewStyleOwn
  ) 
  VALUES (
  %s, %s, %s, %s, %s, 
  %s, %s, %s, %s, %s, 
  %s, %s, %s, %s, %s, 
  %s, %s, %s)",
                       GetSQLValueString("A", "text"),
                       GetSQLValueString(captialize($_POST['brewStyle']), "scrubbed"),
                       GetSQLValueString($_POST['brewStyleOG'], "text"),
                       GetSQLValueString($_POST['brewStyleOGMax'], "text"),
                       GetSQLValueString($_POST['brewStyleFG'], "text"),
                       GetSQLValueString($_POST['brewStyleFGMax'], "text"),
                       GetSQLValueString($_POST['brewStyleABV'], "text"),
                       GetSQLValueString($_POST['brewStyleABVMax'], "text"),
                       GetSQLValueString($_POST['brewStyleIBU'], "text"),
                       GetSQLValueString($_POST['brewStyleIBUMax'], "text"),
                       GetSQLValueString($_POST['brewStyleSRM'], "text"),
                       GetSQLValueString($_POST['brewStyleSRMMax'], "text"),
                       GetSQLValueString($_POST['brewStyleType'], "text"),
                       GetSQLValueString($_POST['brewStyleInfo'], "text"),
                       GetSQLValueString($_POST['brewStyleLink'], "text"),
                       GetSQLValueString($style_add_one, "text"),
					   GetSQLValueString($_POST['brewStyleActive'], "text"),
					   GetSQLValueString($_POST['brewStyleOwn'], "text")
					   );


  mysql_select_db($database_brewing, $brewing);
  $Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
  header(sprintf("Location: %s", $insertGoTo));

}


// --------------------------- If Editing a Style --------------------------- //

if (($action == "edit") && ($dbTable == "styles")) {
  $updateSQL = sprintf("UPDATE styles SET 
  brewStyleNum=%s, 
  brewStyle=%s, 
  brewStyleOG=%s, 
  brewStyleOGMax=%s, 
  brewStyleFG=%s, 
  
  brewStyleFGMax=%s, 
  brewStyleABV=%s, 
  brewStyleABVMax=%s, 
  brewStyleIBU=%s, 
  brewStyleIBUMax=%s, 
  
  brewStyleSRM=%s, 
  brewStyleSRMMax=%s, 
  brewStyleType=%s, 
  brewStyleInfo=%s, 
  brewStyleLink=%s, 
  
  brewStyleGroup=%s,
  brewStyleActive=%s, 
  brewStyleOwn=%s
  
  WHERE id=%s",
                       GetSQLValueString($_POST['brewStyleNum'], "text"),
                       GetSQLValueString(capitalize($_POST['brewStyle']), "scrubbed"),
                       GetSQLValueString($_POST['brewStyleOG'], "text"),
                       GetSQLValueString($_POST['brewStyleOGMax'], "text"),
                       GetSQLValueString($_POST['brewStyleFG'], "text"),
                       GetSQLValueString($_POST['brewStyleFGMax'], "text"),
                       GetSQLValueString($_POST['brewStyleABV'], "text"),
                       GetSQLValueString($_POST['brewStyleABVMax'], "text"),
                       GetSQLValueString($_POST['brewStyleIBU'], "text"),
                       GetSQLValueString($_POST['brewStyleIBUMax'], "text"),
                       GetSQLValueString($_POST['brewStyleSRM'], "text"),
                       GetSQLValueString($_POST['brewStyleSRMMax'], "text"),
                       GetSQLValueString($_POST['brewStyleType'], "text"),
                       GetSQLValueString($_POST['brewStyleInfo'], "text"),
                       GetSQLValueString($_POST['brewStyleLink'], "text"),
                       GetSQLValueString($_POST['brewStyleGroup'], "text"),
					   GetSQLValueString($_POST['brewStyleActive'], "text"),
					   GetSQLValueString($_POST['brewStyleOwn'], "text"),
                       GetSQLValueString($id, "int"));

  mysql_select_db($database_brewing, $brewing);
  $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  
  	$query_log = sprintf("SELECT id FROM brewing WHERE brewStyle = '%s'",$_POST['brewStyleOld']); 
	$log = mysql_query($query_log, $brewing) or die(mysql_error());
	$row_log = mysql_fetch_assoc($log);
	$totalRows_log = mysql_num_rows($log);
	  
  do {
	 $updateSQL = sprintf("UPDATE brewing SET brewStyle='%s' WHERE id='%s'", $_POST['brewStyle'],$row_log['id']);
	 $Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  } while ($row_log = mysql_fetch_assoc($log));
  
  header(sprintf("Location: %s", $updateGoTo));
}

// --------------------------- If Adding a Contact (Non-setup) --------------------------- //

if (($action == "add") && ($dbTable == "contacts")) {

$insertSQL = sprintf("INSERT INTO contacts (
	contactFirstName, 
	contactLastName, 
	contactPosition, 
	contactEmail
	) 
	VALUES 
	(%s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['contactFirstName']), "text"),
                       GetSQLValueString(capitalize($_POST['contactLastName']), "text"),
                       GetSQLValueString(capitalize($_POST['contactPosition']), "text"),
					   GetSQLValueString($_POST['contactEmail'], "text"));
	//echo $insertSQL;				   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $insertGoTo));

}

// --------------------------- If Editing a Contact --------------------------- //

if (($action == "edit") && ($dbTable == "contacts")) {

$updateSQL = sprintf("UPDATE contacts SET 
	contactFirstName=%s, 
	contactLastName=%s, 
	contactPosition=%s, 
	contactEmail=%s
	WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['contactFirstName']), "text"),
                       GetSQLValueString(capitalize($_POST['contactLastName']), "text"),
                       GetSQLValueString(capitalize($_POST['contactPosition']), "text"),
					   GetSQLValueString($_POST['contactEmail'], "text"),
					   GetSQLValueString($id, "int"));
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  	header(sprintf("Location: %s", $updateGoTo));
}

if ($action == "email") { 
// CAPCHA check
include_once  (ROOT.'captcha/securimage.php');
$securimage = new Securimage();

	if ($securimage->check($_POST['captcha_code']) == false) {
		setcookie("to", $_POST['to'], 0, "/"); // $id of contact record in contacts table
		setcookie("from_email", $_POST['from_email'], 0, "/");
		setcookie("from_name", $_POST['from_name'], 0, "/");
		setcookie("subject", $_POST['subject'], 0, "/");
		setcookie("message", $_POST['message'], 0, "/");
		header("Location: ../index.php?section=".$section."&action=email&msg=2");
	}

	else {

		mysql_select_db($database, $brewing);
		$query_contact = sprintf("SELECT * FROM contacts WHERE id='%s'", $_POST['to']);
		$contact = mysql_query($query_contact, $brewing) or die(mysql_error());
		$row_contact = mysql_fetch_assoc($contact);
		
		// Gather the variables from the form
		$to_email = $row_contact['contactEmail'];
		$to_name = $row_contact['contactFirstName']." ".$row_contact['contactLastName'];
		$from_email = $_POST['from_email'];
		$from_name = $_POST['from_name'];
		$subject = $_POST['subject'];
		$message_post = $_POST['message'];
		
		// Build the message
		$message = "<html>" . "\r\n";
		//$message .= "<head>" . $subject."</head>" . "\r\n";
		$message .= "<body>" . $message_post. "\r\n". "</body>" . "\r\n";
		$message .= "</html>";
		
		$headers  = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "To: ".$to_name." <".$to_email.">, " . "\r\n";
		$headers .= "From: ".$from_name." <".$from_email.">" . "\r\n";
		$headers .= "CC: ".$from_name." <".$from_email.">" . "\r\n";
		
		// Debug
		//echo $to_email."<br>";
		//echo $to_name."<br>";
		//echo $headers."<br>";
		//echo $message;
		
		mail($to_email, $subject, $message, $headers);
		header("Location: ../index.php?section=".$section."&action=email&msg=1&id=".$row_contact['id']);
	}
}

// --------------------------- If Editing Judging Preferences ------------------------------- //

if ((($action == "edit") && ($dbTable == "judging_preferences")) || ($section == "step8")) {
if ($_POST['jPrefsQueued'] == "N") $flight_ent = $_POST['jPrefsFlightEntries']; else $flight_ent = $row_judging_prefs['jPrefsFlightEntries'];
$updateSQL = sprintf("UPDATE judging_preferences SET
					 
jPrefsQueued=%s,
jPrefsFlightEntries=%s,
jPrefsMaxBOS=%s,
jPrefsRounds=%s
WHERE id=%s",
                       GetSQLValueString($_POST['jPrefsQueued'], "text"),
					   GetSQLValueString($flight_ent, "int"),
                       GetSQLValueString($_POST['jPrefsMaxBOS'], "int"),
					   GetSQLValueString($_POST['jPrefsRounds'], "int"),
                       GetSQLValueString($id, "int"));
					   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	if ($section == "step8") header("location:../index.php?msg=success"); else header(sprintf("Location: %s", $updateGoTo));
}




// --------------------------- Adding a Table and Associated Styles ------------------------------- //

if (($action == "add") && ($dbTable == "judging_tables")) {

if ($_POST['tableStyles'] != "") $table_styles = implode(",",$_POST['tableStyles']); else $table_styles = $_POST['tableStyles'];

$insertSQL = sprintf("INSERT INTO judging_tables (
tableName, 
tableStyles, 
tableNumber,
tableLocation
  ) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['tableName']), "text"),
					   GetSQLValueString($table_styles, "text"),
					   GetSQLValueString($_POST['tableNumber'], "text"),
					   GetSQLValueString($_POST['tableLocation'], "text")
					   );

	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	
	$query_table = "SELECT id,tableLocation FROM judging_tables ORDER BY id DESC LIMIT 1";
	$table = mysql_query($query_table, $brewing) or die(mysql_error());
	$row_table = mysql_fetch_assoc($table);
	
	$query_table_rounds = sprintf("SELECT judgingRounds FROM judging_locations WHERE id='%s'", $row_table['tableLocation']);
	$table_rounds = mysql_query($query_table_rounds, $brewing) or die(mysql_error());
	$row_table_rounds = mysql_fetch_assoc($table_rounds);
	if ($row_table_rounds['judgingRounds'] == 1) $rounds = "1"; else $rounds = "";
	
	// Add all entries affected entries to Flight1
	
	$a = explode(",",$table_styles);
	
	foreach (array_unique($a) as $value) {
	
		$query_styles = sprintf("SELECT brewStyleGroup, brewStyleNum FROM styles WHERE id='%s'", $value);
		$styles = mysql_query($query_styles, $brewing) or die(mysql_error());
		$row_styles = mysql_fetch_assoc($styles);
		
		$query_entries = sprintf("SELECT id FROM brewing WHERE brewCategorySort='%s' AND brewSubCategory='%s' AND brewPaid='Y' AND brewReceived='Y'", $row_styles['brewStyleGroup'],$row_styles['brewStyleNum']);
		$entries = mysql_query($query_entries, $brewing) or die(mysql_error());
		$row_entries = mysql_fetch_assoc($entries);
		
		do {
			
			$insertSQL = sprintf("INSERT INTO judging_flights (
				flightTable, 
				flightNumber, 
				flightEntryID,
				flightRound
  				) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($row_table['id'], "text"),
					   GetSQLValueString("1", "text"),
					   GetSQLValueString($row_entries['id'], "text"),
					   GetSQLValueString($rounds, "text")
					   );

			//echo $insertSQL."<br>";
			mysql_select_db($database, $brewing);
  			$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
			
		} while ($row_entries = mysql_fetch_assoc($entries));
	}
	if ($_POST['tableStyles'] != "") $insertGoTo = $insertGoTo; else $insertGoTo = $insertGoTo = $_POST['relocate']."&msg=13";
	header(sprintf("Location: %s", $insertGoTo));
}

// --------------------------- Editing a Table and Associated Styles ------------------------------- //

if (($action == "edit") && ($dbTable == "judging_tables")) {
	
	if ($_POST['tableStyles'] != "") $table_styles = implode(",",$_POST['tableStyles']); else $table_styles = "";

	$updateSQL = sprintf("UPDATE judging_tables SET 
	tableName=%s, 
	tableStyles=%s, 
	tableNumber=%s,
	tableLocation=%s
	WHERE id=%s",
                    
	GetSQLValueString(capitalize($_POST['tableName']), "text"),
	GetSQLValueString($table_styles, "text"),
	GetSQLValueString($_POST['tableNumber'], "text"),
	GetSQLValueString($_POST['tableLocation'], "text"),
	GetSQLValueString($id, "text"));

  	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  
  	// Check to see if flights have been designated already
  	$query_flight_count = sprintf("SELECT id,flightEntryID FROM judging_flights WHERE flightTable='%s'", $id);
	$flight_count = mysql_query($query_flight_count, $brewing) or die(mysql_error());
	$row_flight_count = mysql_fetch_assoc($flight_count);
	$totalRows_flight_count = mysql_num_rows($flight_count);
	
	//echo "<p>".$totalRows_flight_count."<p>";
  	
	// If flights are designated and the Table's styles have changed, loop through the judging_flights and update or remove the affected entries
  	if (($totalRows_flight_count > 0) && ($table_styles != "")) {
		
		$query_flight_round = sprintf("SELECT flightRound FROM judging_flights WHERE flightTable='%s' ORDER BY flightRound DESC LIMIT 1", $id);
		$flight_round = mysql_query($query_flight_round, $brewing) or die(mysql_error());
		$row_flight_round = mysql_fetch_assoc($flight_round);
		
		$a = explode(",",$table_styles);
		
		$query_table_styles = "SELECT id,tableStyles FROM judging_tables";
		$table_styles = mysql_query($query_table_styles, $brewing) or die(mysql_error());
		$row_table_styles = mysql_fetch_assoc($table_styles);
					
		do { $t[] = $row_table_styles['id']; } while ($row_table_styles = mysql_fetch_assoc($table_styles));
	
	// Update or remove	
		do { $f[] = $row_flight_count['id']; } while ($row_flight_count = mysql_fetch_assoc($flight_count)); 
		
		foreach ($f as $id) {
			unset($update);
			unset($b);	
			
			$query_entry_style = sprintf("SELECT flightEntryID FROM judging_flights WHERE id='%s'", $id);
			$entry_style = mysql_query($query_entry_style, $brewing) or die(mysql_error());
			$row_entry_style = mysql_fetch_assoc($entry_style);
			//echo $query_entry_style."<br>";
			
			$query_entry = sprintf("SELECT brewCategorySort,brewSubCategory FROM brewing WHERE id='%s'", $row_entry_style['flightEntryID']);
			$entry = mysql_query($query_entry, $brewing) or die(mysql_error());
			$row_entry = mysql_fetch_assoc($entry);
			//echo $query_entry."<br>";
			
			foreach ($t as $table_id) {
				
				$query_table_style = sprintf("SELECT id,tableStyles FROM judging_tables WHERE id='%s'", $table_id);
				$table_style = mysql_query($query_table_style, $brewing) or die(mysql_error());
				$row_table_style = mysql_fetch_assoc($table_style);
				//echo $query_table_style."<br>";
				
				$query_style = sprintf("SELECT id FROM styles WHERE brewStyleGroup='%s' AND brewStyleNum='%s'", $row_entry['brewCategorySort'],$row_entry['brewSubCategory']);
				$style = mysql_query($query_style, $brewing) or die(mysql_error());
				$row_style = mysql_fetch_assoc($style);
				//echo $query_style."<br>";
				
				$array = explode(",",$row_table_style['tableStyles']);
				//print_r($array);
				//echo "<br>";
				if (in_array($row_style['id'],$array)) $update[] = $row_table_style['id']; else $update[] = "N";
			}
			
			$query_flight_info = sprintf("SELECT id,flightEntryID,flightTable FROM judging_flights WHERE id='%s'", $id);
			$flight_info = mysql_query($query_flight_info, $brewing) or die(mysql_error());
			$row_flight_info = mysql_fetch_assoc($flight_info);
			$totalRows_flight_info = mysql_num_rows($flight_info);
			
			$query_entry = sprintf("SELECT brewCategorySort,brewSubCategory FROM brewing WHERE id='%s'", $row_flight_info['flightEntryID']);
			$entry = mysql_query($query_entry, $brewing) or die(mysql_error());
			$row_entry = mysql_fetch_assoc($entry);
			
			$entry_style = $row_entry['brewCategorySort'].$row_entry['brewSubCategory'];
			
			//echo "<p>".$query_entry."<br>";
			
			foreach ($a as $style_id) {
				
				$b[] = 0;
				
				$query_style = sprintf("SELECT brewStyleGroup,brewStyleNum FROM styles WHERE id='%s'", $style_id);
				$style = mysql_query($query_style, $brewing) or die(mysql_error());
				$row_style = mysql_fetch_assoc($style);
			
				//echo $query_style."<br>";
				
				$table_style = $row_style['brewStyleGroup'].$row_style['brewStyleNum'];
				
				//echo "Table Style: ".$table_style."<br>";
				//echo "Entry Style: ".$entry_style."<br>";
				
				if ($table_style == $entry_style) $b[] = 1;
				if ($table_style != $entry_style) $b[] = 0;
			}
			
			$update = array_filter($update,"is_numeric");
			$update = implode("",$update);
			$delete = array_sum($b);
			
			//echo $update."<br>";
			//echo $delete."<br>";
			
			// Delete if no table found that contains the entry's style
			if (($delete == 0) && ($update == "")) {
				$delete = sprintf("DELETE FROM judging_flights WHERE id='%s'", $row_flight_info['id']);
				//echo $delete."<br>";
  				mysql_select_db($database, $brewing);
  				$Result = mysql_query($delete, $brewing) or die(mysql_error());	
			}
			
			// If table style is assigned to another table, reassign the entry to that table
			if (($delete == 0) && ($update != "")) {
				$updateSQL = sprintf("UPDATE judging_flights SET
					flightTable=%s, 
					flightNumber=%s, 
					flightRound=%s
					WHERE id=%s",
                       GetSQLValueString($update, "text"),
                       GetSQLValueString("1", "text"),
                       GetSQLValueString("1", "text"),
                       GetSQLValueString($id, "text"));
				//echo $updateSQL."<br>";
  				mysql_select_db($database_brewing, $brewing);
  				$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());	
			}
		}
	} //end if (($row_flight_count['count'] > 0) && ($table_styles != ""))
	
	// Remove all flight rows if unassigning all present table styles
	if (($totalRows_flight_count > 0) && ($table_styles == "")) {
		do { $a[] = $row_flight_count['id']; } while ($row_flight_count = mysql_fetch_assoc($flight_count));
		foreach ($a as $id) {
			$delete = sprintf("DELETE FROM judging_flights WHERE id='%s'", $id);
  			mysql_select_db($database, $brewing);
  			$Result = mysql_query($delete, $brewing) or die(mysql_error());
			}
	} // end if (($totalRows_flight_count > 0) && ($table_styles == ""))
  
  header(sprintf("Location: %s", $updateGoTo));
}

if (($action == "add") && ($dbTable == "judging_flights")) { 
/*
foreach($_POST['id'] as $id)	{
	$flight_number[] = ltrim($_POST['flightNumber'.$id],"flight");
	$entry_number[] = $_POST['flightEntryID'.$id]."-".ltrim($_POST['flightNumber'.$id],"flight");
}
//print_r(array_unique($flight_number));
//echo "<p>";
//print_r($entry_number);
	//
	//echo $a[0]."<br>";
	//echo $a[1];
	
$x = max($flight_number);
for($i=1; $i<$x+1; $i++) {

	print_r(array_unique($c)); echo "<br>";
}
*/
	foreach($_POST['id'] as $id)	{
		$flight_number = ltrim($_POST['flightNumber'.$id],"flight");
		$insertSQL = sprintf("INSERT INTO judging_flights (
		flightTable, 
		flightNumber, 
		flightEntryID
  		) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['flightTable'], "text"),
					   GetSQLValueString($flight_number, "text"),
					   GetSQLValueString($id, "text")
					   );

		//echo $insertSQL."<br>";
		mysql_select_db($database, $brewing);
  		$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
		}
	header(sprintf("Location: %s", $insertGoTo));
}

if (($action == "edit") && ($dbTable == "judging_flights")) { 

	foreach($_POST['id'] as $id)	{
		$flight_number = ltrim($_POST['flightNumber'.$id],"flight");
	
		if ($id <= "999999") {
			$updateSQL = sprintf("UPDATE judging_flights SET
			flightTable=%s,
			flightNumber=%s
			WHERE id=%s",
                       GetSQLValueString($_POST['flightTable'], "text"),
					   GetSQLValueString($flight_number, "text"),
					   GetSQLValueString($id, "text")
					   );

			//echo $updateSQL."<br>";
			mysql_select_db($database, $brewing);
  			$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		}
		if ($id > "999999"){
			$insertSQL = sprintf("INSERT INTO judging_flights (
			flightTable, 
			flightNumber, 
			flightEntryID
  			) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['flightTable'], "text"),
					   GetSQLValueString($flight_number, "text"),
					   GetSQLValueString($_POST['flightEntryID'.$id], "text")
					   );

			//echo $insertSQL."<br>";
			mysql_select_db($database, $brewing);
  			$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
		}
  	}
	header(sprintf("Location: %s", $updateGoTo));
}

if (($action == "assign") && ($dbTable == "judging_flights")) { 

foreach (array_unique($_POST['id']) as $a) {
	
	// Check to see if round has changed for the table/flight.
	if ($_POST['flightRound'.$a] != $_POST['flightRoundPrevious'.$a]) {
	
	// If so, delete all judging/steward assignments for the "old" round
		$query_assignments = sprintf("SELECT id FROM judging_assignments WHERE assignTable='%s' AND assignFlight='%s' AND assignRound='%s' ORDER BY id", $_POST['flightTable'.$a],$_POST['flightNumber'.$a],$_POST['flightRoundPrevious'.$a]);
		$assignments = mysql_query($query_assignments, $brewing) or die(mysql_error());
		$row_assignments = mysql_fetch_assoc($assignments);
		$totalRows_assignments = mysql_num_rows($assignments);
		//echo $query_assignments."<br>";
		if ($totalRows_assignments > 0) {
			do {	
		 		$deleteAssignment = sprintf("DELETE FROM judging_assignments WHERE id='%s'", $row_assignments['id']);
  				$Result = mysql_query($deleteAssignment, $brewing) or die(mysql_error());
				//echo $deleteAssignment.";<br>";
			} while ($row_assignments = mysql_fetch_assoc($assignments)); 
		}
		
		// Change the rounds for all affected table/flight assignments.	
	
		$query_flights = sprintf("SELECT id FROM judging_flights WHERE flightTable='%s' AND flightNumber='%s' ORDER BY id", $_POST['flightTable'.$a],$_POST['flightNumber'.$a]);
		$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
		$row_flights = mysql_fetch_assoc($flights);
		//echo $query_flights."<br>";
		do {
		$updateSQL = sprintf("UPDATE judging_flights SET flightRound=%s WHERE id=%s", 
			GetSQLValueString($_POST['flightRound'.$a], "text"), 
			GetSQLValueString($row_flights['id'], "int")
			);
		mysql_select_db($database, $brewing);
  		$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
		//echo $updateSQL.";<br>";
		} while ($row_flights = mysql_fetch_assoc($flights));
	}
 }
header(sprintf("Location: %s", $updateGoTo));
}

if (($action == "add") && ($dbTable == "judging_scores")) { 

foreach($_POST['score_id'] as $score_id)	{
	if ($_POST['scoreEntry'.$score_id] != "") {
	$insertSQL = sprintf("INSERT INTO judging_scores (
	eid, 
	bid, 
	scoreTable,
	scoreEntry,
	scorePlace,
	scoreType
  	) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eid'.$score_id], "text"),
					   GetSQLValueString($_POST['bid'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreTable'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreEntry'.$score_id], "text"),
					   GetSQLValueString($_POST['scorePlace'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreType'.$score_id], "text")
					   );

	//echo $insertSQL."<br>";
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
		}
	}
	
	header(sprintf("Location: %s", $insertGoTo));

}

if (($action == "edit") && ($dbTable == "judging_scores")) { 
foreach($_POST['score_id'] as $score_id)	{
	if (($_POST['scoreEntry'.$score_id] != "") && ($_POST['scorePrevious'.$score_id] == "Y")) {
	$updateSQL = sprintf("UPDATE judging_scores SET
	eid=%s,
	bid=%s,
	scoreTable=%s,
	scoreEntry=%s,
	scorePlace=%s,
	scoreType=%s
	WHERE id=%s",
                       GetSQLValueString($_POST['eid'.$score_id], "text"),
					   GetSQLValueString($_POST['bid'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreTable'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreEntry'.$score_id], "text"),
					   GetSQLValueString($_POST['scorePlace'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreType'.$score_id], "text"),
					   GetSQLValueString($score_id, "text")
					   );

	//echo $updateSQL."<br>";
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	}
	if (($_POST['scoreEntry'.$score_id] != "") && ($_POST['scorePrevious'.$score_id] == "N")) {
	$insertSQL = sprintf("INSERT INTO judging_scores (
	eid, 
	bid, 
	scoreTable,
	scoreEntry,
	scorePlace,
	scoreType
  	) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eid'.$score_id], "text"),
					   GetSQLValueString($_POST['bid'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreTable'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreEntry'.$score_id], "text"),
					   GetSQLValueString($_POST['scorePlace'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreType'.$score_id], "text")
					   );

	//echo $insertSQL."<br>";
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());		
		}
	}
	header(sprintf("Location: %s", $updateGoTo));

}

if (($action == "enter") && ($dbTable == "judging_scores_bos")) { 
foreach($_POST['score_id'] as $score_id)	{
	if ($_POST['scorePrevious'.$score_id] == "Y") {
	$updateSQL = sprintf("UPDATE judging_scores_bos SET
	eid=%s,
	bid=%s,
	scoreEntry=%s,
	scorePlace=%s,
	scoreType=%s
	WHERE id=%s",
                       GetSQLValueString($_POST['eid'.$score_id], "text"),
					   GetSQLValueString($_POST['bid'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreEntry'.$score_id], "text"),
					   GetSQLValueString($_POST['scorePlace'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreType'.$score_id], "text"),
					   GetSQLValueString($_POST['id'.$score_id], "text")
					   );

	//echo $updateSQL."<br>";
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
	}
	if (($_POST['scorePlace'.$score_id] != "") && ($_POST['scorePrevious'.$score_id] == "N")) {
	$insertSQL = sprintf("INSERT INTO judging_scores_bos (
	eid, 
	bid, 
	scoreEntry,
	scorePlace,
	scoreType
  	) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eid'.$score_id], "text"),
					   GetSQLValueString($_POST['bid'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreEntry'.$score_id], "text"),
					   GetSQLValueString($_POST['scorePlace'.$score_id], "text"),
					   GetSQLValueString($_POST['scoreType'.$score_id], "text")
					   );

	//echo $insertSQL."<br>";
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());		
		}
	}
	header(sprintf("Location: %s", $updateGoTo));

}

if (($action == "add") && ($dbTable == "style_types")) { 
$insertSQL = sprintf("INSERT INTO style_types (
	styleTypeName, 
	styleTypeOwn, 
	styleTypeBOS, 
	styleTypeBOSMethod
	) 
	VALUES 
	(%s, %s, %s, %s)",
                       GetSQLValueString(capitalize($_POST['styleTypeName']), "text"),
                       GetSQLValueString($_POST['styleTypeOwn'], "text"),
                       GetSQLValueString($_POST['styleTypeBOS'], "text"),
					   GetSQLValueString($_POST['styleTypeBOSMethod'], "text"));
	//echo $insertSQL;				   
	mysql_select_db($database, $brewing);
  	$Result1 = mysql_query($insertSQL, $brewing) or die(mysql_error());
	header(sprintf("Location: %s", $insertGoTo));

}

if (($action == "edit") && ($dbTable == "style_types")) { 
$updateSQL = sprintf("UPDATE style_types SET
	styleTypeName=%s, 
	styleTypeOwn=%s, 
	styleTypeBOS=%s, 
	styleTypeBOSMethod=%s
	WHERE id=%s",
                       GetSQLValueString(capitalize($_POST['styleTypeName']), "text"),
                       GetSQLValueString($_POST['styleTypeOwn'], "text"),
                       GetSQLValueString($_POST['styleTypeBOS'], "text"),
					   GetSQLValueString($_POST['styleTypeBOSMethod'], "text"),
                       GetSQLValueString($id, "int"));
	//echo $updateSQL."<br>";
  	mysql_select_db($database_brewing, $brewing);
  	$Result1 = mysql_query($updateSQL, $brewing) or die(mysql_error());
  	header(sprintf("Location: %s", $updateGoTo));

}

if (($action == "update") && ($dbTable == "judging_assignments")) {

	if ($row_judging_prefs['jPrefsQueued'] == "N") {
	foreach ($_POST['random'] as $random) {
		// Check to see if participant is 1) not being "unassigned" and reassigned, and 2) being assigned.
		if (($_POST['unassign'.$random] == 0) && ($_POST['assignFlight'.$random] > 0)) {
			
			//Perform check to see if a record is in the DB. If not, insert a new record.
			// If so, see will update
			$query_flights = sprintf("SELECT COUNT(*) as 'count' FROM judging_assignments WHERE (bid='%s' AND assignRound='%s' AND assignFlight='%s' AND assignLocation='%s')", $_POST['bid'.$random], $_POST['assignRound'.$random], $_POST['assignFlight'.$random], $_POST['assignLocation'.$random]);
			$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
			$row_flights = mysql_fetch_assoc($flights);
			//echo $query_flights."<br>";
			if ($row_flights['count'] == 0) {
			$insertSQL = sprintf("INSERT INTO judging_assignments (bid, assignment, assignTable, assignFlight, assignRound, assignLocation) VALUES (%s, %s, %s, %s, %s, %s)",
           		GetSQLValueString($_POST['bid'.$random], "text"),
           		GetSQLValueString($_POST['assignment'.$random], "text"),
                GetSQLValueString($_POST['assignTable'.$random], "text"),
				GetSQLValueString($_POST['assignFlight'.$random], "text"),
				GetSQLValueString($_POST['assignRound'.$random], "text"),
				GetSQLValueString($_POST['assignLocation'.$random], "text"));
			//echo $insertSQL.";<br>";
			mysql_select_db($database, $brewing);
  			$Result = mysql_query($insertSQL, $brewing) or die(mysql_error());
			}
		}
		
		
		if (($_POST['unassign'.$random] > 0) && ($_POST['assignFlight'.$random] > 0)) {
			$updateSQL = sprintf("UPDATE judging_assignments SET bid=%s, assignment=%s, assignTable=%s, assignFlight=%s, assignRound=%s, assignLocation=%s WHERE id=%s", 
				GetSQLValueString($_POST['bid'.$random], "text"),
           		GetSQLValueString($_POST['assignment'.$random], "text"),
                GetSQLValueString($_POST['assignTable'.$random], "text"),
				GetSQLValueString($_POST['assignFlight'.$random], "text"),
				GetSQLValueString($_POST['assignRound'.$random], "text"),
				GetSQLValueString($_POST['assignLocation'.$random], "text"),
				GetSQLValueString($_POST['unassign'.$random], "text")
				);		   
  			//echo $updateSQL.";<br>";
			mysql_select_db($database, $brewing);
  			$Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
			}
		
		if (($_POST['unassign'.$random] > 0) && ($_POST['assignFlight'.$random] == 0)) {
			$query_flights = sprintf("SELECT id FROM judging_assignments WHERE bid='%s' AND assignRound='%s' and assignLocation='%s'", $_POST['bid'.$random], $_POST['assignRound'.$random], $_POST['assignLocation'.$random]);
			$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
			$row_flights = mysql_fetch_assoc($flights);
			$totalRows_flights = mysql_num_rows($flights);
			//echo $query_flights."<br>";
			
			if ($totalRows_flights > 0) {
				$deleteSQL = sprintf("DELETE FROM judging_assignments WHERE id='%s'", $row_flights['id']);
 				//echo $deleteSQL.";<br>"; 
				mysql_select_db($database, $brewing);
 				$Result = mysql_query($deleteSQL, $brewing) or die(mysql_error());
				}	
			}
		} // end foreach
  } // end if ($row_judging_prefs['jPrefsQueued'] == "N")
  
  if ($row_judging_prefs['jPrefsQueued'] == "Y") {
		foreach ($_POST['random'] as $random) {
			// Check to see if participant is 1) not being "unassigned" and reassigned, and 2) being assigned.
			if (($_POST['unassign'.$random] == 0) && ($_POST['assignRound'.$random] > 0))  {
			//Perform check to see if a record is in the DB. If not, insert a new record.
			// If so, will update
			$query_flights = sprintf("SELECT COUNT(*) as 'count' FROM judging_assignments WHERE (bid='%s' AND assignRound='%s' AND assignLocation='%s')", $_POST['bid'.$random], $_POST['assignRound'.$random], $_POST['assignLocation'.$random]);
			$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
			$row_flights = mysql_fetch_assoc($flights);
			//echo $query_flights."<br>";
				if ($row_flights['count'] == 0) {
				$insertSQL = sprintf("INSERT INTO judging_assignments (bid, assignment, assignTable, assignFlight, assignRound, assignLocation) VALUES (%s, %s, %s, %s, %s, %s)",
           		GetSQLValueString($_POST['bid'.$random], "text"),
           		GetSQLValueString($_POST['assignment'.$random], "text"),
                GetSQLValueString($id, "text"),
				GetSQLValueString("1", "text"),
				GetSQLValueString($_POST['assignRound'.$random], "text"),
				GetSQLValueString($_POST['assignLocation'.$random], "text"));
				//echo $insertSQL.";<br>";
				mysql_select_db($database, $brewing);
  				$Result = mysql_query($insertSQL, $brewing) or die(mysql_error());
				}
			}
		
		
		if (($_POST['unassign'.$random] > 0) && ($_POST['assignRound'.$random] > 0)) {
			$updateSQL = sprintf("UPDATE judging_assignments SET bid=%s, assignment=%s, assignTable=%s, assignFlight=%s, assignRound=%s, assignLocation=%s WHERE id=%s", 
				GetSQLValueString($_POST['bid'.$random], "text"),
           		GetSQLValueString($_POST['assignment'.$random], "text"),
                GetSQLValueString($id, "text"),
				GetSQLValueString("1", "text"),
				GetSQLValueString($_POST['assignRound'.$random], "text"),
				GetSQLValueString($_POST['assignLocation'.$random], "text"),
				GetSQLValueString($_POST['unassign'.$random], "text")
				);		   
  			//echo $updateSQL.";<br>";
			mysql_select_db($database, $brewing);
  			$Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
			}
		
		if (($_POST['unassign'.$random] > 0) && ($_POST['assignRound'.$random] == 0)) {
			/*
			$query_flights = sprintf("SELECT id FROM judging_assignments WHERE id='%s'", $_POST['unassign'.$random]);
			$flights = mysql_query($query_flights, $brewing) or die(mysql_error());
			$row_flights = mysql_fetch_assoc($flights);
			$totalRows_flights = mysql_num_rows($flights);
			echo $query_flights.";<br>";
			*/
			//if ($totalRows_flights > 0) {
				$deleteSQL = sprintf("DELETE FROM judging_assignments WHERE id='%s'", $_POST['unassign'.$random]);
 				//echo $deleteSQL.";<br>"; 
				mysql_select_db($database, $brewing);
 				$Result = mysql_query($deleteSQL, $brewing) or die(mysql_error());
			//	}	
			}
		} // end foreach	  
 }  // end if ($row_judging_prefs['jPrefsQueued'] == "Y")

header(sprintf("Location: %s", $updateGoTo));
} // end if (($action == "update") && ($dbTable == "judging_assignments"))


if ($action == "check_discount") {
	
	mysql_select_db($database, $brewing);
	$query_contest_info = "SELECT contestEntryFeePassword FROM contest_info WHERE id=1";
	$contest_info = mysql_query($query_contest_info, $brewing) or die(mysql_error());
	$row_contest_info = mysql_fetch_assoc($contest_info);
					
	if ($_POST['brewerDiscount'] == $row_contest_info['contestEntryFeePassword']) {
		$updateSQL = sprintf("UPDATE brewer SET brewerDiscount=%s WHERE uid=%s", 
					   GetSQLValueString("Y", "text"),
                       GetSQLValueString($id, "text"));	
		
		//echo $updateSQL;
  		mysql_select_db($database, $brewing);
  		$Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
  		header(sprintf("Location: %s", "../index.php?section=pay&bid=".$id."&msg=12"));
	}
	else header(sprintf("Location: %s", "../index.php?section=pay&bid=".$id."&msg=13"));
}


if ($action == "generate_judging_numbers") {
	
	if ($filter == "default") {
		$query_judging_numbers = "SELECT id FROM brewing ORDER BY id ASC";
		$judging_numbers = mysql_query($query_judging_numbers, $brewing) or die(mysql_error());
		$row_judging_numbers = mysql_fetch_assoc($judging_numbers);
		
		// Clear out all current judging numbers
		do { 
			$updateSQL = sprintf("UPDATE brewing SET brewJudgingNumber=%s WHERE id='%s'", "NULL", $row_judging_numbers['id']);
			
  			mysql_select_db($database, $brewing);
  			$Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
			
			//echo $updateSQL."<br>";
		
		} while ($row_judging_numbers = mysql_fetch_assoc($judging_numbers));
		
		$query_judging_numbers = "SELECT id,brewCategory,brewName FROM brewing ORDER BY $sort $dir";
		$judging_numbers = mysql_query($query_judging_numbers, $brewing) or die(mysql_error());
		$row_judging_numbers = mysql_fetch_assoc($judging_numbers);
		
		// Generate and insert new judging numbers
		do { 	
			$updateSQL = sprintf("UPDATE brewing SET brewJudgingNumber=%s WHERE id=%s", 
					   GetSQLValueString(generate_judging_num($row_judging_numbers['brewCategory']), "text"),
                       GetSQLValueString($row_judging_numbers['id'], "text"));	
  			mysql_select_db($database, $brewing);
  			$Result = mysql_query($updateSQL, $brewing) or die(mysql_error());
			//echo $updateSQL."<br>";
		
		} while ($row_judging_numbers = mysql_fetch_assoc($judging_numbers));
		
	}
	
header(sprintf("Location: %s", "../index.php?section=admin&msg=2"));		
}

?>