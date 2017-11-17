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

$users_add = NULL; // Initialize page object first

class cusers_add extends cusers {

	// Page ID
	var $PageID = 'add';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'users';

	// Page object name
	var $PageObjName = 'users_add';

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
			define("EW_PAGE_ID", 'add', TRUE);

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

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up curent action

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
	var $DbMasterFilter = "";
	var $DbDetailFilter = "";
	var $Priv = 0;
	var $OldRecordset;
	var $CopyRecord;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Process form if post back
		if (@$_POST["a_add"] <> "") {
			$this->CurrentAction = $_POST["a_add"]; // Get form action
			$this->CopyRecord = $this->LoadOldRecord(); // Load old recordset
			$this->LoadFormValues(); // Load form values
		} else { // Not post back

			// Load key values from QueryString
			$this->CopyRecord = TRUE;
			if (@$_GET["id"] != "") {
				$this->id->setQueryStringValue($_GET["id"]);
				$this->setKey("id", $this->id->CurrentValue); // Set up key
			} else {
				$this->setKey("id", ""); // Clear key
				$this->CopyRecord = FALSE;
			}
			if ($this->CopyRecord) {
				$this->CurrentAction = "C"; // Copy record
			} else {
				$this->CurrentAction = "I"; // Display blank record
				$this->LoadDefaultValues(); // Load default values
			}
		}

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Validate form if post back
		if (@$_POST["a_add"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = "I"; // Form error, reset action
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues(); // Restore form values
				$this->setFailureMessage($gsFormError);
			}
		}

