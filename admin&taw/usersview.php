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

$users_view = NULL; // Initialize page object first

class cusers_view extends cusers {

	// Page ID
	var $PageID = 'view';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'users';

	// Page object name
	var $PageObjName = 'users_view';

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

	// Page URLs
	var $AddUrl;
	var $EditUrl;
	var $CopyUrl;
	var $DeleteUrl;
	var $ViewUrl;
	var $ListUrl;

	// Export URLs
	var $ExportPrintUrl;
	var $ExportHtmlUrl;
	var $ExportExcelUrl;
	var $ExportWordUrl;
	var $ExportXmlUrl;
	var $ExportCsvUrl;
	var $ExportPdfUrl;

	// Update URLs
	var $InlineAddUrl;
	var $InlineCopyUrl;
	var $InlineEditUrl;
	var $GridAddUrl;
	var $GridEditUrl;
	var $MultiDeleteUrl;
	var $MultiUpdateUrl;

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
		$KeyUrl = "";
		if (@$_GET["id"] <> "") {
			$this->RecKey["id"] = $_GET["id"];
			$KeyUrl .= "&amp;id=" . urlencode($this->RecKey["id"]);
		}
		$this->ExportPrintUrl = $this->PageUrl() . "export=print" . $KeyUrl;
		$this->ExportHtmlUrl = $this->PageUrl() . "export=html" . $KeyUrl;
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel" . $KeyUrl;
		$this->ExportWordUrl = $this->PageUrl() . "export=word" . $KeyUrl;
		$this->ExportXmlUrl = $this->PageUrl() . "export=xml" . $KeyUrl;
		$this->ExportCsvUrl = $this->PageUrl() . "export=csv" . $KeyUrl;
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf" . $KeyUrl;

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'view', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'users', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();

		// Export options
		$this->ExportOptions = new cListOptions();
		$this->ExportOptions->Tag = "div";
		$this->ExportOptions->TagClassName = "ewExportOption";

		// Other options
		$this->OtherOptions['action'] = new cListOptions();
		$this->OtherOptions['action']->Tag = "div";
		$this->OtherOptions['action']->TagClassName = "ewActionOption";
		$this->OtherOptions['detail'] = new cListOptions();
		$this->OtherOptions['detail']->Tag = "div";
		$this->OtherOptions['detail']->TagClassName = "ewDetailOption";
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
	var $ExportOptions; // Export options
	var $OtherOptions = array(); // Other options
	var $DisplayRecs = 1;
	var $StartRec;
	var $StopRec;
	var $TotalRecs = 0;
	var $RecRange = 10;
	var $RecCnt;
	var $RecKey = array();
	var $Recordset;

	//
	// Page main
	//
	function Page_Main() {
		global $Language;
		$sReturnUrl = "";
		$bMatchRecord = FALSE;

		// Set up Breadcrumb
		$this->SetupBreadcrumb();
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET["id"] <> "") {
				$this->id->setQueryStringValue($_GET["id"]);
				$this->RecKey["id"] = $this->id->QueryStringValue;
			} else {
				$sReturnUrl = "userslist.php"; // Return to list
			}

