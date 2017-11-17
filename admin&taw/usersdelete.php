<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "usersinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$users_delete = NULL; // Initialize page object first

class cusers_delete extends cusers {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'users';

	// Page object name
	var $PageObjName = 'users_delete';

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

		// Table object (users)
		if (!isset($GLOBALS["users"]) || get_class($GLOBALS["users"]) == "cusers") {
			$GLOBALS["users"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["users"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'delete', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'users', TRUE);

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
			$this->Page_Terminate("userslist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in users class, usersinfo.php

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
		$this->username->setDbValue($rs->fields('username'));
		$this->phone->setDbValue($rs->fields('phone'));
		$this->password->setDbValue($rs->fields('password'));
		$this->gender->setDbValue($rs->fields('gender'));
		$this->have_car->setDbValue($rs->fields('have_car'));
		$this->car_number->setDbValue($rs->fields('car_number'));
		$this->car_model->setDbValue($rs->fields('car_model'));
		$this->car_color->setDbValue($rs->fields('car_color'));
		$this->type->setDbValue($rs->fields('type'));
		$this->img->setDbValue($rs->fields('img'));
		$this->remember_token->setDbValue($rs->fields('remember_token'));
		$this->created_at->setDbValue($rs->fields('created_at'));
		$this->updated_at->setDbValue($rs->fields('updated_at'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->username->DbValue = $row['username'];
		$this->phone->DbValue = $row['phone'];
		$this->password->DbValue = $row['password'];
		$this->gender->DbValue = $row['gender'];
		$this->have_car->DbValue = $row['have_car'];
		$this->car_number->DbValue = $row['car_number'];
		$this->car_model->DbValue = $row['car_model'];
		$this->car_color->DbValue = $row['car_color'];
		$this->type->DbValue = $row['type'];
		$this->img->DbValue = $row['img'];
		$this->remember_token->DbValue = $row['remember_token'];
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
		// username
		// phone
		// password
		// gender
		// have_car
		// car_number
		// car_model
		// car_color
		// type
		// img
		// remember_token
		// created_at
		// updated_at

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// username
			$this->username->ViewValue = $this->username->CurrentValue;
			$this->username->ViewCustomAttributes = "";

			// phone
			$this->phone->ViewValue = $this->phone->CurrentValue;
			$this->phone->ViewCustomAttributes = "";

			// password
			$this->password->ViewValue = $this->password->CurrentValue;
			$this->password->ViewCustomAttributes = "";

			// gender
			if (strval($this->gender->CurrentValue) <> "") {
				switch ($this->gender->CurrentValue) {
					case $this->gender->FldTagValue(1):
						$this->gender->ViewValue = $this->gender->FldTagCaption(1) <> "" ? $this->gender->FldTagCaption(1) : $this->gender->CurrentValue;
						break;
					case $this->gender->FldTagValue(2):
						$this->gender->ViewValue = $this->gender->FldTagCaption(2) <> "" ? $this->gender->FldTagCaption(2) : $this->gender->CurrentValue;
						break;
					default:
						$this->gender->ViewValue = $this->gender->CurrentValue;
				}
			} else {
				$this->gender->ViewValue = NULL;
			}
			$this->gender->ViewCustomAttributes = "";

			// have_car
			if (ew_ConvertToBool($this->have_car->CurrentValue)) {
				$this->have_car->ViewValue = $this->have_car->FldTagCaption(2) <> "" ? $this->have_car->FldTagCaption(2) : "1";
			} else {
				$this->have_car->ViewValue = $this->have_car->FldTagCaption(1) <> "" ? $this->have_car->FldTagCaption(1) : "0";
			}
			$this->have_car->ViewCustomAttributes = "";

			// car_number
			$this->car_number->ViewValue = $this->car_number->CurrentValue;
			$this->car_number->ViewCustomAttributes = "";

			// car_model
			$this->car_model->ViewValue = $this->car_model->CurrentValue;
			$this->car_model->ViewCustomAttributes = "";

			// car_color
			$this->car_color->ViewValue = $this->car_color->CurrentValue;
			$this->car_color->ViewCustomAttributes = "";

			// type
			if (strval($this->type->CurrentValue) <> "") {
				switch ($this->type->CurrentValue) {
					case $this->type->FldTagValue(1):
						$this->type->ViewValue = $this->type->FldTagCaption(1) <> "" ? $this->type->FldTagCaption(1) : $this->type->CurrentValue;
						break;
					case $this->type->FldTagValue(2):
						$this->type->ViewValue = $this->type->FldTagCaption(2) <> "" ? $this->type->FldTagCaption(2) : $this->type->CurrentValue;
						break;
					default:
						$this->type->ViewValue = $this->type->CurrentValue;
				}
			} else {
				$this->type->ViewValue = NULL;
			}
			$this->type->ViewCustomAttributes = "";

			// img
			$this->img->ViewValue = $this->img->CurrentValue;
			$this->img->ImageAlt = $this->img->FldAlt();
			$this->img->ViewCustomAttributes = "";

			// remember_token
			$this->remember_token->ViewValue = $this->remember_token->CurrentValue;
			$this->remember_token->ViewCustomAttributes = "";

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

			// username
			$this->username->LinkCustomAttributes = "";
			$this->username->HrefValue = "";
			$this->username->TooltipValue = "";

			// phone
			$this->phone->LinkCustomAttributes = "";
			$this->phone->HrefValue = "";
			$this->phone->TooltipValue = "";

			// password
			$this->password->LinkCustomAttributes = "";
			$this->password->HrefValue = "";
			$this->password->TooltipValue = "";

			// gender
			$this->gender->LinkCustomAttributes = "";
			$this->gender->HrefValue = "";
			$this->gender->TooltipValue = "";

			// have_car
			$this->have_car->LinkCustomAttributes = "";
			$this->have_car->HrefValue = "";
			$this->have_car->TooltipValue = "";

			// car_number
			$this->car_number->LinkCustomAttributes = "";
			$this->car_number->HrefValue = "";
			$this->car_number->TooltipValue = "";

			// car_model
			$this->car_model->LinkCustomAttributes = "";
			$this->car_model->HrefValue = "";
			$this->car_model->TooltipValue = "";

			// car_color
			$this->car_color->LinkCustomAttributes = "";
			$this->car_color->HrefValue = "";
			$this->car_color->TooltipValue = "";

			// type
			$this->type->LinkCustomAttributes = "";
			$this->type->HrefValue = "";
			$this->type->TooltipValue = "";

			// img
			$this->img->LinkCustomAttributes = "";
			$this->img->HrefValue = "";
			$this->img->TooltipValue = "";

			// remember_token
			$this->remember_token->LinkCustomAttributes = "";
			$this->remember_token->HrefValue = "";
			$this->remember_token->TooltipValue = "";

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
		$Breadcrumb->Add("list", $this->TableVar, "userslist.php", $this->TableVar, TRUE);
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
if (!isset($users_delete)) $users_delete = new cusers_delete();

// Page init
$users_delete->Page_Init();

// Page main
$users_delete->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$users_delete->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var users_delete = new ew_Page("users_delete");
users_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = users_delete.PageID; // For backward compatibility

// Form object
var fusersdelete = new ew_Form("fusersdelete");

// Form_CustomValidate event
fusersdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fusersdelete.ValidateRequired = true;
<?php } else { ?>
fusersdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($users_delete->Recordset = $users_delete->LoadRecordset())
	$users_deleteTotalRecs = $users_delete->Recordset->RecordCount(); // Get record count
if ($users_deleteTotalRecs <= 0) { // No record found, exit
	if ($users_delete->Recordset)
		$users_delete->Recordset->Close();
	$users_delete->Page_Terminate("userslist.php"); // Return to list
}
?>
<?php $Breadcrumb->Render(); ?>
<?php $users_delete->ShowPageHeader(); ?>
<?php
$users_delete->ShowMessage();
?>
<form name="fusersdelete" id="fusersdelete" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="users">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($users_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_usersdelete" class="ewTable ewTableSeparate">
<?php echo $users->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
<?php if ($users->id->Visible) { // id ?>
		<td><span id="elh_users_id" class="users_id"><?php echo $users->id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->username->Visible) { // username ?>
		<td><span id="elh_users_username" class="users_username"><?php echo $users->username->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->phone->Visible) { // phone ?>
		<td><span id="elh_users_phone" class="users_phone"><?php echo $users->phone->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->password->Visible) { // password ?>
		<td><span id="elh_users_password" class="users_password"><?php echo $users->password->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->gender->Visible) { // gender ?>
		<td><span id="elh_users_gender" class="users_gender"><?php echo $users->gender->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->have_car->Visible) { // have_car ?>
		<td><span id="elh_users_have_car" class="users_have_car"><?php echo $users->have_car->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->car_number->Visible) { // car_number ?>
		<td><span id="elh_users_car_number" class="users_car_number"><?php echo $users->car_number->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->car_model->Visible) { // car_model ?>
		<td><span id="elh_users_car_model" class="users_car_model"><?php echo $users->car_model->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->car_color->Visible) { // car_color ?>
		<td><span id="elh_users_car_color" class="users_car_color"><?php echo $users->car_color->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->type->Visible) { // type ?>
		<td><span id="elh_users_type" class="users_type"><?php echo $users->type->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->img->Visible) { // img ?>
		<td><span id="elh_users_img" class="users_img"><?php echo $users->img->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->remember_token->Visible) { // remember_token ?>
		<td><span id="elh_users_remember_token" class="users_remember_token"><?php echo $users->remember_token->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->created_at->Visible) { // created_at ?>
		<td><span id="elh_users_created_at" class="users_created_at"><?php echo $users->created_at->FldCaption() ?></span></td>
<?php } ?>
<?php if ($users->updated_at->Visible) { // updated_at ?>
		<td><span id="elh_users_updated_at" class="users_updated_at"><?php echo $users->updated_at->FldCaption() ?></span></td>
<?php } ?>
	</tr>
	</thead>
	<tbody>
<?php
$users_delete->RecCnt = 0;
$i = 0;
while (!$users_delete->Recordset->EOF) {
	$users_delete->RecCnt++;
	$users_delete->RowCnt++;

	// Set row properties
	$users->ResetAttrs();
	$users->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$users_delete->LoadRowValues($users_delete->Recordset);

	// Render row
	$users_delete->RenderRow();
?>
	<tr<?php echo $users->RowAttributes() ?>>
<?php if ($users->id->Visible) { // id ?>
		<td<?php echo $users->id->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_id" class="control-group users_id">
<span<?php echo $users->id->ViewAttributes() ?>>
<?php echo $users->id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->username->Visible) { // username ?>
		<td<?php echo $users->username->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_username" class="control-group users_username">
<span<?php echo $users->username->ViewAttributes() ?>>
<?php echo $users->username->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->phone->Visible) { // phone ?>
		<td<?php echo $users->phone->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_phone" class="control-group users_phone">
<span<?php echo $users->phone->ViewAttributes() ?>>
<?php echo $users->phone->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->password->Visible) { // password ?>
		<td<?php echo $users->password->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_password" class="control-group users_password">
<span<?php echo $users->password->ViewAttributes() ?>>
<?php echo $users->password->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->gender->Visible) { // gender ?>
		<td<?php echo $users->gender->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_gender" class="control-group users_gender">
<span<?php echo $users->gender->ViewAttributes() ?>>
<?php echo $users->gender->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->have_car->Visible) { // have_car ?>
		<td<?php echo $users->have_car->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_have_car" class="control-group users_have_car">
<span<?php echo $users->have_car->ViewAttributes() ?>>
<?php echo $users->have_car->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->car_number->Visible) { // car_number ?>
		<td<?php echo $users->car_number->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_car_number" class="control-group users_car_number">
<span<?php echo $users->car_number->ViewAttributes() ?>>
<?php echo $users->car_number->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->car_model->Visible) { // car_model ?>
		<td<?php echo $users->car_model->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_car_model" class="control-group users_car_model">
<span<?php echo $users->car_model->ViewAttributes() ?>>
<?php echo $users->car_model->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->car_color->Visible) { // car_color ?>
		<td<?php echo $users->car_color->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_car_color" class="control-group users_car_color">
<span<?php echo $users->car_color->ViewAttributes() ?>>
<?php echo $users->car_color->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->type->Visible) { // type ?>
		<td<?php echo $users->type->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_type" class="control-group users_type">
<span<?php echo $users->type->ViewAttributes() ?>>
<?php echo $users->type->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->img->Visible) { // img ?>
		<td<?php echo $users->img->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_img" class="control-group users_img">
<span>
<?php if (!ew_EmptyStr($users->img->ListViewValue())) { ?><img src="<?php echo $users->img->ListViewValue() ?>" alt=""<?php echo $users->img->ViewAttributes() ?>><?php } ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->remember_token->Visible) { // remember_token ?>
		<td<?php echo $users->remember_token->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_remember_token" class="control-group users_remember_token">
<span<?php echo $users->remember_token->ViewAttributes() ?>>
<?php echo $users->remember_token->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->created_at->Visible) { // created_at ?>
		<td<?php echo $users->created_at->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_created_at" class="control-group users_created_at">
<span<?php echo $users->created_at->ViewAttributes() ?>>
<?php echo $users->created_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($users->updated_at->Visible) { // updated_at ?>
		<td<?php echo $users->updated_at->CellAttributes() ?>>
<span id="el<?php echo $users_delete->RowCnt ?>_users_updated_at" class="control-group users_updated_at">
<span<?php echo $users->updated_at->ViewAttributes() ?>>
<?php echo $users->updated_at->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
	</tr>
<?php
	$users_delete->Recordset->MoveNext();
}
$users_delete->Recordset->Close();
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
fusersdelete.Init();
</script>
<?php
$users_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$users_delete->Page_Terminate();
?>