		// Perform action based on action code
		switch ($this->CurrentAction) {
			case "I": // Blank record, no action required
				break;
			case "C": // Copy an existing record
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("userslist.php"); // No matching record, return to list
				}
				break;
			case "A": // Add new record
				$this->SendEmail = TRUE; // Send email on add success
				if ($this->AddRow($this->OldRecordset)) { // Add successful
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("AddSuccess")); // Set up success message
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "usersview.php")
						$sReturnUrl = $this->GetViewUrl(); // View paging, return to view page with keyurl directly
					$this->Page_Terminate($sReturnUrl); // Clean up and return
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Add failed, restore form values
				}
		}

		// Render row based on row type
		$this->RowType = EW_ROWTYPE_ADD;  // Render add type

		// Render row
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm;

		// Get upload data
	}

	// Load default values
	function LoadDefaultValues() {
		$this->username->CurrentValue = NULL;
		$this->username->OldValue = $this->username->CurrentValue;
		$this->phone->CurrentValue = NULL;
		$this->phone->OldValue = $this->phone->CurrentValue;
		$this->password->CurrentValue = NULL;
		$this->password->OldValue = $this->password->CurrentValue;
		$this->gender->CurrentValue = NULL;
		$this->gender->OldValue = $this->gender->CurrentValue;
		$this->have_car->CurrentValue = NULL;
		$this->have_car->OldValue = $this->have_car->CurrentValue;
		$this->car_number->CurrentValue = NULL;
		$this->car_number->OldValue = $this->car_number->CurrentValue;
		$this->car_model->CurrentValue = NULL;
		$this->car_model->OldValue = $this->car_model->CurrentValue;
		$this->car_color->CurrentValue = NULL;
		$this->car_color->OldValue = $this->car_color->CurrentValue;
		$this->type->CurrentValue = NULL;
		$this->type->OldValue = $this->type->CurrentValue;
		$this->img->CurrentValue = NULL;
		$this->img->OldValue = $this->img->CurrentValue;
		$this->remember_token->CurrentValue = NULL;
		$this->remember_token->OldValue = $this->remember_token->CurrentValue;
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->username->FldIsDetailKey) {
			$this->username->setFormValue($objForm->GetValue("x_username"));
		}
		if (!$this->phone->FldIsDetailKey) {
			$this->phone->setFormValue($objForm->GetValue("x_phone"));
		}
		if (!$this->password->FldIsDetailKey) {
			$this->password->setFormValue($objForm->GetValue("x_password"));
		}
		if (!$this->gender->FldIsDetailKey) {
			$this->gender->setFormValue($objForm->GetValue("x_gender"));
		}
		if (!$this->have_car->FldIsDetailKey) {
			$this->have_car->setFormValue($objForm->GetValue("x_have_car"));
		}
		if (!$this->car_number->FldIsDetailKey) {
			$this->car_number->setFormValue($objForm->GetValue("x_car_number"));
		}
		if (!$this->car_model->FldIsDetailKey) {
			$this->car_model->setFormValue($objForm->GetValue("x_car_model"));
		}
		if (!$this->car_color->FldIsDetailKey) {
			$this->car_color->setFormValue($objForm->GetValue("x_car_color"));
		}
		if (!$this->type->FldIsDetailKey) {
			$this->type->setFormValue($objForm->GetValue("x_type"));
		}
		if (!$this->img->FldIsDetailKey) {
			$this->img->setFormValue($objForm->GetValue("x_img"));
		}
		if (!$this->remember_token->FldIsDetailKey) {
			$this->remember_token->setFormValue($objForm->GetValue("x_remember_token"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadOldRecord();
		$this->username->CurrentValue = $this->username->FormValue;
		$this->phone->CurrentValue = $this->phone->FormValue;
		$this->password->CurrentValue = $this->password->FormValue;
		$this->gender->CurrentValue = $this->gender->FormValue;
		$this->have_car->CurrentValue = $this->have_car->FormValue;
		$this->car_number->CurrentValue = $this->car_number->FormValue;
		$this->car_model->CurrentValue = $this->car_model->FormValue;
		$this->car_color->CurrentValue = $this->car_color->FormValue;
		$this->type->CurrentValue = $this->type->FormValue;
		$this->img->CurrentValue = $this->img->FormValue;
		$this->remember_token->CurrentValue = $this->remember_token->FormValue;
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

	// Load old record
	function LoadOldRecord() {

		// Load key values from Session
		$bValidKey = TRUE;
		if (strval($this->getKey("id")) <> "")
			$this->id->CurrentValue = $this->getKey("id"); // id
		else
			$bValidKey = FALSE;

		// Load old recordset
		if ($bValidKey) {
			$this->CurrentFilter = $this->KeyFilter();
			$sSql = $this->SQL();
			$this->OldRecordset = ew_LoadRecordset($sSql);
			$this->LoadRowValues($this->OldRecordset); // Load row values
		} else {
			$this->OldRecordset = NULL;
		}
		return $bValidKey;
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
		} elseif ($this->RowType == EW_ROWTYPE_ADD) { // Add row

			// username
			$this->username->EditCustomAttributes = "";
			$this->username->EditValue = ew_HtmlEncode($this->username->CurrentValue);
			$this->username->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->username->FldCaption()));

			// phone
			$this->phone->EditCustomAttributes = "";
			$this->phone->EditValue = ew_HtmlEncode($this->phone->CurrentValue);
			$this->phone->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->phone->FldCaption()));

			// password
			$this->password->EditCustomAttributes = "";
			$this->password->EditValue = ew_HtmlEncode($this->password->CurrentValue);
			$this->password->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->password->FldCaption()));

			// gender
			$this->gender->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->gender->FldTagValue(1), $this->gender->FldTagCaption(1) <> "" ? $this->gender->FldTagCaption(1) : $this->gender->FldTagValue(1));
			$arwrk[] = array($this->gender->FldTagValue(2), $this->gender->FldTagCaption(2) <> "" ? $this->gender->FldTagCaption(2) : $this->gender->FldTagValue(2));
			$this->gender->EditValue = $arwrk;

			// have_car
			$this->have_car->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->have_car->FldTagValue(1), $this->have_car->FldTagCaption(1) <> "" ? $this->have_car->FldTagCaption(1) : $this->have_car->FldTagValue(1));
			$arwrk[] = array($this->have_car->FldTagValue(2), $this->have_car->FldTagCaption(2) <> "" ? $this->have_car->FldTagCaption(2) : $this->have_car->FldTagValue(2));
			$this->have_car->EditValue = $arwrk;

			// car_number
			$this->car_number->EditCustomAttributes = "";
			$this->car_number->EditValue = ew_HtmlEncode($this->car_number->CurrentValue);
			$this->car_number->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_number->FldCaption()));

			// car_model
			$this->car_model->EditCustomAttributes = "";
			$this->car_model->EditValue = ew_HtmlEncode($this->car_model->CurrentValue);
			$this->car_model->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_model->FldCaption()));

			// car_color
			$this->car_color->EditCustomAttributes = "";
			$this->car_color->EditValue = ew_HtmlEncode($this->car_color->CurrentValue);
			$this->car_color->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_color->FldCaption()));

			// type
			$this->type->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->type->FldTagValue(1), $this->type->FldTagCaption(1) <> "" ? $this->type->FldTagCaption(1) : $this->type->FldTagValue(1));
			$arwrk[] = array($this->type->FldTagValue(2), $this->type->FldTagCaption(2) <> "" ? $this->type->FldTagCaption(2) : $this->type->FldTagValue(2));
			$this->type->EditValue = $arwrk;

			// img
			$this->img->EditCustomAttributes = "";
			$this->img->EditValue = ew_HtmlEncode($this->img->CurrentValue);
			$this->img->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->img->FldCaption()));

			// remember_token
			$this->remember_token->EditCustomAttributes = "";
			$this->remember_token->EditValue = ew_HtmlEncode($this->remember_token->CurrentValue);
			$this->remember_token->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->remember_token->FldCaption()));

			// Edit refer script
			// username

			$this->username->HrefValue = "";

			// phone
			$this->phone->HrefValue = "";

			// password
			$this->password->HrefValue = "";

			// gender
			$this->gender->HrefValue = "";

			// have_car
			$this->have_car->HrefValue = "";

			// car_number
			$this->car_number->HrefValue = "";

			// car_model
			$this->car_model->HrefValue = "";

			// car_color
			$this->car_color->HrefValue = "";

			// type
			$this->type->HrefValue = "";

			// img
			$this->img->HrefValue = "";

			// remember_token
			$this->remember_token->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!$this->username->FldIsDetailKey && !is_null($this->username->FormValue) && $this->username->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->username->FldCaption());
		}
		if (!$this->phone->FldIsDetailKey && !is_null($this->phone->FormValue) && $this->phone->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->phone->FldCaption());
		}
		if (!$this->password->FldIsDetailKey && !is_null($this->password->FormValue) && $this->password->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->password->FldCaption());
		}
		if ($this->gender->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->gender->FldCaption());
		}
		if ($this->have_car->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->have_car->FldCaption());
		}
		if (!$this->car_number->FldIsDetailKey && !is_null($this->car_number->FormValue) && $this->car_number->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->car_number->FldCaption());
		}
		if (!$this->car_model->FldIsDetailKey && !is_null($this->car_model->FormValue) && $this->car_model->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->car_model->FldCaption());
		}
		if (!$this->car_color->FldIsDetailKey && !is_null($this->car_color->FormValue) && $this->car_color->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->car_color->FldCaption());
		}
		if ($this->type->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->type->FldCaption());
		}
		if (!$this->img->FldIsDetailKey && !is_null($this->img->FormValue) && $this->img->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->img->FldCaption());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Add record
	function AddRow($rsold = NULL) {
		global $conn, $Language, $Security;

		// Load db values from rsold
		if ($rsold) {
			$this->LoadDbValues($rsold);
		}
		$rsnew = array();

		// username
		$this->username->SetDbValueDef($rsnew, $this->username->CurrentValue, "", FALSE);

		// phone
		$this->phone->SetDbValueDef($rsnew, $this->phone->CurrentValue, "", FALSE);

		// password
		$this->password->SetDbValueDef($rsnew, $this->password->CurrentValue, "", FALSE);

		// gender
		$this->gender->SetDbValueDef($rsnew, $this->gender->CurrentValue, "", FALSE);

		// have_car
		$this->have_car->SetDbValueDef($rsnew, ((strval($this->have_car->CurrentValue) == "1") ? "1" : "0"), 0, FALSE);

		// car_number
		$this->car_number->SetDbValueDef($rsnew, $this->car_number->CurrentValue, "", FALSE);

		// car_model
		$this->car_model->SetDbValueDef($rsnew, $this->car_model->CurrentValue, "", FALSE);

		// car_color
		$this->car_color->SetDbValueDef($rsnew, $this->car_color->CurrentValue, "", FALSE);

		// type
		$this->type->SetDbValueDef($rsnew, $this->type->CurrentValue, "", FALSE);

		// img
		$this->img->SetDbValueDef($rsnew, $this->img->CurrentValue, "", FALSE);

		// remember_token
		$this->remember_token->SetDbValueDef($rsnew, $this->remember_token->CurrentValue, NULL, FALSE);

		// Call Row Inserting event
		$rs = ($rsold == NULL) ? NULL : $rsold->fields;
		$bInsertRow = $this->Row_Inserting($rs, $rsnew);
		if ($bInsertRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$AddRow = $this->Insert($rsnew);
			$conn->raiseErrorFn = '';
			if ($AddRow) {
			}
		} else {
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("InsertCancelled"));
			}
			$AddRow = FALSE;
		}

		// Get insert id if necessary
		if ($AddRow) {
			$this->id->setDbValue($conn->Insert_ID());
			$rsnew['id'] = $this->id->DbValue;
		}
		if ($AddRow) {

			// Call Row Inserted event
			$rs = ($rsold == NULL) ? NULL : $rsold->fields;
			$this->Row_Inserted($rs, $rsnew);
		}
		return $AddRow;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "userslist.php", $this->TableVar, TRUE);
		$PageId = ($this->CurrentAction == "C") ? "Copy" : "Add";
		$Breadcrumb->Add("add", $PageId, ew_CurrentUrl());
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

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($users_add)) $users_add = new cusers_add();

