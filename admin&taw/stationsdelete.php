<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "stationsinfo.php" ?>
<?php include_once "usersinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$stations_delete = NULL; // Initialize page object first

class cstations_delete extends cstations {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'stations';

	// Page object name
	var $PageObjName = 'stations_delete';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-error ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<table class=\"ewStdTable\"><tr><td><div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div></td></tr></table>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (stations)
		if (!isset($GLOBALS["stations"]) || get_class($GLOBALS["stations"]) == "cstations") {
			$GLOBALS["stations"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["stations"];
		}

		// Table object (users)
		if (!isset($GLOBALS['users'])) $GLOBALS['users'] = new cusers();

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'delete', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'stations', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Security
		$Security = new cAdvancedSecurity();
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up curent action
		$this->id->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $TotalRecs = 0;
	var $RecCnt;
	var $RecKeys = array();
	var $Recordset;
	var $StartRowCnt = 1;
	var $RowCnt = 0;

	//
	// Page main
	//
	function Page_Main() {
		global $Language;

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Load key parameters
		$this->RecKeys = $this->GetRecordKeys(); // Load record keys
		$sFilter = $this->GetKeyFilter();
		if ($sFilter == "")
			$this->Page_Terminate("stationslist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in stations class, stationsinfo.php

		$this->CurrentFilter = $sFilter;

		// Get action
		if (@$_POST["a_delete"] <> "") {
			$this->CurrentAction = $_POST["a_delete"];
		} else {
			$this->CurrentAction = "I"; // Display record
		}
		switch ($this->CurrentAction) {
			case "D": // Delete
				$this->SendEmail = TRUE; // Send email on delete success
				if ($this->DeleteRows()) { // Delete rows
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("DeleteSuccess")); // Set up success message
					$this->Page_Terminate($this->getReturnUrl()); // Return to caller
				}
		}
	}

// No functions
	// Load recordset
	function LoadRecordset($offset = -1, $rowcnt = -1) {
		global $conn;

		// Call Recordset Selecting event
		$this->Recordset_Selecting($this->CurrentFilter);

		// Load List page SQL
		$sSql = $this->SelectSQL();
		if ($offset > -1 && $rowcnt > -1)
			$sSql .= " LIMIT $rowcnt OFFSET $offset";

		// Load recordset
		$rs = ew_LoadRecordset($sSql);

		// Call Recordset Selected event
		$this->Recordset_Selected($rs);
		return $rs;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id->setDbValue($rs->fields('id'));
		$this->travel_id->setDbValue($rs->fields('travel_id'));
		$this->station_name->setDbValue($rs->fields('station_name'));
		$this->longitude->setDbValue($rs->fields('longitude'));
		$this->latitude->setDbValue($rs->fields('latitude'));
		$this->created_at->setDbValue($rs->fields('created_at'));
		$this->updated_at->setDbValue($rs->fields('updated_at'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->travel_id->DbValue = $row['travel_id'];
		$this->station_name->DbValue = $row['station_name'];
		$this->longitude->DbValue = $row['longitude'];
		$this->latitude->DbValue = $row['latitude'];
		$this->created_at->DbValue = $row['created_at'];
		$this->updated_at->DbValue = $row['updated_at'];
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// travel_id
		// station_name
		// longitude
		// latitude
		// created_at
		// updated_at

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// travel_id
			if (strval($this->travel_id->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->travel_id->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `travel_name` AS `DispFld`, '' AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `travels`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->travel_id, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->travel_id->ViewValue = $rswrk->fields('DispFld');
					$rswrk->Close();
				} else {
					$this->travel_id->ViewValue = $this->travel_id->CurrentValue;
				}
			} else {
				$this->travel_id->ViewValue = NULL;
			}
			$this->travel_id->ViewCustomAttributes = "";

			// station_name
			$this->station_name->ViewValue = $this->station_name->CurrentValue;
			$this->station_name->ViewCustomAttributes = "";

			// longitude
			$this->longitude->ViewValue = $this->longitude->CurrentValue;
			$this->longitude->ViewCustomAttributes = "";

			// latitude
			$this->latitude->ViewValue = $this->latitude->CurrentValue;
			$this->latitude->ViewCustomAttributes = "";

			// created_at
			$this->created_at->ViewValue = $this->created_at->CurrentValue;
			$this->created_at->ViewValue = ew_FormatDateTime($this->created_at->ViewValue, 5);
			$this->created_at->ViewCustomAttributes = "";

			// updated_at
			$this->updated_at->ViewValue = $this->updated_at->CurrentValue;
			$this->updated_at->ViewValue = ew_FormatDateTime($this->updated_at->ViewValue, 5);
			$this->updated_at->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

			// travel_id
			$this->travel_id->LinkCustomAttributes = "";
			$this->travel_id->HrefValue = "";
			$this->travel_id->TooltipValue = "";

			// station_name
			$this->station_name->LinkCustomAttributes = "";
			$this->station_name->HrefValue = "";
			$this->station_name->TooltipValue = "";

			// longitude
			$this->longitude->LinkCustomAttributes = "";
			$this->longitude->HrefValue = "";
			$this->longitude->TooltipValue = "";

			// latitude
			$this->latitude->LinkCustomAttributes = "";
			$this->latitude->HrefValue = "";
			$this->latitude->TooltipValue = "";

			// created_at
			$this->created_at->LinkCustomAttributes = "";
			$this->created_at->HrefValue = "";
			$this->created_at->TooltipValue = "";

			// updated_at
			$this->updated_at->LinkCustomAttributes = "";
			$this->updated_at->HrefValue = "";
			$this->updated_at->TooltipValue = "";
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	//
	// Delete records based on current filter
	//
	function DeleteRows() {
		global $conn, $Language, $Security;
		$DeleteRows = TRUE;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE) {
			return FALSE;
		} elseif ($rs->EOF) {
			$this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
			$rs->Close();
			return FALSE;

		//} else {
		//	$this->LoadRowValues($rs); // Load row values

		}
		$conn->BeginTrans();

		// Clone old rows
		$rsold = ($rs) ? $rs->GetRows() : array();
		if ($rs)
			$rs->Close();

		// Call row deleting event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$DeleteRows = $this->Row_Deleting($row);
				if (!$DeleteRows) break;
			}
		}
		if ($DeleteRows) {
			$sKey = "";
			foreach ($rsold as $row) {
				$sThisKey = "";
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['id'];
				$conn->raiseErrorFn = 'ew_ErrorFn';
				$DeleteRows = $this->Delete($row); // Delete
				$conn->raiseErrorFn = '';
				if ($DeleteRows === FALSE)
					break;
				if ($sKey <> "") $sKey .= ", ";
				$sKey .= $sThisKey;
			}
		} else {

			// Set up error message
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("DeleteCancelled"));
			}
		}
		if ($DeleteRows) {
			$conn->CommitTrans(); // Commit the changes
		} else {
			$conn->RollbackTrans(); // Rollback changes
		}

