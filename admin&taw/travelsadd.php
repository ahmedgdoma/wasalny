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

$travels_add = NULL; // Initialize page object first

class ctravels_add extends ctravels {

	// Page ID
	var $PageID = 'add';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'travels';

	// Page object name
	var $PageObjName = 'travels_add';

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
			define("EW_PAGE_ID", 'add', TRUE);

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
					$this->Page_Terminate("travelslist.php"); // No matching record, return to list
				}
				break;
			case "A": // Add new record
				$this->SendEmail = TRUE; // Send email on add success
				if ($this->AddRow($this->OldRecordset)) { // Add successful
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("AddSuccess")); // Set up success message
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "travelsview.php")
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
		$this->user_id->CurrentValue = NULL;
		$this->user_id->OldValue = $this->user_id->CurrentValue;
		$this->travel_name->CurrentValue = NULL;
		$this->travel_name->OldValue = $this->travel_name->CurrentValue;
		$this->start_point->CurrentValue = NULL;
		$this->start_point->OldValue = $this->start_point->CurrentValue;
		$this->end_point->CurrentValue = NULL;
		$this->end_point->OldValue = $this->end_point->CurrentValue;
		$this->capacity->CurrentValue = NULL;
		$this->capacity->OldValue = $this->capacity->CurrentValue;
		$this->start_time->CurrentValue = NULL;
		$this->start_time->OldValue = $this->start_time->CurrentValue;
		$this->passenger_gender->CurrentValue = NULL;
		$this->passenger_gender->OldValue = $this->passenger_gender->CurrentValue;
		$this->status->CurrentValue = NULL;
		$this->status->OldValue = $this->status->CurrentValue;
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->user_id->FldIsDetailKey) {
			$this->user_id->setFormValue($objForm->GetValue("x_user_id"));
		}
		if (!$this->travel_name->FldIsDetailKey) {
			$this->travel_name->setFormValue($objForm->GetValue("x_travel_name"));
		}
		if (!$this->start_point->FldIsDetailKey) {
			$this->start_point->setFormValue($objForm->GetValue("x_start_point"));
		}
		if (!$this->end_point->FldIsDetailKey) {
			$this->end_point->setFormValue($objForm->GetValue("x_end_point"));
		}
		if (!$this->capacity->FldIsDetailKey) {
			$this->capacity->setFormValue($objForm->GetValue("x_capacity"));
		}
		if (!$this->start_time->FldIsDetailKey) {
			$this->start_time->setFormValue($objForm->GetValue("x_start_time"));
			$this->start_time->CurrentValue = ew_UnFormatDateTime($this->start_time->CurrentValue, 9);
		}
		if (!$this->passenger_gender->FldIsDetailKey) {
			$this->passenger_gender->setFormValue($objForm->GetValue("x_passenger_gender"));
		}
		if (!$this->status->FldIsDetailKey) {
			$this->status->setFormValue($objForm->GetValue("x_status"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadOldRecord();
		$this->user_id->CurrentValue = $this->user_id->FormValue;
		$this->travel_name->CurrentValue = $this->travel_name->FormValue;
		$this->start_point->CurrentValue = $this->start_point->FormValue;
		$this->end_point->CurrentValue = $this->end_point->FormValue;
		$this->capacity->CurrentValue = $this->capacity->FormValue;
		$this->start_time->CurrentValue = $this->start_time->FormValue;
		$this->start_time->CurrentValue = ew_UnFormatDateTime($this->start_time->CurrentValue, 9);
		$this->passenger_gender->CurrentValue = $this->passenger_gender->FormValue;
		$this->status->CurrentValue = $this->status->FormValue;
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
		} elseif ($this->RowType == EW_ROWTYPE_ADD) { // Add row

			// user_id
			$this->user_id->EditCustomAttributes = "";
			$sFilterWrk = "";
			$sSqlWrk = "SELECT `id`, `username` AS `DispFld`, '' AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld`, '' AS `SelectFilterFld`, '' AS `SelectFilterFld2`, '' AS `SelectFilterFld3`, '' AS `SelectFilterFld4` FROM `users`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->user_id, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
			$rswrk = $conn->Execute($sSqlWrk);
			$arwrk = ($rswrk) ? $rswrk->GetRows() : array();
			if ($rswrk) $rswrk->Close();
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect"), "", "", "", "", "", "", ""));
			$this->user_id->EditValue = $arwrk;

			// travel_name
			$this->travel_name->EditCustomAttributes = "";
			$this->travel_name->EditValue = ew_HtmlEncode($this->travel_name->CurrentValue);
			$this->travel_name->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->travel_name->FldCaption()));

			// start_point
			$this->start_point->EditCustomAttributes = "";
			$this->start_point->EditValue = ew_HtmlEncode($this->start_point->CurrentValue);
			$this->start_point->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->start_point->FldCaption()));

			// end_point
			$this->end_point->EditCustomAttributes = "";
			$this->end_point->EditValue = ew_HtmlEncode($this->end_point->CurrentValue);
			$this->end_point->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->end_point->FldCaption()));

			// capacity
			$this->capacity->EditCustomAttributes = "";
			$this->capacity->EditValue = ew_HtmlEncode($this->capacity->CurrentValue);
			$this->capacity->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->capacity->FldCaption()));

			// start_time
			$this->start_time->EditCustomAttributes = "";
			$this->start_time->EditValue = ew_HtmlEncode(ew_FormatDateTime($this->start_time->CurrentValue, 9));
			$this->start_time->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->start_time->FldCaption()));

			// passenger_gender
			$this->passenger_gender->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->passenger_gender->FldTagValue(1), $this->passenger_gender->FldTagCaption(1) <> "" ? $this->passenger_gender->FldTagCaption(1) : $this->passenger_gender->FldTagValue(1));
			$arwrk[] = array($this->passenger_gender->FldTagValue(2), $this->passenger_gender->FldTagCaption(2) <> "" ? $this->passenger_gender->FldTagCaption(2) : $this->passenger_gender->FldTagValue(2));
			$arwrk[] = array($this->passenger_gender->FldTagValue(3), $this->passenger_gender->FldTagCaption(3) <> "" ? $this->passenger_gender->FldTagCaption(3) : $this->passenger_gender->FldTagValue(3));
			$this->passenger_gender->EditValue = $arwrk;

			// status
			$this->status->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->status->FldTagValue(1), $this->status->FldTagCaption(1) <> "" ? $this->status->FldTagCaption(1) : $this->status->FldTagValue(1));
			$arwrk[] = array($this->status->FldTagValue(2), $this->status->FldTagCaption(2) <> "" ? $this->status->FldTagCaption(2) : $this->status->FldTagValue(2));
			$arwrk[] = array($this->status->FldTagValue(3), $this->status->FldTagCaption(3) <> "" ? $this->status->FldTagCaption(3) : $this->status->FldTagValue(3));
			$this->status->EditValue = $arwrk;

			// Edit refer script
			// user_id

			$this->user_id->HrefValue = "";

			// travel_name
			$this->travel_name->HrefValue = "";

			// start_point
			$this->start_point->HrefValue = "";

			// end_point
			$this->end_point->HrefValue = "";

			// capacity
			$this->capacity->HrefValue = "";

			// start_time
			$this->start_time->HrefValue = "";

			// passenger_gender
			$this->passenger_gender->HrefValue = "";

			// status
			$this->status->HrefValue = "";
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
		if (!$this->user_id->FldIsDetailKey && !is_null($this->user_id->FormValue) && $this->user_id->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->user_id->FldCaption());
		}
		if (!$this->travel_name->FldIsDetailKey && !is_null($this->travel_name->FormValue) && $this->travel_name->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->travel_name->FldCaption());
		}
		if (!$this->start_point->FldIsDetailKey && !is_null($this->start_point->FormValue) && $this->start_point->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->start_point->FldCaption());
		}
		if (!$this->end_point->FldIsDetailKey && !is_null($this->end_point->FormValue) && $this->end_point->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->end_point->FldCaption());
		}
		if (!$this->capacity->FldIsDetailKey && !is_null($this->capacity->FormValue) && $this->capacity->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->capacity->FldCaption());
		}
		if (!$this->start_time->FldIsDetailKey && !is_null($this->start_time->FormValue) && $this->start_time->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->start_time->FldCaption());
		}
		if (!ew_CheckDate($this->start_time->FormValue)) {
			ew_AddMessage($gsFormError, $this->start_time->FldErrMsg());
		}
		if ($this->passenger_gender->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->passenger_gender->FldCaption());
		}
		if ($this->status->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->status->FldCaption());
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

		// user_id
		$this->user_id->SetDbValueDef($rsnew, $this->user_id->CurrentValue, 0, FALSE);

		// travel_name
		$this->travel_name->SetDbValueDef($rsnew, $this->travel_name->CurrentValue, "", FALSE);

		// start_point
		$this->start_point->SetDbValueDef($rsnew, $this->start_point->CurrentValue, "", FALSE);

		// end_point
		$this->end_point->SetDbValueDef($rsnew, $this->end_point->CurrentValue, "", FALSE);

		// capacity
		$this->capacity->SetDbValueDef($rsnew, $this->capacity->CurrentValue, "", FALSE);

		// start_time
		$this->start_time->SetDbValueDef($rsnew, ew_UnFormatDateTime($this->start_time->CurrentValue, 9), ew_CurrentDate(), FALSE);

		// passenger_gender
		$this->passenger_gender->SetDbValueDef($rsnew, $this->passenger_gender->CurrentValue, "", FALSE);

		// status
		$this->status->SetDbValueDef($rsnew, $this->status->CurrentValue, "", FALSE);

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
		$Breadcrumb->Add("list", $this->TableVar, "travelslist.php", $this->TableVar, TRUE);
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
if (!isset($travels_add)) $travels_add = new ctravels_add();