			// Get action
			$this->CurrentAction = "I"; // Display form
			switch ($this->CurrentAction) {
				case "I": // Get a record to display
					if (!$this->LoadRow()) { // Load record based on key
						if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
							$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
						$sReturnUrl = "userslist.php"; // No matching record, return to list
					}
			}
		} else {
			$sReturnUrl = "userslist.php"; // Not page request, return to list
		}
		if ($sReturnUrl <> "")
			$this->Page_Terminate($sReturnUrl);

		// Render row
		$this->RowType = EW_ROWTYPE_VIEW;
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Set up other options
	function SetupOtherOptions() {
		global $Language, $Security;
		$options = &$this->OtherOptions;
		$option = &$options["action"];

		// Add
		$item = &$option->Add("add");
		$item->Body = "<a class=\"ewAction ewAdd\" href=\"" . ew_HtmlEncode($this->AddUrl) . "\">" . $Language->Phrase("ViewPageAddLink") . "</a>";
		$item->Visible = ($this->AddUrl <> "" && $Security->IsLoggedIn());

		// Edit
		$item = &$option->Add("edit");
		$item->Body = "<a class=\"ewAction ewEdit\" href=\"" . ew_HtmlEncode($this->EditUrl) . "\">" . $Language->Phrase("ViewPageEditLink") . "</a>";
		$item->Visible = ($this->EditUrl <> "" && $Security->IsLoggedIn());

		// Copy
		$item = &$option->Add("copy");
		$item->Body = "<a class=\"ewAction ewCopy\" href=\"" . ew_HtmlEncode($this->CopyUrl) . "\">" . $Language->Phrase("ViewPageCopyLink") . "</a>";
		$item->Visible = ($this->CopyUrl <> "" && $Security->IsLoggedIn());

		// Delete
		$item = &$option->Add("delete");
		$item->Body = "<a class=\"ewAction ewDelete\" href=\"" . ew_HtmlEncode($this->DeleteUrl) . "\">" . $Language->Phrase("ViewPageDeleteLink") . "</a>";
		$item->Visible = ($this->DeleteUrl <> "" && $Security->IsLoggedIn());

		// Set up options default
		foreach ($options as &$option) {
			$option->UseDropDownButton = FALSE;
			$option->UseButtonGroup = TRUE;
			$item = &$option->Add($option->GroupOptionName);
			$item->Body = "";
			$item->Visible = FALSE;
		}
		$options["detail"]->DropDownButtonPhrase = $Language->Phrase("ButtonDetails");
		$options["action"]->DropDownButtonPhrase = $Language->Phrase("ButtonActions");
	}

	// Set up starting record parameters
	function SetUpStartRec() {
		if ($this->DisplayRecs == 0)
			return;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET[EW_TABLE_START_REC] <> "") { // Check for "start" parameter
				$this->StartRec = $_GET[EW_TABLE_START_REC];
				$this->setStartRecordNumber($this->StartRec);
			} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
				$PageNo = $_GET[EW_TABLE_PAGE_NO];
				if (is_numeric($PageNo)) {
					$this->StartRec = ($PageNo-1)*$this->DisplayRecs+1;
					if ($this->StartRec <= 0) {
						$this->StartRec = 1;
					} elseif ($this->StartRec >= intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1) {
						$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1;
					}
					$this->setStartRecordNumber($this->StartRec);
				}
			}
		}
		$this->StartRec = $this->getStartRecordNumber();

		// Check if correct start record counter
		if (!is_numeric($this->StartRec) || $this->StartRec == "") { // Avoid invalid start record counter
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} elseif (intval($this->StartRec) > intval($this->TotalRecs)) { // Avoid starting record > total records
			$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to last page first record
			$this->setStartRecordNumber($this->StartRec);
		} elseif (($this->StartRec-1) % $this->DisplayRecs <> 0) {
			$this->StartRec = intval(($this->StartRec-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to page boundary
			$this->setStartRecordNumber($this->StartRec);
		}
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
		$this->AddUrl = $this->GetAddUrl();
		$this->EditUrl = $this->GetEditUrl();
		$this->CopyUrl = $this->GetCopyUrl();
		$this->DeleteUrl = $this->GetDeleteUrl();
		$this->ListUrl = $this->GetListUrl();
		$this->SetupOtherOptions();

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

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "userslist.php", $this->TableVar, TRUE);
		$PageId = "view";
		$Breadcrumb->Add("view", $PageId, ew_CurrentUrl());
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
if (!isset($users_view)) $users_view = new cusers_view();

// Page init
$users_view->Page_Init();

// Page main
$users_view->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$users_view->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var users_view = new ew_Page("users_view");
users_view.PageID = "view"; // Page ID
var EW_PAGE_ID = users_view.PageID; // For backward compatibility

// Form object
var fusersview = new ew_Form("fusersview");

// Form_CustomValidate event
fusersview.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fusersview.ValidateRequired = true;
<?php } else { ?>
fusersview.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<div class="ewViewExportOptions">
<?php $users_view->ExportOptions->Render("body") ?>
<?php if (!$users_view->ExportOptions->UseDropDownButton) { ?>
</div>
<div class="ewViewOtherOptions">
<?php } ?>
<?php
	foreach ($users_view->OtherOptions as &$option)
		$option->Render("body");
?>
</div>
<?php $users_view->ShowPageHeader(); ?>
<?php
$users_view->ShowMessage();
?>
<form name="fusersview" id="fusersview" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="users">
<table class="ewGrid"><tr><td>
<table id="tbl_usersview" class="table table-bordered table-striped">
<?php if ($users->id->Visible) { // id ?>
	<tr id="r_id">
		<td><span id="elh_users_id"><?php echo $users->id->FldCaption() ?></span></td>
		<td<?php echo $users->id->CellAttributes() ?>>
<span id="el_users_id" class="control-group">
<span<?php echo $users->id->ViewAttributes() ?>>
<?php echo $users->id->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->username->Visible) { // username ?>
	<tr id="r_username">
		<td><span id="elh_users_username"><?php echo $users->username->FldCaption() ?></span></td>
		<td<?php echo $users->username->CellAttributes() ?>>
<span id="el_users_username" class="control-group">
<span<?php echo $users->username->ViewAttributes() ?>>
<?php echo $users->username->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->phone->Visible) { // phone ?>
	<tr id="r_phone">
		<td><span id="elh_users_phone"><?php echo $users->phone->FldCaption() ?></span></td>
		<td<?php echo $users->phone->CellAttributes() ?>>
<span id="el_users_phone" class="control-group">
<span<?php echo $users->phone->ViewAttributes() ?>>
<?php echo $users->phone->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->password->Visible) { // password ?>
	<tr id="r_password">
		<td><span id="elh_users_password"><?php echo $users->password->FldCaption() ?></span></td>
		<td<?php echo $users->password->CellAttributes() ?>>
<span id="el_users_password" class="control-group">
<span<?php echo $users->password->ViewAttributes() ?>>
<?php echo $users->password->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->gender->Visible) { // gender ?>
	<tr id="r_gender">
		<td><span id="elh_users_gender"><?php echo $users->gender->FldCaption() ?></span></td>
		<td<?php echo $users->gender->CellAttributes() ?>>
<span id="el_users_gender" class="control-group">
<span<?php echo $users->gender->ViewAttributes() ?>>
<?php echo $users->gender->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->have_car->Visible) { // have_car ?>
	<tr id="r_have_car">
		<td><span id="elh_users_have_car"><?php echo $users->have_car->FldCaption() ?></span></td>
		<td<?php echo $users->have_car->CellAttributes() ?>>
<span id="el_users_have_car" class="control-group">
<span<?php echo $users->have_car->ViewAttributes() ?>>
<?php echo $users->have_car->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->car_number->Visible) { // car_number ?>
	<tr id="r_car_number">
		<td><span id="elh_users_car_number"><?php echo $users->car_number->FldCaption() ?></span></td>
		<td<?php echo $users->car_number->CellAttributes() ?>>
<span id="el_users_car_number" class="control-group">
<span<?php echo $users->car_number->ViewAttributes() ?>>
<?php echo $users->car_number->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->car_model->Visible) { // car_model ?>
	<tr id="r_car_model">
		<td><span id="elh_users_car_model"><?php echo $users->car_model->FldCaption() ?></span></td>
		<td<?php echo $users->car_model->CellAttributes() ?>>
<span id="el_users_car_model" class="control-group">
<span<?php echo $users->car_model->ViewAttributes() ?>>
<?php echo $users->car_model->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->car_color->Visible) { // car_color ?>
	<tr id="r_car_color">
		<td><span id="elh_users_car_color"><?php echo $users->car_color->FldCaption() ?></span></td>
		<td<?php echo $users->car_color->CellAttributes() ?>>
<span id="el_users_car_color" class="control-group">
<span<?php echo $users->car_color->ViewAttributes() ?>>
<?php echo $users->car_color->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->type->Visible) { // type ?>
	<tr id="r_type">
		<td><span id="elh_users_type"><?php echo $users->type->FldCaption() ?></span></td>
		<td<?php echo $users->type->CellAttributes() ?>>
<span id="el_users_type" class="control-group">
<span<?php echo $users->type->ViewAttributes() ?>>
<?php echo $users->type->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->img->Visible) { // img ?>
	<tr id="r_img">
		<td><span id="elh_users_img"><?php echo $users->img->FldCaption() ?></span></td>
		<td<?php echo $users->img->CellAttributes() ?>>
<span id="el_users_img" class="control-group">
<span>
<?php if (!ew_EmptyStr($users->img->ViewValue)) { ?><img src="<?php echo $users->img->ViewValue ?>" alt=""<?php echo $users->img->ViewAttributes() ?>><?php } ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->remember_token->Visible) { // remember_token ?>
	<tr id="r_remember_token">
		<td><span id="elh_users_remember_token"><?php echo $users->remember_token->FldCaption() ?></span></td>
		<td<?php echo $users->remember_token->CellAttributes() ?>>
<span id="el_users_remember_token" class="control-group">
<span<?php echo $users->remember_token->ViewAttributes() ?>>
<?php echo $users->remember_token->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->created_at->Visible) { // created_at ?>
	<tr id="r_created_at">
		<td><span id="elh_users_created_at"><?php echo $users->created_at->FldCaption() ?></span></td>
		<td<?php echo $users->created_at->CellAttributes() ?>>
<span id="el_users_created_at" class="control-group">
<span<?php echo $users->created_at->ViewAttributes() ?>>
<?php echo $users->created_at->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
<?php if ($users->updated_at->Visible) { // updated_at ?>
	<tr id="r_updated_at">
		<td><span id="elh_users_updated_at"><?php echo $users->updated_at->FldCaption() ?></span></td>
		<td<?php echo $users->updated_at->CellAttributes() ?>>
<span id="el_users_updated_at" class="control-group">
<span<?php echo $users->updated_at->ViewAttributes() ?>>
<?php echo $users->updated_at->ViewValue ?></span>
</span>
</td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
</form>
<script type="text/javascript">
fusersview.Init();
</script>
<?php
$users_view->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$users_view->Page_Terminate();
?>