		// Call Row Deleted event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$this->Row_Deleted($row);
			}
		}
		return $DeleteRows;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "stationslist.php", $this->TableVar, TRUE);
		$PageId = "delete";
		$Breadcrumb->Add("delete", $PageId, ew_CurrentUrl());
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($stations_delete)) $stations_delete = new cstations_delete();

// Page init
$stations_delete->Page_Init();

// Page main
$stations_delete->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$stations_delete->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var stations_delete = new ew_Page("stations_delete");
stations_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = stations_delete.PageID; // For backward compatibility

// Form object
var fstationsdelete = new ew_Form("fstationsdelete");

// Form_CustomValidate event
fstationsdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fstationsdelete.ValidateRequired = true;
<?php } else { ?>
fstationsdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
fstationsdelete.Lists["x_travel_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_travel_name","","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($stations_delete->Recordset = $stations_delete->LoadRecordset())
	$stations_deleteTotalRecs = $stations_delete->Recordset->RecordCount(); // Get record count
if ($stations_deleteTotalRecs <= 0) { // No record found, exit
	if ($stations_delete->Recordset)
		$stations_delete->Recordset->Close();
	$stations_delete->Page_Terminate("stationslist.php"); // Return to list
}
?>
<?php $Breadcrumb->Render(); ?>
<?php $stations_delete->ShowPageHeader(); ?>
<?php
$stations_delete->ShowMessage();
?>
<form name="fstationsdelete" id="fstationsdelete" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="stations">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($stations_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_stationsdelete" class="ewTable ewTableSeparate">
<?php echo $stations->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
<?php if ($stations->id->Visible) { // id ?>
		<td><span id="elh_stations_id" class="stations_id"><?php echo $stations->id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->travel_id->Visible) { // travel_id ?>
		<td><span id="elh_stations_travel_id" class="stations_travel_id"><?php echo $stations->travel_id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->station_name->Visible) { // station_name ?>
		<td><span id="elh_stations_station_name" class="stations_station_name"><?php echo $stations->station_name->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->longitude->Visible) { // longitude ?>
		<td><span id="elh_stations_longitude" class="stations_longitude"><?php echo $stations->longitude->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->latitude->Visible) { // latitude ?>
		<td><span id="elh_stations_latitude" class="stations_latitude"><?php echo $stations->latitude->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->created_at->Visible) { // created_at ?>
		<td><span id="elh_stations_created_at" class="stations_created_at"><?php echo $stations->created_at->FldCaption() ?></span></td>
<?php } ?>
<?php if ($stations->updated_at->Visible) { // updated_at ?>
		<td><span id="elh_stations_updated_at" class="stations_updated_at"><?php echo $stations->updated_at->FldCaption() ?></span></td>
<?php } ?>
	</tr>
	</thead>
	<tbody>
<?php
$stations_delete->RecCnt = 0;
$i = 0;
while (!$stations_delete->Recordset->EOF) {
	$stations_delete->RecCnt++;
	$stations_delete->RowCnt++;

	// Set row properties
	$stations->ResetAttrs();
	$stations->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$stations_delete->LoadRowValues($stations_delete->Recordset);

	// Render row
	$stations_delete->RenderRow();
?>
	<tr<?php echo $stations->RowAttributes() ?>>
<?php if ($stations->id->Visible) { // id ?>
		<td<?php echo $stations->id->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_id" class="control-group stations_id">
<span<?php echo $stations->id->ViewAttributes() ?>>
<?php echo $stations->id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->travel_id->Visible) { // travel_id ?>
		<td<?php echo $stations->travel_id->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_travel_id" class="control-group stations_travel_id">
<span<?php echo $stations->travel_id->ViewAttributes() ?>>
<?php echo $stations->travel_id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->station_name->Visible) { // station_name ?>
		<td<?php echo $stations->station_name->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_station_name" class="control-group stations_station_name">
<span<?php echo $stations->station_name->ViewAttributes() ?>>
<?php echo $stations->station_name->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->longitude->Visible) { // longitude ?>
		<td<?php echo $stations->longitude->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_longitude" class="control-group stations_longitude">
<span<?php echo $stations->longitude->ViewAttributes() ?>>
<?php echo $stations->longitude->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->latitude->Visible) { // latitude ?>
		<td<?php echo $stations->latitude->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_latitude" class="control-group stations_latitude">
<span<?php echo $stations->latitude->ViewAttributes() ?>>
<?php echo $stations->latitude->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->created_at->Visible) { // created_at ?>
		<td<?php echo $stations->created_at->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_created_at" class="control-group stations_created_at">
<span<?php echo $stations->created_at->ViewAttributes() ?>>
<?php echo $stations->created_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($stations->updated_at->Visible) { // updated_at ?>
		<td<?php echo $stations->updated_at->CellAttributes() ?>>
<span id="el<?php echo $stations_delete->RowCnt ?>_stations_updated_at" class="control-group stations_updated_at">
<span<?php echo $stations->updated_at->ViewAttributes() ?>>
<?php echo $stations->updated_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
	</tr>
<?php
	$stations_delete->Recordset->MoveNext();
}
$stations_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<div class="btn-group ewButtonGroup">
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("DeleteBtn") ?></button>
</div>
</form>
<script type="text/javascript">
fstationsdelete.Init();
</script>
<?php
$stations_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$stations_delete->Page_Terminate();
?>