// Page init
$users_add->Page_Init();

// Page main
$users_add->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$users_add->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var users_add = new ew_Page("users_add");
users_add.PageID = "add"; // Page ID
var EW_PAGE_ID = users_add.PageID; // For backward compatibility

// Form object
var fusersadd = new ew_Form("fusersadd");

// Validate form
fusersadd.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);
	this.PostAutoSuggest();
	if ($fobj.find("#a_confirm").val() == "F")
		return true;
	var elm, felm, uelm, addcnt = 0;
	var $k = $fobj.find("#" + this.FormKeyCountName); // Get key_count
	var rowcnt = ($k[0]) ? parseInt($k.val(), 10) : 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // Check rowcnt == 0 => Inline-Add
	var gridinsert = $fobj.find("#a_list").val() == "gridinsert";
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = ($k[0]) ? String(i) : "";
		$fobj.data("rowindex", infix);
			elm = this.GetElements("x" + infix + "_username");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->username->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_phone");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->phone->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_password");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->password->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_gender");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->gender->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_have_car");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->have_car->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_car_number");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->car_number->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_car_model");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->car_model->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_car_color");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->car_color->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_type");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->type->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_img");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($users->img->FldCaption()) ?>");

			// Set up row object
			ew_ElementsToRow(fobj);

			// Fire Form_CustomValidate event
			if (!this.Form_CustomValidate(fobj))
				return false;
	}

	// Process detail forms
	var dfs = $fobj.find("input[name='detailpage']").get();
	for (var i = 0; i < dfs.length; i++) {
		var df = dfs[i], val = df.value;
		if (val && ewForms[val])
			if (!ewForms[val].Validate())
				return false;
	}
	return true;
}