// Page init
$travels_add->Page_Init();

// Page main
$travels_add->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$travels_add->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var travels_add = new ew_Page("travels_add");
travels_add.PageID = "add"; // Page ID
var EW_PAGE_ID = travels_add.PageID; // For backward compatibility

// Form object
var ftravelsadd = new ew_Form("ftravelsadd");

// Validate form
ftravelsadd.Validate = function() {
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
			elm = this.GetElements("x" + infix + "_user_id");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->user_id->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_travel_name");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->travel_name->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_start_point");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->start_point->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_end_point");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->end_point->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_capacity");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->capacity->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_start_time");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->start_time->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_start_time");
			if (elm && !ew_CheckDate(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($travels->start_time->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_passenger_gender");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->passenger_gender->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_status");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($travels->status->FldCaption()) ?>");

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
ftravelsadd.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
ftravelsadd.ValidateRequired = true;
<?php } else { ?>
ftravelsadd.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
ftravelsadd.Lists["x_user_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_username","","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php $travels_add->ShowPageHeader(); ?>
<?php
$travels_add->ShowMessage();
?>
<form name="ftravelsadd" id="ftravelsadd" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="travels">
<input type="hidden" name="a_add" id="a_add" value="A">
<table class="ewGrid"><tr><td>
<table id="tbl_travelsadd" class="table table-bordered table-striped">
<?php if ($travels->user_id->Visible) { // user_id ?>
	<tr id="r_user_id">
		<td><span id="elh_travels_user_id"><?php echo $travels->user_id->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->user_id->CellAttributes() ?>>
<span id="el_travels_user_id" class="control-group">
<select data-field="x_user_id" id="x_user_id" name="x_user_id"<?php echo $travels->user_id->EditAttributes() ?>>
<?php
if (is_array($travels->user_id->EditValue)) {
	$arwrk = $travels->user_id->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($travels->user_id->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
	}
}
?>
</select>
<script type="text/javascript">
ftravelsadd.Lists["x_user_id"].Options = <?php echo (is_array($travels->user_id->EditValue)) ? ew_ArrayToJson($travels->user_id->EditValue, 1) : "[]" ?>;
</script>
</span>
<?php echo $travels->user_id->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->travel_name->Visible) { // travel_name ?>
	<tr id="r_travel_name">
		<td><span id="elh_travels_travel_name"><?php echo $travels->travel_name->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->travel_name->CellAttributes() ?>>
<span id="el_travels_travel_name" class="control-group">
<input type="text" data-field="x_travel_name" name="x_travel_name" id="x_travel_name" size="30" maxlength="255" placeholder="<?php echo $travels->travel_name->PlaceHolder ?>" value="<?php echo $travels->travel_name->EditValue ?>"<?php echo $travels->travel_name->EditAttributes() ?>>
</span>
<?php echo $travels->travel_name->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->start_point->Visible) { // start_point ?>
	<tr id="r_start_point">
		<td><span id="elh_travels_start_point"><?php echo $travels->start_point->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->start_point->CellAttributes() ?>>
<span id="el_travels_start_point" class="control-group">
<input type="text" data-field="x_start_point" name="x_start_point" id="x_start_point" size="30" maxlength="255" placeholder="<?php echo $travels->start_point->PlaceHolder ?>" value="<?php echo $travels->start_point->EditValue ?>"<?php echo $travels->start_point->EditAttributes() ?>>
</span>
<?php echo $travels->start_point->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->end_point->Visible) { // end_point ?>
	<tr id="r_end_point">
		<td><span id="elh_travels_end_point"><?php echo $travels->end_point->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->end_point->CellAttributes() ?>>
<span id="el_travels_end_point" class="control-group">
<input type="text" data-field="x_end_point" name="x_end_point" id="x_end_point" size="30" maxlength="255" placeholder="<?php echo $travels->end_point->PlaceHolder ?>" value="<?php echo $travels->end_point->EditValue ?>"<?php echo $travels->end_point->EditAttributes() ?>>
</span>
<?php echo $travels->end_point->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->capacity->Visible) { // capacity ?>
	<tr id="r_capacity">
		<td><span id="elh_travels_capacity"><?php echo $travels->capacity->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->capacity->CellAttributes() ?>>
<span id="el_travels_capacity" class="control-group">
<input type="text" data-field="x_capacity" name="x_capacity" id="x_capacity" size="30" maxlength="255" placeholder="<?php echo $travels->capacity->PlaceHolder ?>" value="<?php echo $travels->capacity->EditValue ?>"<?php echo $travels->capacity->EditAttributes() ?>>
</span>
<?php echo $travels->capacity->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->start_time->Visible) { // start_time ?>
	<tr id="r_start_time">
		<td><span id="elh_travels_start_time"><?php echo $travels->start_time->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->start_time->CellAttributes() ?>>
<span id="el_travels_start_time" class="control-group">
<input type="text" data-field="x_start_time" name="x_start_time" id="x_start_time" placeholder="<?php echo $travels->start_time->PlaceHolder ?>" value="<?php echo $travels->start_time->EditValue ?>"<?php echo $travels->start_time->EditAttributes() ?>>
</span>
<?php echo $travels->start_time->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
	<tr id="r_passenger_gender">
		<td><span id="elh_travels_passenger_gender"><?php echo $travels->passenger_gender->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->passenger_gender->CellAttributes() ?>>
<span id="el_travels_passenger_gender" class="control-group">
<div id="tp_x_passenger_gender" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_passenger_gender" id="x_passenger_gender" value="{value}"<?php echo $travels->passenger_gender->EditAttributes() ?>></div>
<div id="dsl_x_passenger_gender" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $travels->passenger_gender->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($travels->passenger_gender->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;

		// Note: No spacing within the LABEL tag
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<label class="radio"><input type="radio" data-field="x_passenger_gender" name="x_passenger_gender" id="x_passenger_gender_<?php echo $rowcntwrk ?>" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $travels->passenger_gender->EditAttributes() ?>><?php echo $arwrk[$rowcntwrk][1] ?></label>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
<?php
	}
}
?>
</div>
</span>
<?php echo $travels->passenger_gender->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($travels->status->Visible) { // status ?>
	<tr id="r_status">
		<td><span id="elh_travels_status"><?php echo $travels->status->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $travels->status->CellAttributes() ?>>
<span id="el_travels_status" class="control-group">
<div id="tp_x_status" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_status" id="x_status" value="{value}"<?php echo $travels->status->EditAttributes() ?>></div>
<div id="dsl_x_status" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $travels->status->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($travels->status->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;

		// Note: No spacing within the LABEL tag
?>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 1) ?>
<label class="radio"><input type="radio" data-field="x_status" name="x_status" id="x_status_<?php echo $rowcntwrk ?>" value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?><?php echo $travels->status->EditAttributes() ?>><?php echo $arwrk[$rowcntwrk][1] ?></label>
<?php echo ew_RepeatColumnTable($rowswrk, $rowcntwrk, 5, 2) ?>
<?php
	}
}
?>
</div>
</span>
<?php echo $travels->status->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("AddBtn") ?></button>
</form>
<script type="text/javascript">
ftravelsadd.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$travels_add->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$travels_add->Page_Terminate();
?>
