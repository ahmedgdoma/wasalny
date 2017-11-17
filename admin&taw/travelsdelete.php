<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "travelsinfo.php" ?>
<?php include_once "usersinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$travels_delete = NULL; // Initialize page object first

class ctravels_delete extends ctravels {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'travels';

	// Page object name
	var $PageObjName = 'travels_delete';

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

		// Table object (travels)
		if (!isset($GLOBALS["travels"]) || get_class($GLOBALS["travels"]) == "ctravels") {
			$GLOBALS["travels"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["travels"];
		}

		// Table object (users)
		if (!isset($GLOBALS['users'])) $GLOBALS['users'] = new cusers();

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'delete', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'travels', TRUE);

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
			$this->Page_Terminate("travelslist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in travels class, travelsinfo.php

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
		$this->user_id->setDbValue($rs->fields('user_id'));
		$this->travel_name->setDbValue($rs->fields('travel_name'));
		$this->start_point->setDbValue($rs->fields('start_point'));
		$this->end_point->setDbValue($rs->fields('end_point'));
		$this->capacity->setDbValue($rs->fields('capacity'));
		$this->start_time->setDbValue($rs->fields('start_time'));
		$this->passenger_gender->setDbValue($rs->fields('passenger_gender'));
		$this->status->setDbValue($rs->fields('status'));
		$this->created_at->setDbValue($rs->fields('created_at'));
		$this->updated_at->setDbValue($rs->fields('updated_at'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->user_id->DbValue = $row['user_id'];
		$this->travel_name->DbValue = $row['travel_name'];
		$this->start_point->DbValue = $row['start_point'];
		$this->end_point->DbValue = $row['end_point'];
		$this->capacity->DbValue = $row['capacity'];
		$this->start_time->DbValue = $row['start_time'];
		$this->passenger_gender->DbValue = $row['passenger_gender'];
		$this->status->DbValue = $row['status'];
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
		// user_id
		// travel_name
		// start_point
		// end_point
		// capacity
		// start_time
		// passenger_gender
		// status
		// created_at
		// updated_at

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// user_id
			if (strval($this->user_id->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->user_id->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `username` AS `DispFld`, '' AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `users`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->user_id, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->user_id->ViewValue = $rswrk->fields('DispFld');
					$rswrk->Close();
				} else {
					$this->user_id->ViewValue = $this->user_id->CurrentValue;
				}
			} else {
				$this->user_id->ViewValue = NULL;
			}
			$this->user_id->ViewCustomAttributes = "";

			// travel_name
			$this->travel_name->ViewValue = $this->travel_name->CurrentValue;
			$this->travel_name->ViewCustomAttributes = "";

			// start_point
			$this->start_point->ViewValue = $this->start_point->CurrentValue;
			$this->start_point->ViewCustomAttributes = "";

			// end_point
			$this->end_point->ViewValue = $this->end_point->CurrentValue;
			$this->end_point->ViewCustomAttributes = "";

			// capacity
			$this->capacity->ViewValue = $this->capacity->CurrentValue;
			$this->capacity->ViewCustomAttributes = "";

			// start_time
			$this->start_time->ViewValue = $this->start_time->CurrentValue;
			$this->start_time->ViewValue = ew_FormatDateTime($this->start_time->ViewValue, 9);
			$this->start_time->ViewCustomAttributes = "";

			// passenger_gender
			if (strval($this->passenger_gender->CurrentValue) <> "") {
				switch ($this->passenger_gender->CurrentValue) {
					case $this->passenger_gender->FldTagValue(1):
						$this->passenger_gender->ViewValue = $this->passenger_gender->FldTagCaption(1) <> "" ? $this->passenger_gender->FldTagCaption(1) : $this->passenger_gender->CurrentValue;
						break;
					case $this->passenger_gender->FldTagValue(2):
						$this->passenger_gender->ViewValue = $this->passenger_gender->FldTagCaption(2) <> "" ? $this->passenger_gender->FldTagCaption(2) : $this->passenger_gender->CurrentValue;
						break;
					case $this->passenger_gender->FldTagValue(3):
						$this->passenger_gender->ViewValue = $this->passenger_gender->FldTagCaption(3) <> "" ? $this->passenger_gender->FldTagCaption(3) : $this->passenger_gender->CurrentValue;
						break;
					default:
						$this->passenger_gender->ViewValue = $this->passenger_gender->CurrentValue;
				}
			} else {
				$this->passenger_gender->ViewValue = NULL;
			}
			$this->passenger_gender->ViewCustomAttributes = "";

			// status
			if (strval($this->status->CurrentValue) <> "") {
				switch ($this->status->CurrentValue) {
					case $this->status->FldTagValue(1):
						$this->status->ViewValue = $this->status->FldTagCaption(1) <> "" ? $this->status->FldTagCaption(1) : $this->status->CurrentValue;
						break;
					case $this->status->FldTagValue(2):
						$this->status->ViewValue = $this->status->FldTagCaption(2) <> "" ? $this->status->FldTagCaption(2) : $this->status->CurrentValue;
						break;
					case $this->status->FldTagValue(3):
						$this->status->ViewValue = $this->status->FldTagCaption(3) <> "" ? $this->status->FldTagCaption(3) : $this->status->CurrentValue;
						break;
					default:
						$this->status->ViewValue = $this->status->CurrentValue;
				}
			} else {
				$this->status->ViewValue = NULL;
			}
			$this->status->ViewCustomAttributes = "";

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

			// user_id
			$this->user_id->LinkCustomAttributes = "";
			$this->user_id->HrefValue = "";
			$this->user_id->TooltipValue = "";

			// travel_name
			$this->travel_name->LinkCustomAttributes = "";
			$this->travel_name->HrefValue = "";
			$this->travel_name->TooltipValue = "";

			// start_point
			$this->start_point->LinkCustomAttributes = "";
			$this->start_point->HrefValue = "";
			$this->start_point->TooltipValue = "";

			// end_point
			$this->end_point->LinkCustomAttributes = "";
			$this->end_point->HrefValue = "";
			$this->end_point->TooltipValue = "";

			// capacity
			$this->capacity->LinkCustomAttributes = "";
			$this->capacity->HrefValue = "";
			$this->capacity->TooltipValue = "";

			// start_time
			$this->start_time->LinkCustomAttributes = "";
			$this->start_time->HrefValue = "";
			$this->start_time->TooltipValue = "";

			// passenger_gender
			$this->passenger_gender->LinkCustomAttributes = "";
			$this->passenger_gender->HrefValue = "";
			$this->passenger_gender->TooltipValue = "";

			// status
			$this->status->LinkCustomAttributes = "";
			$this->status->HrefValue = "";
			$this->status->TooltipValue = "";

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
		$Breadcrumb->Add("list", $this->TableVar, "travelslist.php", $this->TableVar, TRUE);
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
if (!isset($travels_delete)) $travels_delete = new ctravels_delete();

// Page init
$travels_delete->Page_Init();

// Page main
$travels_delete->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$travels_delete->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var travels_delete = new ew_Page("travels_delete");
travels_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = travels_delete.PageID; // For backward compatibility

// Form object
var ftravelsdelete = new ew_Form("ftravelsdelete");

// Form_CustomValidate event
ftravelsdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
ftravelsdelete.ValidateRequired = true;
<?php } else { ?>
ftravelsdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
ftravelsdelete.Lists["x_user_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_username","","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($travels_delete->Recordset = $travels_delete->LoadRecordset())
	$travels_deleteTotalRecs = $travels_delete->Recordset->RecordCount(); // Get record count
if ($travels_deleteTotalRecs <= 0) { // No record found, exit
	if ($travels_delete->Recordset)
		$travels_delete->Recordset->Close();
	$travels_delete->Page_Terminate("travelslist.php"); // Return to list
}
?>
<?php $Breadcrumb->Render(); ?>
<?php $travels_delete->ShowPageHeader(); ?>
<?php
$travels_delete->ShowMessage();
?>
<form name="ftravelsdelete" id="ftravelsdelete" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="travels">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($travels_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_travelsdelete" class="ewTable ewTableSeparate">
<?php echo $travels->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
<?php if ($travels->id->Visible) { // id ?>
		<td><span id="elh_travels_id" class="travels_id"><?php echo $travels->id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->user_id->Visible) { // user_id ?>
		<td><span id="elh_travels_user_id" class="travels_user_id"><?php echo $travels->user_id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->travel_name->Visible) { // travel_name ?>
		<td><span id="elh_travels_travel_name" class="travels_travel_name"><?php echo $travels->travel_name->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->start_point->Visible) { // start_point ?>
		<td><span id="elh_travels_start_point" class="travels_start_point"><?php echo $travels->start_point->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->end_point->Visible) { // end_point ?>
		<td><span id="elh_travels_end_point" class="travels_end_point"><?php echo $travels->end_point->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->capacity->Visible) { // capacity ?>
		<td><span id="elh_travels_capacity" class="travels_capacity"><?php echo $travels->capacity->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->start_time->Visible) { // start_time ?>
		<td><span id="elh_travels_start_time" class="travels_start_time"><?php echo $travels->start_time->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
		<td><span id="elh_travels_passenger_gender" class="travels_passenger_gender"><?php echo $travels->passenger_gender->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->status->Visible) { // status ?>
		<td><span id="elh_travels_status" class="travels_status"><?php echo $travels->status->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->created_at->Visible) { // created_at ?>
		<td><span id="elh_travels_created_at" class="travels_created_at"><?php echo $travels->created_at->FldCaption() ?></span></td>
<?php } ?>
<?php if ($travels->updated_at->Visible) { // updated_at ?>
		<td><span id="elh_travels_updated_at" class="travels_updated_at"><?php echo $travels->updated_at->FldCaption() ?></span></td>
<?php } ?>
	</tr>
	</thead>
	<tbody>
<?php
$travels_delete->RecCnt = 0;
$i = 0;
while (!$travels_delete->Recordset->EOF) {
	$travels_delete->RecCnt++;
	$travels_delete->RowCnt++;

	// Set row properties
	$travels->ResetAttrs();
	$travels->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$travels_delete->LoadRowValues($travels_delete->Recordset);

	// Render row
	$travels_delete->RenderRow();
?>
	<tr<?php echo $travels->RowAttributes() ?>>
<?php if ($travels->id->Visible) { // id ?>
		<td<?php echo $travels->id->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_id" class="control-group travels_id">
<span<?php echo $travels->id->ViewAttributes() ?>>
<?php echo $travels->id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->user_id->Visible) { // user_id ?>
		<td<?php echo $travels->user_id->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_user_id" class="control-group travels_user_id">
<span<?php echo $travels->user_id->ViewAttributes() ?>>
<?php echo $travels->user_id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->travel_name->Visible) { // travel_name ?>
		<td<?php echo $travels->travel_name->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_travel_name" class="control-group travels_travel_name">
<span<?php echo $travels->travel_name->ViewAttributes() ?>>
<?php echo $travels->travel_name->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->start_point->Visible) { // start_point ?>
		<td<?php echo $travels->start_point->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_start_point" class="control-group travels_start_point">
<span<?php echo $travels->start_point->ViewAttributes() ?>>
<?php echo $travels->start_point->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->end_point->Visible) { // end_point ?>
		<td<?php echo $travels->end_point->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_end_point" class="control-group travels_end_point">
<span<?php echo $travels->end_point->ViewAttributes() ?>>
<?php echo $travels->end_point->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->capacity->Visible) { // capacity ?>
		<td<?php echo $travels->capacity->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_capacity" class="control-group travels_capacity">
<span<?php echo $travels->capacity->ViewAttributes() ?>>
<?php echo $travels->capacity->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->start_time->Visible) { // start_time ?>
		<td<?php echo $travels->start_time->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_start_time" class="control-group travels_start_time">
<span<?php echo $travels->start_time->ViewAttributes() ?>>
<?php echo $travels->start_time->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
		<td<?php echo $travels->passenger_gender->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_passenger_gender" class="control-group travels_passenger_gender">
<span<?php echo $travels->passenger_gender->ViewAttributes() ?>>
<?php echo $travels->passenger_gender->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->status->Visible) { // status ?>
		<td<?php echo $travels->status->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_status" class="control-group travels_status">
<span<?php echo $travels->status->ViewAttributes() ?>>
<?php echo $travels->status->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->created_at->Visible) { // created_at ?>
		<td<?php echo $travels->created_at->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_created_at" class="control-group travels_created_at">
<span<?php echo $travels->created_at->ViewAttributes() ?>>
<?php echo $travels->created_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($travels->updated_at->Visible) { // updated_at ?>
		<td<?php echo $travels->updated_at->CellAttributes() ?>>
<span id="el<?php echo $travels_delete->RowCnt ?>_travels_updated_at" class="control-group travels_updated_at">
<span<?php echo $travels->updated_at->ViewAttributes() ?>>
<?php echo $travels->updated_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
	</tr>
<?php
	$travels_delete->Recordset->MoveNext();
}
$travels_delete->Recordset->Close();
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
ftravelsdelete.Init();
</script>
<?php
$travels_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$travels_delete->Page_Terminate();
?>