// Form_CustomValidate event
fusersadd.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fusersadd.ValidateRequired = true;
<?php } else { ?>
fusersadd.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php $users_add->ShowPageHeader(); ?>
<?php
$users_add->ShowMessage();
?>
<form name="fusersadd" id="fusersadd" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="users">
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewGrid"><tr><td>
<table id="tbl_usersadd" class="table table-bordered table-striped">
<?php if ($users->username->Visible) { // username ?>
	<tr id="r_username">
		<td><span id="elh_users_username"><?php echo $users->username->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->username->CellAttributes() ?>>
<span id="el_users_username" class="control-group">
<input type="text" data-field="x_username" name="x_username" id="x_username" size="30" maxlength="255" placeholder="<?php echo $users->username->PlaceHolder ?>" value="<?php echo $users->username->EditValue ?>"<?php echo $users->username->EditAttributes() ?>>
</span>
<?php echo $users->username->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->phone->Visible) { // phone ?>
	<tr id="r_phone">
		<td><span id="elh_users_phone"><?php echo $users->phone->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->phone->CellAttributes() ?>>
<span id="el_users_phone" class="control-group">
<input type="text" data-field="x_phone" name="x_phone" id="x_phone" size="30" maxlength="255" placeholder="<?php echo $users->phone->PlaceHolder ?>" value="<?php echo $users->phone->EditValue ?>"<?php echo $users->phone->EditAttributes() ?>>
</span>
<?php echo $users->phone->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->password->Visible) { // password ?>
	<tr id="r_password">
		<td><span id="elh_users_password"><?php echo $users->password->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->password->CellAttributes() ?>>
<span id="el_users_password" class="control-group">
<input type="text" data-field="x_password" name="x_password" id="x_password" size="30" maxlength="255" placeholder="<?php echo $users->password->PlaceHolder ?>" value="<?php echo $users->password->EditValue ?>"<?php echo $users->password->EditAttributes() ?>>
</span>
<?php echo $users->password->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->gender->Visible) { // gender ?>
	<tr id="r_gender">
		<td><span id="elh_users_gender"><?php echo $users->gender->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->gender->CellAttributes() ?>>
<span id="el_users_gender" class="control-group">
<div id="tp_x_gender" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_gender" id="x_gender" value="{value}"<?php echo $users->gender->EditAttributes() ?>></div>
<div id="dsl_x_gender" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->gender->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->gender->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;

		// Note: No spacing within the LABEL tag
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<label class="radio"><input type="radio" data-field="x_gender" name="x_gender" id="x_gender_<?php echo $rowcntwrk ?>" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $users->gender->EditAttributes() ?>><?php echo $arwrk[$rowcntwrk][1] ?></label>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
<?php
	}
}
?>
</div>
</span>
<?php echo $users->gender->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->have_car->Visible) { // have_car ?>
	<tr id="r_have_car">
		<td><span id="elh_users_have_car"><?php echo $users->have_car->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->have_car->CellAttributes() ?>>
<span id="el_users_have_car" class="control-group">
<div id="tp_x_have_car" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_have_car" id="x_have_car" value="{value}"<?php echo $users->have_car->EditAttributes() ?>></div>
<div id="dsl_x_have_car" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->have_car->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->have_car->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;

		// Note: No spacing within the LABEL tag
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<label class="radio"><input type="radio" data-field="x_have_car" name="x_have_car" id="x_have_car_<?php echo $rowcntwrk ?>" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $users->have_car->EditAttributes() ?>><?php echo $arwrk[$rowcntwrk][1] ?></label>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
<?php
	}
}
?>
</div>
</span>
<?php echo $users->have_car->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->car_number->Visible) { // car_number ?>
	<tr id="r_car_number">
		<td><span id="elh_users_car_number"><?php echo $users->car_number->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->car_number->CellAttributes() ?>>
<span id="el_users_car_number" class="control-group">
<input type="text" data-field="x_car_number" name="x_car_number" id="x_car_number" size="30" maxlength="255" placeholder="<?php echo $users->car_number->PlaceHolder ?>" value="<?php echo $users->car_number->EditValue ?>"<?php echo $users->car_number->EditAttributes() ?>>
</span>
<?php echo $users->car_number->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->car_model->Visible) { // car_model ?>
	<tr id="r_car_model">
		<td><span id="elh_users_car_model"><?php echo $users->car_model->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->car_model->CellAttributes() ?>>
<span id="el_users_car_model" class="control-group">
<input type="text" data-field="x_car_model" name="x_car_model" id="x_car_model" size="30" maxlength="255" placeholder="<?php echo $users->car_model->PlaceHolder ?>" value="<?php echo $users->car_model->EditValue ?>"<?php echo $users->car_model->EditAttributes() ?>>
</span>
<?php echo $users->car_model->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->car_color->Visible) { // car_color ?>
	<tr id="r_car_color">
		<td><span id="elh_users_car_color"><?php echo $users->car_color->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->car_color->CellAttributes() ?>>
<span id="el_users_car_color" class="control-group">
<input type="text" data-field="x_car_color" name="x_car_color" id="x_car_color" size="30" maxlength="255" placeholder="<?php echo $users->car_color->PlaceHolder ?>" value="<?php echo $users->car_color->EditValue ?>"<?php echo $users->car_color->EditAttributes() ?>>
</span>
<?php echo $users->car_color->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->type->Visible) { // type ?>
	<tr id="r_type">
		<td><span id="elh_users_type"><?php echo $users->type->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->type->CellAttributes() ?>>
<span id="el_users_type" class="control-group">
<div id="tp_x_type" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_type" id="x_type" value="{value}"<?php echo $users->type->EditAttributes() ?>></div>
<div id="dsl_x_type" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->type->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->type->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;

		// Note: No spacing within the LABEL tag
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<label class="radio"><input type="radio" data-field="x_type" name="x_type" id="x_type_<?php echo $rowcntwrk ?>" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $users->type->EditAttributes() ?>><?php echo $arwrk[$rowcntwrk][1] ?></label>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
<?php
	}
}
?>
</div>
</span>
<?php echo $users->type->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->img->Visible) { // img ?>
	<tr id="r_img">
		<td><span id="elh_users_img"><?php echo $users->img->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $users->img->CellAttributes() ?>>
<span id="el_users_img" class="control-group">
<input type="text" data-field="x_img" name="x_img" id="x_img" size="30" maxlength="255" placeholder="<?php echo $users->img->PlaceHolder ?>" value="<?php echo $users->img->EditValue ?>"<?php echo $users->img->EditAttributes() ?>>
</span>
<?php echo $users->img->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($users->remember_token->Visible) { // remember_token ?>
	<tr id="r_remember_token">
		<td><span id="elh_users_remember_token"><?php echo $users->remember_token->FldCaption() ?></span></td>
		<td<?php echo $users->remember_token->CellAttributes() ?>>
<span id="el_users_remember_token" class="control-group">
<input type="text" data-field="x_remember_token" name="x_remember_token" id="x_remember_token" size="30" maxlength="100" placeholder="<?php echo $users->remember_token->PlaceHolder ?>" value="<?php echo $users->remember_token->EditValue ?>"<?php echo $users->remember_token->EditAttributes() ?>>
</span>
<?php echo $users->remember_token->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("AddBtn") ?></button>
</form>
<script type="text/javascript">
fusersadd.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$users_add->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$users_add->Page_Terminate();
?>
