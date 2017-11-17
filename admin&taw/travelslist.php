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

$travels_list = NULL; // Initialize page object first

class ctravels_list extends ctravels {

	// Page ID
	var $PageID = 'list';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'travels';

	// Page object name
	var $PageObjName = 'travels_list';

	// Grid form hidden field names
	var $FormName = 'ftravelslist';
	var $FormActionName = 'k_action';
	var $FormKeyName = 'k_key';
	var $FormOldKeyName = 'k_oldkey';
	var $FormBlankRowName = 'k_blankrow';
	var $FormKeyCountName = 'key_count';

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

		// Table object (travels)
		if (!isset($GLOBALS["travels"]) || get_class($GLOBALS["travels"]) == "ctravels") {
			$GLOBALS["travels"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["travels"];
		}

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportHtmlUrl = $this->PageUrl() . "export=html";
		$this->ExportXmlUrl = $this->PageUrl() . "export=xml";
		$this->ExportCsvUrl = $this->PageUrl() . "export=csv";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";
		$this->AddUrl = "travelsadd.php";
		$this->InlineAddUrl = $this->PageUrl() . "a=add";
		$this->GridAddUrl = $this->PageUrl() . "a=gridadd";
		$this->GridEditUrl = $this->PageUrl() . "a=gridedit";
		$this->MultiDeleteUrl = "travelsdelete.php";
		$this->MultiUpdateUrl = "travelsupdate.php";

		// Table object (users)
		if (!isset($GLOBALS['users'])) $GLOBALS['users'] = new cusers();

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'list', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'travels', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();

		// List options
		$this->ListOptions = new cListOptions();
		$this->ListOptions->TableVar = $this->TableVar;

		// Export options
		$this->ExportOptions = new cListOptions();
		$this->ExportOptions->Tag = "div";
		$this->ExportOptions->TagClassName = "ewExportOption";

		// Other options
		$this->OtherOptions['addedit'] = new cListOptions();
		$this->OtherOptions['addedit']->Tag = "div";
		$this->OtherOptions['addedit']->TagClassName = "ewAddEditOption";
		$this->OtherOptions['detail'] = new cListOptions();
		$this->OtherOptions['detail']->Tag = "div";
		$this->OtherOptions['detail']->TagClassName = "ewDetailOption";
		$this->OtherOptions['action'] = new cListOptions();
		$this->OtherOptions['action']->Tag = "div";
		$this->OtherOptions['action']->TagClassName = "ewActionOption";
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

		// Get grid add count
		$gridaddcnt = @$_GET[EW_TABLE_GRID_ADD_ROW_COUNT];
		if (is_numeric($gridaddcnt) && $gridaddcnt > 0)
			$this->GridAddRowCount = $gridaddcnt;

		// Set up list options
		$this->SetupListOptions();
		$this->id->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();

		// Setup other options
		$this->SetupOtherOptions();

		// Set "checkbox" visible
		if (count($this->CustomActions) > 0)
			$this->ListOptions->Items["checkbox"]->Visible = TRUE;
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

	// Class variables
	var $ListOptions; // List options
	var $ExportOptions; // Export options
	var $OtherOptions = array(); // Other options
	var $DisplayRecs = 20;
	var $StartRec;
	var $StopRec;
	var $TotalRecs = 0;
	var $RecRange = 10;
	var $Pager;
	var $SearchWhere = ""; // Search WHERE clause
	var $RecCnt = 0; // Record count
	var $EditRowCnt;
	var $StartRowCnt = 1;
	var $RowCnt = 0;
	var $Attrs = array(); // Row attributes and cell attributes
	var $RowIndex = 0; // Row index
	var $KeyCount = 0; // Key count
	var $RowAction = ""; // Row action
	var $RowOldKey = ""; // Row old key (for copy)
	var $RecPerRow = 0;
	var $ColCnt = 0;
	var $DbMasterFilter = ""; // Master filter
	var $DbDetailFilter = ""; // Detail filter
	var $MasterRecordExists;	
	var $MultiSelectKey;
	var $Command;
	var $RestoreSearch = FALSE;
	var $Recordset;
	var $OldRecordset;

	//
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError, $gsSearchError, $Security;

		// Search filters
		$sSrchAdvanced = ""; // Advanced search filter
		$sSrchBasic = ""; // Basic search filter
		$sFilter = "";

		// Get command
		$this->Command = strtolower(@$_GET["cmd"]);
		if ($this->IsPageRequest()) { // Validate request

			// Process custom action first
			$this->ProcessCustomAction();

			// Handle reset command
			$this->ResetCmd();

			// Set up Breadcrumb
			$this->SetupBreadcrumb();

			// Hide list options
			if ($this->Export <> "") {
				$this->ListOptions->HideAllOptions(array("sequence"));
				$this->ListOptions->UseDropDownButton = FALSE; // Disable drop down button
				$this->ListOptions->UseButtonGroup = FALSE; // Disable button group
			} elseif ($this->CurrentAction == "gridadd" || $this->CurrentAction == "gridedit") {
				$this->ListOptions->HideAllOptions();
				$this->ListOptions->UseDropDownButton = FALSE; // Disable drop down button
				$this->ListOptions->UseButtonGroup = FALSE; // Disable button group
			}

			// Hide export options
			if ($this->Export <> "" || $this->CurrentAction <> "")
				$this->ExportOptions->HideAllOptions();

			// Hide other options
			if ($this->Export <> "") {
				foreach ($this->OtherOptions as &$option)
					$option->HideAllOptions();
			}

			// Get basic search values
			$this->LoadBasicSearchValues();

			// Get and validate search values for advanced search
			$this->LoadSearchValues(); // Get search values
			if (!$this->ValidateSearch())
				$this->setFailureMessage($gsSearchError);

			// Restore search parms from Session if not searching / reset
			if ($this->Command <> "search" && $this->Command <> "reset" && $this->Command <> "resetall" && $this->CheckSearchParms())
				$this->RestoreSearchParms();

			// Call Recordset SearchValidated event
			$this->Recordset_SearchValidated();

			// Set up sorting order
			$this->SetUpSortOrder();

			// Get basic search criteria
			if ($gsSearchError == "")
				$sSrchBasic = $this->BasicSearchWhere();

			// Get search criteria for advanced search
			if ($gsSearchError == "")
				$sSrchAdvanced = $this->AdvancedSearchWhere();
		}

		// Restore display records
		if ($this->getRecordsPerPage() <> "") {
			$this->DisplayRecs = $this->getRecordsPerPage(); // Restore from Session
		} else {
			$this->DisplayRecs = 20; // Load default
		}

		// Load Sorting Order
		$this->LoadSortOrder();

		// Load search default if no existing search criteria
		if (!$this->CheckSearchParms()) {

			// Load basic search from default
			$this->BasicSearch->LoadDefault();
			if ($this->BasicSearch->Keyword != "")
				$sSrchBasic = $this->BasicSearchWhere();

			// Load advanced search from default
			if ($this->LoadAdvancedSearchDefault()) {
				$sSrchAdvanced = $this->AdvancedSearchWhere();
			}
		}

		// Build search criteria
		ew_AddFilter($this->SearchWhere, $sSrchAdvanced);
		ew_AddFilter($this->SearchWhere, $sSrchBasic);

		// Call Recordset_Searching event
		$this->Recordset_Searching($this->SearchWhere);

		// Save search criteria
		if ($this->Command == "search" && !$this->RestoreSearch) {
			$this->setSearchWhere($this->SearchWhere); // Save to Session
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} else {
			$this->SearchWhere = $this->getSearchWhere();
		}

		// Build filter
		$sFilter = "";
		ew_AddFilter($sFilter, $this->DbDetailFilter);
		ew_AddFilter($sFilter, $this->SearchWhere);

		// Set up filter in session
		$this->setSessionWhere($sFilter);
		$this->CurrentFilter = "";
	}

	// Build filter for all keys
	function BuildKeyFilter() {
		global $objForm;
		$sWrkFilter = "";

		// Update row index and get row key
		$rowindex = 1;
		$objForm->Index = $rowindex;
		$sThisKey = strval($objForm->GetValue("k_key"));
		while ($sThisKey <> "") {
			if ($this->SetupKeyValues($sThisKey)) {
				$sFilter = $this->KeyFilter();
				if ($sWrkFilter <> "") $sWrkFilter .= " OR ";
				$sWrkFilter .= $sFilter;
			} else {
				$sWrkFilter = "0=1";
				break;
			}

			// Update row index and get row key
			$rowindex++; // Next row
			$objForm->Index = $rowindex;
			$sThisKey = strval($objForm->GetValue("k_key"));
		}
		return $sWrkFilter;
	}

	// Set up key values
	function SetupKeyValues($key) {
		$arrKeyFlds = explode($GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"], $key);
		if (count($arrKeyFlds) >= 1) {
			$this->id->setFormValue($arrKeyFlds[0]);
			if (!is_numeric($this->id->FormValue))
				return FALSE;
		}
		return TRUE;
	}

	// Advanced search WHERE clause based on QueryString
	function AdvancedSearchWhere() {
		global $Security;
		$sWhere = "";
		$this->BuildSearchSql($sWhere, $this->id, FALSE); // id
		$this->BuildSearchSql($sWhere, $this->user_id, FALSE); // user_id
		$this->BuildSearchSql($sWhere, $this->travel_name, FALSE); // travel_name
		$this->BuildSearchSql($sWhere, $this->start_point, FALSE); // start_point
		$this->BuildSearchSql($sWhere, $this->end_point, FALSE); // end_point
		$this->BuildSearchSql($sWhere, $this->capacity, FALSE); // capacity
		$this->BuildSearchSql($sWhere, $this->start_time, FALSE); // start_time
		$this->BuildSearchSql($sWhere, $this->passenger_gender, FALSE); // passenger_gender
		$this->BuildSearchSql($sWhere, $this->status, FALSE); // status
		$this->BuildSearchSql($sWhere, $this->created_at, FALSE); // created_at
		$this->BuildSearchSql($sWhere, $this->updated_at, FALSE); // updated_at

		// Set up search parm
		if ($sWhere <> "") {
			$this->Command = "search";
		}
		if ($this->Command == "search") {
			$this->id->AdvancedSearch->Save(); // id
			$this->user_id->AdvancedSearch->Save(); // user_id
			$this->travel_name->AdvancedSearch->Save(); // travel_name
			$this->start_point->AdvancedSearch->Save(); // start_point
			$this->end_point->AdvancedSearch->Save(); // end_point
			$this->capacity->AdvancedSearch->Save(); // capacity
			$this->start_time->AdvancedSearch->Save(); // start_time
			$this->passenger_gender->AdvancedSearch->Save(); // passenger_gender
			$this->status->AdvancedSearch->Save(); // status
			$this->created_at->AdvancedSearch->Save(); // created_at
			$this->updated_at->AdvancedSearch->Save(); // updated_at
		}
		return $sWhere;
	}

	// Build search SQL
	function BuildSearchSql(&$Where, &$Fld, $MultiValue) {
		$FldParm = substr($Fld->FldVar, 2);
		$FldVal = $Fld->AdvancedSearch->SearchValue; // @$_GET["x_$FldParm"]
		$FldOpr = $Fld->AdvancedSearch->SearchOperator; // @$_GET["z_$FldParm"]
		$FldCond = $Fld->AdvancedSearch->SearchCondition; // @$_GET["v_$FldParm"]
		$FldVal2 = $Fld->AdvancedSearch->SearchValue2; // @$_GET["y_$FldParm"]
		$FldOpr2 = $Fld->AdvancedSearch->SearchOperator2; // @$_GET["w_$FldParm"]
		$sWrk = "";

		//$FldVal = ew_StripSlashes($FldVal);
		if (is_array($FldVal)) $FldVal = implode(",", $FldVal);

		//$FldVal2 = ew_StripSlashes($FldVal2);
		if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
		$FldOpr = strtoupper(trim($FldOpr));
		if ($FldOpr == "") $FldOpr = "=";
		$FldOpr2 = strtoupper(trim($FldOpr2));
		if ($FldOpr2 == "") $FldOpr2 = "=";
		if (EW_SEARCH_MULTI_VALUE_OPTION == 1 || $FldOpr <> "LIKE" ||
			($FldOpr2 <> "LIKE" && $FldVal2 <> ""))
			$MultiValue = FALSE;
		if ($MultiValue) {
			$sWrk1 = ($FldVal <> "") ? ew_GetMultiSearchSql($Fld, $FldOpr, $FldVal) : ""; // Field value 1
			$sWrk2 = ($FldVal2 <> "") ? ew_GetMultiSearchSql($Fld, $FldOpr2, $FldVal2) : ""; // Field value 2
			$sWrk = $sWrk1; // Build final SQL
			if ($sWrk2 <> "")
				$sWrk = ($sWrk <> "") ? "($sWrk) $FldCond ($sWrk2)" : $sWrk2;
		} else {
			$FldVal = $this->ConvertSearchValue($Fld, $FldVal);
			$FldVal2 = $this->ConvertSearchValue($Fld, $FldVal2);
			$sWrk = ew_GetSearchSql($Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2);
		}
		ew_AddFilter($Where, $sWrk);
	}

	// Convert search value
	function ConvertSearchValue(&$Fld, $FldVal) {
		if ($FldVal == EW_NULL_VALUE || $FldVal == EW_NOT_NULL_VALUE)
			return $FldVal;
		$Value = $FldVal;
		if ($Fld->FldDataType == EW_DATATYPE_BOOLEAN) {
			if ($FldVal <> "") $Value = ($FldVal == "1" || strtolower(strval($FldVal)) == "y" || strtolower(strval($FldVal)) == "t") ? $Fld->TrueValue : $Fld->FalseValue;
		} elseif ($Fld->FldDataType == EW_DATATYPE_DATE) {
			if ($FldVal <> "") $Value = ew_UnFormatDateTime($FldVal, $Fld->FldDateTimeFormat);
		}
		return $Value;
	}

	// Return basic search SQL
	function BasicSearchSQL($Keyword) {
		$sKeyword = ew_AdjustSql($Keyword);
		$sWhere = "";
		$this->BuildBasicSearchSQL($sWhere, $this->travel_name, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->start_point, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->end_point, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->capacity, $Keyword);
		return $sWhere;
	}

	// Build basic search SQL
	function BuildBasicSearchSql(&$Where, &$Fld, $Keyword) {
		if ($Keyword == EW_NULL_VALUE) {
			$sWrk = $Fld->FldExpression . " IS NULL";
		} elseif ($Keyword == EW_NOT_NULL_VALUE) {
			$sWrk = $Fld->FldExpression . " IS NOT NULL";
		} else {
			$sFldExpression = ($Fld->FldVirtualExpression <> $Fld->FldExpression) ? $Fld->FldVirtualExpression : $Fld->FldBasicSearchExpression;
			$sWrk = $sFldExpression . ew_Like(ew_QuotedValue("%" . $Keyword . "%", EW_DATATYPE_STRING));
		}
		if ($Where <> "") $Where .= " OR ";
		$Where .= $sWrk;
	}

	// Return basic search WHERE clause based on search keyword and type
	function BasicSearchWhere() {
		global $Security;
		$sSearchStr = "";
		$sSearchKeyword = $this->BasicSearch->Keyword;
		$sSearchType = $this->BasicSearch->Type;
		if ($sSearchKeyword <> "") {
			$sSearch = trim($sSearchKeyword);
			if ($sSearchType <> "=") {
				while (strpos($sSearch, "  ") !== FALSE)
					$sSearch = str_replace("  ", " ", $sSearch);
				$arKeyword = explode(" ", trim($sSearch));
				foreach ($arKeyword as $sKeyword) {
					if ($sSearchStr <> "") $sSearchStr .= " " . $sSearchType . " ";
					$sSearchStr .= "(" . $this->BasicSearchSQL($sKeyword) . ")";
				}
			} else {
				$sSearchStr = $this->BasicSearchSQL($sSearch);
			}
			$this->Command = "search";
		}
		if ($this->Command == "search") {
			$this->BasicSearch->setKeyword($sSearchKeyword);
			$this->BasicSearch->setType($sSearchType);
		}
		return $sSearchStr;
	}

	// Check if search parm exists
	function CheckSearchParms() {

		// Check basic search
		if ($this->BasicSearch->IssetSession())
			return TRUE;
		if ($this->id->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->user_id->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->travel_name->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->start_point->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->end_point->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->capacity->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->start_time->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->passenger_gender->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->status->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->created_at->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->updated_at->AdvancedSearch->IssetSession())
			return TRUE;
		return FALSE;
	}

	// Clear all search parameters
	function ResetSearchParms() {

		// Clear search WHERE clause
		$this->SearchWhere = "";
		$this->setSearchWhere($this->SearchWhere);

		// Clear basic search parameters
		$this->ResetBasicSearchParms();

		// Clear advanced search parameters
		$this->ResetAdvancedSearchParms();
	}

	// Load advanced search default values
	function LoadAdvancedSearchDefault() {
		return FALSE;
	}

	// Clear all basic search parameters
	function ResetBasicSearchParms() {
		$this->BasicSearch->UnsetSession();
	}

	// Clear all advanced search parameters
	function ResetAdvancedSearchParms() {
		$this->id->AdvancedSearch->UnsetSession();
		$this->user_id->AdvancedSearch->UnsetSession();
		$this->travel_name->AdvancedSearch->UnsetSession();
		$this->start_point->AdvancedSearch->UnsetSession();
		$this->end_point->AdvancedSearch->UnsetSession();
		$this->capacity->AdvancedSearch->UnsetSession();
		$this->start_time->AdvancedSearch->UnsetSession();
		$this->passenger_gender->AdvancedSearch->UnsetSession();
		$this->status->AdvancedSearch->UnsetSession();
		$this->created_at->AdvancedSearch->UnsetSession();
		$this->updated_at->AdvancedSearch->UnsetSession();
	}

	// Restore all search parameters
	function RestoreSearchParms() {
		$this->RestoreSearch = TRUE;

		// Restore basic search values
		$this->BasicSearch->Load();

		// Restore advanced search values
		$this->id->AdvancedSearch->Load();
		$this->user_id->AdvancedSearch->Load();
		$this->travel_name->AdvancedSearch->Load();
		$this->start_point->AdvancedSearch->Load();
		$this->end_point->AdvancedSearch->Load();
		$this->capacity->AdvancedSearch->Load();
		$this->start_time->AdvancedSearch->Load();
		$this->passenger_gender->AdvancedSearch->Load();
		$this->status->AdvancedSearch->Load();
		$this->created_at->AdvancedSearch->Load();
		$this->updated_at->AdvancedSearch->Load();
	}

	// Set up sort parameters
	function SetUpSortOrder() {

		// Check for "order" parameter
		if (@$_GET["order"] <> "") {
			$this->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
			$this->CurrentOrderType = @$_GET["ordertype"];
			$this->UpdateSort($this->id); // id
			$this->UpdateSort($this->user_id); // user_id
			$this->UpdateSort($this->travel_name); // travel_name
			$this->UpdateSort($this->start_point); // start_point
			$this->UpdateSort($this->end_point); // end_point
			$this->UpdateSort($this->capacity); // capacity
			$this->UpdateSort($this->start_time); // start_time
			$this->UpdateSort($this->passenger_gender); // passenger_gender
			$this->UpdateSort($this->status); // status
			$this->UpdateSort($this->created_at); // created_at
			$this->UpdateSort($this->updated_at); // updated_at
			$this->setStartRecordNumber(1); // Reset start position
		}
	}

	// Load sort order parameters
	function LoadSortOrder() {
		$sOrderBy = $this->getSessionOrderBy(); // Get ORDER BY from Session
		if ($sOrderBy == "") {
			if ($this->SqlOrderBy() <> "") {
				$sOrderBy = $this->SqlOrderBy();
				$this->setSessionOrderBy($sOrderBy);
			}
		}
	}

	// Reset command
	// - cmd=reset (Reset search parameters)
	// - cmd=resetall (Reset search and master/detail parameters)
	// - cmd=resetsort (Reset sort parameters)
	function ResetCmd() {

		// Check if reset command
		if (substr($this->Command,0,5) == "reset") {

			// Reset search criteria
			if ($this->Command == "reset" || $this->Command == "resetall")
				$this->ResetSearchParms();

			// Reset sorting order
			if ($this->Command == "resetsort") {
				$sOrderBy = "";
				$this->setSessionOrderBy($sOrderBy);
				$this->id->setSort("");
				$this->user_id->setSort("");
				$this->travel_name->setSort("");
				$this->start_point->setSort("");
				$this->end_point->setSort("");
				$this->capacity->setSort("");
				$this->start_time->setSort("");
				$this->passenger_gender->setSort("");
				$this->status->setSort("");
				$this->created_at->setSort("");
				$this->updated_at->setSort("");
			}

			// Reset start position
			$this->StartRec = 1;
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Set up list options
	function SetupListOptions() {
		global $Security, $Language;

		// Add group option item
		$item = &$this->ListOptions->Add($this->ListOptions->GroupOptionName);
		$item->Body = "";
		$item->OnLeft = FALSE;
		$item->Visible = FALSE;

		// "view"
		$item = &$this->ListOptions->Add("view");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = FALSE;

		// "edit"
		$item = &$this->ListOptions->Add("edit");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = FALSE;

		// "copy"
		$item = &$this->ListOptions->Add("copy");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = FALSE;

		// "delete"
		$item = &$this->ListOptions->Add("delete");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = FALSE;

		// "checkbox"
		$item = &$this->ListOptions->Add("checkbox");
		$item->Visible = FALSE;
		$item->OnLeft = FALSE;
		$item->Header = "<label class=\"checkbox\"><input type=\"checkbox\" name=\"key\" id=\"key\" onclick=\"ew_SelectAllKey(this);\"></label>";
		$item->ShowInDropDown = FALSE;
		$item->ShowInButtonGroup = FALSE;

		// Drop down button for ListOptions
		$this->ListOptions->UseDropDownButton = FALSE;
		$this->ListOptions->DropDownButtonPhrase = $Language->Phrase("ButtonListOptions");
		$this->ListOptions->UseButtonGroup = FALSE;
		$this->ListOptions->ButtonClass = "btn-small"; // Class for button group

		// Call ListOptions_Load event
		$this->ListOptions_Load();
		$item = &$this->ListOptions->GetItem($this->ListOptions->GroupOptionName);
		$item->Visible = $this->ListOptions->GroupOptionVisible();
	}

	// Render list options
	function RenderListOptions() {
		global $Security, $Language, $objForm;
		$this->ListOptions->LoadDefault();

		// "view"
		$oListOpt = &$this->ListOptions->Items["view"];
		if ($Security->IsLoggedIn())
			$oListOpt->Body = "<a class=\"ewRowLink ewView\" data-caption=\"" . ew_HtmlTitle($Language->Phrase("ViewLink")) . "\" href=\"" . ew_HtmlEncode($this->ViewUrl) . "\">" . $Language->Phrase("ViewLink") . "</a>";
		else
			$oListOpt->Body = "";

		// "edit"
		$oListOpt = &$this->ListOptions->Items["edit"];
		if ($Security->IsLoggedIn()) {
			$oListOpt->Body = "<a class=\"ewRowLink ewEdit\" data-caption=\"" . ew_HtmlTitle($Language->Phrase("EditLink")) . "\" href=\"" . ew_HtmlEncode($this->EditUrl) . "\">" . $Language->Phrase("EditLink") . "</a>";
		} else {
			$oListOpt->Body = "";
		}

		// "copy"
		$oListOpt = &$this->ListOptions->Items["copy"];
		if ($Security->IsLoggedIn()) {
			$oListOpt->Body = "<a class=\"ewRowLink ewCopy\" data-caption=\"" . ew_HtmlTitle($Language->Phrase("CopyLink")) . "\" href=\"" . ew_HtmlEncode($this->CopyUrl) . "\">" . $Language->Phrase("CopyLink") . "</a>";
		} else {
			$oListOpt->Body = "";
		}

		// "delete"
		$oListOpt = &$this->ListOptions->Items["delete"];
		if ($Security->IsLoggedIn())
			$oListOpt->Body = "<a class=\"ewRowLink ewDelete\"" . "" . " data-caption=\"" . ew_HtmlTitle($Language->Phrase("DeleteLink")) . "\" href=\"" . ew_HtmlEncode($this->DeleteUrl) . "\">" . $Language->Phrase("DeleteLink") . "</a>";
		else
			$oListOpt->Body = "";

		// "checkbox"
		$oListOpt = &$this->ListOptions->Items["checkbox"];
		$oListOpt->Body = "<label class=\"checkbox\"><input type=\"checkbox\" name=\"key_m[]\" value=\"" . ew_HtmlEncode($this->id->CurrentValue) . "\" onclick='ew_ClickMultiCheckbox(event, this);'></label>";
		$this->RenderListOptionsExt();

		// Call ListOptions_Rendered event
		$this->ListOptions_Rendered();
	}

	// Set up other options
	function SetupOtherOptions() {
		global $Language, $Security;
		$options = &$this->OtherOptions;
		$option = $options["addedit"];

		// Add
		$item = &$option->Add("add");
		$item->Body = "<a class=\"ewAddEdit ewAdd\" href=\"" . ew_HtmlEncode($this->AddUrl) . "\">" . $Language->Phrase("AddLink") . "</a>";
		$item->Visible = ($this->AddUrl <> "" && $Security->IsLoggedIn());
		$option = $options["action"];

		// Set up options default
		foreach ($options as &$option) {
			$option->UseDropDownButton = FALSE;
			$option->UseButtonGroup = TRUE;
			$option->ButtonClass = "btn-small"; // Class for button group
			$item = &$option->Add($option->GroupOptionName);
			$item->Body = "";
			$item->Visible = FALSE;
		}
		$options["addedit"]->DropDownButtonPhrase = $Language->Phrase("ButtonAddEdit");
		$options["detail"]->DropDownButtonPhrase = $Language->Phrase("ButtonDetails");
		$options["action"]->DropDownButtonPhrase = $Language->Phrase("ButtonActions");
	}

	// Render other options
	function RenderOtherOptions() {
		global $Language, $Security;
		$options = &$this->OtherOptions;
			$option = &$options["action"];
			foreach ($this->CustomActions as $action => $name) {

				// Add custom action
				$item = &$option->Add("custom_" . $action);
				$item->Body = "<a class=\"ewAction ewCustomAction\" href=\"\" onclick=\"ew_SubmitSelected(document.ftravelslist, '" . ew_CurrentUrl() . "', null, '" . $action . "');return false;\">" . $name . "</a>";
			}

			// Hide grid edit, multi-delete and multi-update
			if ($this->TotalRecs <= 0) {
				$option = &$options["addedit"];
				$item = &$option->GetItem("gridedit");
				if ($item) $item->Visible = FALSE;
				$option = &$options["action"];
				$item = &$option->GetItem("multidelete");
				if ($item) $item->Visible = FALSE;
				$item = &$option->GetItem("multiupdate");
				if ($item) $item->Visible = FALSE;
			}
	}

	// Process custom action
	function ProcessCustomAction() {
		global $conn, $Language, $Security;
		$sFilter = $this->GetKeyFilter();
		$UserAction = @$_POST["useraction"];
		if ($sFilter <> "" && $UserAction <> "") {
			$this->CurrentFilter = $sFilter;
			$sSql = $this->SQL();
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$rs = $conn->Execute($sSql);
			$conn->raiseErrorFn = '';
			$rsuser = ($rs) ? $rs->GetRows() : array();
			if ($rs)
				$rs->Close();

			// Call row custom action event
			if (count($rsuser) > 0) {
				$conn->BeginTrans();
				foreach ($rsuser as $row) {
					$Processed = $this->Row_CustomAction($UserAction, $row);
					if (!$Processed) break;
				}
				if ($Processed) {
					$conn->CommitTrans(); // Commit the changes
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage(str_replace('%s', $UserAction, $Language->Phrase("CustomActionCompleted"))); // Set up success message
				} else {
					$conn->RollbackTrans(); // Rollback changes

					// Set up error message
					if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

						// Use the message, do nothing
					} elseif ($this->CancelMessage <> "") {
						$this->setFailureMessage($this->CancelMessage);
						$this->CancelMessage = "";
					} else {
						$this->setFailureMessage(str_replace('%s', $UserAction, $Language->Phrase("CustomActionCancelled")));
					}
				}
			}
		}
	}

	function RenderListOptionsExt() {
		global $Security, $Language;
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

	// Load basic search values
	function LoadBasicSearchValues() {
		$this->BasicSearch->Keyword = @$_GET[EW_TABLE_BASIC_SEARCH];
		if ($this->BasicSearch->Keyword <> "") $this->Command = "search";
		$this->BasicSearch->Type = @$_GET[EW_TABLE_BASIC_SEARCH_TYPE];
	}

	//  Load search values for validation
	function LoadSearchValues() {
		global $objForm;

		// Load search values
		// id

		$this->id->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_id"]);
		if ($this->id->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->id->AdvancedSearch->SearchOperator = @$_GET["z_id"];

		// user_id
		$this->user_id->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_user_id"]);
		if ($this->user_id->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->user_id->AdvancedSearch->SearchOperator = @$_GET["z_user_id"];

		// travel_name
		$this->travel_name->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_travel_name"]);
		if ($this->travel_name->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->travel_name->AdvancedSearch->SearchOperator = @$_GET["z_travel_name"];

		// start_point
		$this->start_point->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_start_point"]);
		if ($this->start_point->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->start_point->AdvancedSearch->SearchOperator = @$_GET["z_start_point"];

		// end_point
		$this->end_point->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_end_point"]);
		if ($this->end_point->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->end_point->AdvancedSearch->SearchOperator = @$_GET["z_end_point"];

		// capacity
		$this->capacity->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_capacity"]);
		if ($this->capacity->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->capacity->AdvancedSearch->SearchOperator = @$_GET["z_capacity"];

		// start_time
		$this->start_time->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_start_time"]);
		if ($this->start_time->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->start_time->AdvancedSearch->SearchOperator = @$_GET["z_start_time"];

		// passenger_gender
		$this->passenger_gender->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_passenger_gender"]);
		if ($this->passenger_gender->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->passenger_gender->AdvancedSearch->SearchOperator = @$_GET["z_passenger_gender"];

		// status
		$this->status->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_status"]);
		if ($this->status->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->status->AdvancedSearch->SearchOperator = @$_GET["z_status"];

		// created_at
		$this->created_at->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_created_at"]);
		if ($this->created_at->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->created_at->AdvancedSearch->SearchOperator = @$_GET["z_created_at"];

		// updated_at
		$this->updated_at->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_updated_at"]);
		if ($this->updated_at->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->updated_at->AdvancedSearch->SearchOperator = @$_GET["z_updated_at"];
	}

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
		$this->ViewUrl = $this->GetViewUrl();
		$this->EditUrl = $this->GetEditUrl();
		$this->InlineEditUrl = $this->GetInlineEditUrl();
		$this->CopyUrl = $this->GetCopyUrl();
		$this->InlineCopyUrl = $this->GetInlineCopyUrl();
		$this->DeleteUrl = $this->GetDeleteUrl();

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
		} elseif ($this->RowType == EW_ROWTYPE_SEARCH) { // Search row

			// id
			$this->id->EditCustomAttributes = "";
			$this->id->EditValue = ew_HtmlEncode($this->id->AdvancedSearch->SearchValue);
			$this->id->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->id->FldCaption()));

			// user_id
			$this->user_id->EditCustomAttributes = "";

			// travel_name
			$this->travel_name->EditCustomAttributes = "";
			$this->travel_name->EditValue = ew_HtmlEncode($this->travel_name->AdvancedSearch->SearchValue);
			$this->travel_name->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->travel_name->FldCaption()));

			// start_point
			$this->start_point->EditCustomAttributes = "";
			$this->start_point->EditValue = ew_HtmlEncode($this->start_point->AdvancedSearch->SearchValue);
			$this->start_point->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->start_point->FldCaption()));

			// end_point
			$this->end_point->EditCustomAttributes = "";
			$this->end_point->EditValue = ew_HtmlEncode($this->end_point->AdvancedSearch->SearchValue);
			$this->end_point->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->end_point->FldCaption()));

			// capacity
			$this->capacity->EditCustomAttributes = "";
			$this->capacity->EditValue = ew_HtmlEncode($this->capacity->AdvancedSearch->SearchValue);
			$this->capacity->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->capacity->FldCaption()));

			// start_time
			$this->start_time->EditCustomAttributes = "";
			$this->start_time->EditValue = ew_HtmlEncode(ew_FormatDateTime(ew_UnFormatDateTime($this->start_time->AdvancedSearch->SearchValue, 9), 9));
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

			// created_at
			$this->created_at->EditCustomAttributes = "";
			$this->created_at->EditValue = ew_HtmlEncode(ew_FormatDateTime(ew_UnFormatDateTime($this->created_at->AdvancedSearch->SearchValue, 5), 5));
			$this->created_at->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->created_at->FldCaption()));

			// updated_at
			$this->updated_at->EditCustomAttributes = "";
			$this->updated_at->EditValue = ew_HtmlEncode(ew_FormatDateTime(ew_UnFormatDateTime($this->updated_at->AdvancedSearch->SearchValue, 5), 5));
			$this->updated_at->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->updated_at->FldCaption()));
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

	// Validate search
	function ValidateSearch() {
		global $gsSearchError;

		// Initialize
		$gsSearchError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return TRUE;

		// Return validate result
		$ValidateSearch = ($gsSearchError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateSearch = $ValidateSearch && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsSearchError, $sFormCustomError);
		}
		return $ValidateSearch;
	}

	// Load advanced search
	function LoadAdvancedSearch() {
		$this->id->AdvancedSearch->Load();
		$this->user_id->AdvancedSearch->Load();
		$this->travel_name->AdvancedSearch->Load();
		$this->start_point->AdvancedSearch->Load();
		$this->end_point->AdvancedSearch->Load();
		$this->capacity->AdvancedSearch->Load();
		$this->start_time->AdvancedSearch->Load();
		$this->passenger_gender->AdvancedSearch->Load();
		$this->status->AdvancedSearch->Load();
		$this->created_at->AdvancedSearch->Load();
		$this->updated_at->AdvancedSearch->Load();
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$url = ew_CurrentUrl();
		$url = preg_replace('/\?cmd=reset(all){0,1}$/i', '', $url); // Remove cmd=reset / cmd=resetall
		$Breadcrumb->Add("list", $this->TableVar, $url, $this->TableVar, TRUE);
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

	// ListOptions Load event
	function ListOptions_Load() {

		// Example:
		//$opt = &$this->ListOptions->Add("new");
		//$opt->Header = "xxx";
		//$opt->OnLeft = TRUE; // Link on left
		//$opt->MoveTo(0); // Move to first column

	}

	// ListOptions Rendered event
	function ListOptions_Rendered() {

		// Example: 
		//$this->ListOptions->Items["new"]->Body = "xxx";

	}

	// Row Custom Action event
	function Row_CustomAction($action, $row) {

		// Return FALSE to abort
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($travels_list)) $travels_list = new ctravels_list();

// Page init
$travels_list->Page_Init();

// Page main
$travels_list->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$travels_list->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var travels_list = new ew_Page("travels_list");
travels_list.PageID = "list"; // Page ID
var EW_PAGE_ID = travels_list.PageID; // For backward compatibility

// Form object
var ftravelslist = new ew_Form("ftravelslist");
ftravelslist.FormKeyCountName = '<?php echo $travels_list->FormKeyCountName ?>';

// Form_CustomValidate event
ftravelslist.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
ftravelslist.ValidateRequired = true;
<?php } else { ?>
ftravelslist.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
ftravelslist.Lists["x_user_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_username","","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
var ftravelslistsrch = new ew_Form("ftravelslistsrch");

// Validate function for search
ftravelslistsrch.Validate = function(fobj) {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	fobj = fobj || this.Form;
	this.PostAutoSuggest();
	var infix = "";

	// Set up row object
	ew_ElementsToRow(fobj);

	// Fire Form_CustomValidate event
	if (!this.Form_CustomValidate(fobj))
		return false;
	return true;
}

// Form_CustomValidate event
ftravelslistsrch.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
ftravelslistsrch.ValidateRequired = true; // Use JavaScript validation
<?php } else { ?>
ftravelslistsrch.ValidateRequired = false; // No JavaScript validation
<?php } ?>

// Dynamic selection lists
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php if ($travels_list->ExportOptions->Visible()) { ?>
<div class="ewListExportOptions"><?php $travels_list->ExportOptions->Render("body") ?></div>
<?php } ?>
<?php
	$bSelectLimit = EW_SELECT_LIMIT;
	if ($bSelectLimit) {
		$travels_list->TotalRecs = $travels->SelectRecordCount();
	} else {
		if ($travels_list->Recordset = $travels_list->LoadRecordset())
			$travels_list->TotalRecs = $travels_list->Recordset->RecordCount();
	}
	$travels_list->StartRec = 1;
	if ($travels_list->DisplayRecs <= 0 || ($travels->Export <> "" && $travels->ExportAll)) // Display all records
		$travels_list->DisplayRecs = $travels_list->TotalRecs;
	if (!($travels->Export <> "" && $travels->ExportAll))
		$travels_list->SetUpStartRec(); // Set up start record position
	if ($bSelectLimit)
		$travels_list->Recordset = $travels_list->LoadRecordset($travels_list->StartRec-1, $travels_list->DisplayRecs);
$travels_list->RenderOtherOptions();
?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($travels->Export == "" && $travels->CurrentAction == "") { ?>
<form name="ftravelslistsrch" id="ftravelslistsrch" class="ewForm form-inline" action="<?php echo ew_CurrentPage() ?>">
<div class="accordion ewDisplayTable ewSearchTable" id="ftravelslistsrch_SearchGroup">
	<div class="accordion-group">
		<div class="accordion-heading">
<a class="accordion-toggle" data-toggle="collapse" data-parent="#ftravelslistsrch_SearchGroup" href="#ftravelslistsrch_SearchBody"><?php echo $Language->Phrase("Search") ?></a>
		</div>
		<div id="ftravelslistsrch_SearchBody" class="accordion-body collapse in">
			<div class="accordion-inner">
<div id="ftravelslistsrch_SearchPanel">
<input type="hidden" name="cmd" value="search">
<input type="hidden" name="t" value="travels">
<div class="ewBasicSearch">
<?php
if ($gsSearchError == "")
	$travels_list->LoadAdvancedSearch(); // Load advanced search

// Render for search
$travels->RowType = EW_ROWTYPE_SEARCH;

// Render row
$travels->ResetAttrs();
$travels_list->RenderRow();
?>
<div id="xsr_1" class="ewRow">
<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
	<span id="xsc_passenger_gender" class="ewCell">
		<span class="ewSearchCaption"><?php echo $travels->passenger_gender->FldCaption() ?></span>
		<span class="ewSearchOperator"><?php echo $Language->Phrase("=") ?><input type="hidden" name="z_passenger_gender" id="z_passenger_gender" value="="></span>
		<span class="control-group ewSearchField">
<div id="tp_x_passenger_gender" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_passenger_gender" id="x_passenger_gender" value="{value}"<?php echo $travels->passenger_gender->EditAttributes() ?>></div>
<div id="dsl_x_passenger_gender" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $travels->passenger_gender->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($travels->passenger_gender->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
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
	</span>
<?php } ?>
</div>
<div id="xsr_2" class="ewRow">
<?php if ($travels->status->Visible) { // status ?>
	<span id="xsc_status" class="ewCell">
		<span class="ewSearchCaption"><?php echo $travels->status->FldCaption() ?></span>
		<span class="ewSearchOperator"><?php echo $Language->Phrase("=") ?><input type="hidden" name="z_status" id="z_status" value="="></span>
		<span class="control-group ewSearchField">
<div id="tp_x_status" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_status" id="x_status" value="{value}"<?php echo $travels->status->EditAttributes() ?>></div>
<div id="dsl_x_status" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $travels->status->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($travels->status->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
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
	</span>
<?php } ?>
</div>
<div id="xsr_3" class="ewRow">
	<div class="btn-group ewButtonGroup">
	<div class="input-append">
	<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" class="input-large" value="<?php echo ew_HtmlEncode($travels_list->BasicSearch->getKeyword()) ?>" placeholder="<?php echo $Language->Phrase("Search") ?>">
	<button class="btn btn-primary ewButton" name="btnsubmit" id="btnsubmit" type="submit"><?php echo $Language->Phrase("QuickSearchBtn") ?></button>
	</div>
	</div>
	<div class="btn-group ewButtonGroup">
	<a class="btn ewShowAll" href="<?php echo $travels_list->PageUrl() ?>cmd=reset"><?php echo $Language->Phrase("ShowAll") ?></a>
	</div>
</div>
<div id="xsr_4" class="ewRow">
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="="<?php if ($travels_list->BasicSearch->getType() == "=") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("ExactPhrase") ?></label>
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND"<?php if ($travels_list->BasicSearch->getType() == "AND") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AllWord") ?></label>
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR"<?php if ($travels_list->BasicSearch->getType() == "OR") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AnyWord") ?></label>
</div>
</div>
</div>
			</div>
		</div>
	</div>
</div>
</form>
<?php } ?>
<?php } ?>
<?php $travels_list->ShowPageHeader(); ?>
<?php
$travels_list->ShowMessage();
?>
<table class="ewGrid"><tr><td class="ewGridContent">
<form name="ftravelslist" id="ftravelslist" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="travels">
<div id="gmp_travels" class="ewGridMiddlePanel">
<?php if ($travels_list->TotalRecs > 0) { ?>
<table id="tbl_travelslist" class="ewTable ewTableSeparate">
<?php echo $travels->TableCustomInnerHtml ?>
<thead><!-- Table header -->
	<tr class="ewTableHeader">
<?php

// Render list options
$travels_list->RenderListOptions();

// Render list options (header, left)
$travels_list->ListOptions->Render("header", "left");
?>
<?php if ($travels->id->Visible) { // id ?>
	<?php if ($travels->SortUrl($travels->id) == "") { ?>
		<td><div id="elh_travels_id" class="travels_id"><div class="ewTableHeaderCaption"><?php echo $travels->id->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->id) ?>',1);"><div id="elh_travels_id" class="travels_id">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->id->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->id->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->id->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->user_id->Visible) { // user_id ?>
	<?php if ($travels->SortUrl($travels->user_id) == "") { ?>
		<td><div id="elh_travels_user_id" class="travels_user_id"><div class="ewTableHeaderCaption"><?php echo $travels->user_id->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->user_id) ?>',1);"><div id="elh_travels_user_id" class="travels_user_id">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->user_id->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->user_id->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->user_id->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->travel_name->Visible) { // travel_name ?>
	<?php if ($travels->SortUrl($travels->travel_name) == "") { ?>
		<td><div id="elh_travels_travel_name" class="travels_travel_name"><div class="ewTableHeaderCaption"><?php echo $travels->travel_name->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->travel_name) ?>',1);"><div id="elh_travels_travel_name" class="travels_travel_name">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->travel_name->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($travels->travel_name->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->travel_name->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->start_point->Visible) { // start_point ?>
	<?php if ($travels->SortUrl($travels->start_point) == "") { ?>
		<td><div id="elh_travels_start_point" class="travels_start_point"><div class="ewTableHeaderCaption"><?php echo $travels->start_point->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->start_point) ?>',1);"><div id="elh_travels_start_point" class="travels_start_point">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->start_point->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($travels->start_point->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->start_point->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->end_point->Visible) { // end_point ?>
	<?php if ($travels->SortUrl($travels->end_point) == "") { ?>
		<td><div id="elh_travels_end_point" class="travels_end_point"><div class="ewTableHeaderCaption"><?php echo $travels->end_point->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->end_point) ?>',1);"><div id="elh_travels_end_point" class="travels_end_point">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->end_point->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($travels->end_point->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->end_point->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->capacity->Visible) { // capacity ?>
	<?php if ($travels->SortUrl($travels->capacity) == "") { ?>
		<td><div id="elh_travels_capacity" class="travels_capacity"><div class="ewTableHeaderCaption"><?php echo $travels->capacity->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->capacity) ?>',1);"><div id="elh_travels_capacity" class="travels_capacity">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->capacity->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($travels->capacity->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->capacity->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->start_time->Visible) { // start_time ?>
	<?php if ($travels->SortUrl($travels->start_time) == "") { ?>
		<td><div id="elh_travels_start_time" class="travels_start_time"><div class="ewTableHeaderCaption"><?php echo $travels->start_time->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->start_time) ?>',1);"><div id="elh_travels_start_time" class="travels_start_time">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->start_time->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->start_time->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->start_time->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
	<?php if ($travels->SortUrl($travels->passenger_gender) == "") { ?>
		<td><div id="elh_travels_passenger_gender" class="travels_passenger_gender"><div class="ewTableHeaderCaption"><?php echo $travels->passenger_gender->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->passenger_gender) ?>',1);"><div id="elh_travels_passenger_gender" class="travels_passenger_gender">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->passenger_gender->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->passenger_gender->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->passenger_gender->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->status->Visible) { // status ?>
	<?php if ($travels->SortUrl($travels->status) == "") { ?>
		<td><div id="elh_travels_status" class="travels_status"><div class="ewTableHeaderCaption"><?php echo $travels->status->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->status) ?>',1);"><div id="elh_travels_status" class="travels_status">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->status->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->status->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->status->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->created_at->Visible) { // created_at ?>
	<?php if ($travels->SortUrl($travels->created_at) == "") { ?>
		<td><div id="elh_travels_created_at" class="travels_created_at"><div class="ewTableHeaderCaption"><?php echo $travels->created_at->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->created_at) ?>',1);"><div id="elh_travels_created_at" class="travels_created_at">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->created_at->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->created_at->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->created_at->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($travels->updated_at->Visible) { // updated_at ?>
	<?php if ($travels->SortUrl($travels->updated_at) == "") { ?>
		<td><div id="elh_travels_updated_at" class="travels_updated_at"><div class="ewTableHeaderCaption"><?php echo $travels->updated_at->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $travels->SortUrl($travels->updated_at) ?>',1);"><div id="elh_travels_updated_at" class="travels_updated_at">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $travels->updated_at->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($travels->updated_at->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($travels->updated_at->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php

// Render list options (header, right)
$travels_list->ListOptions->Render("header", "right");
?>
	</tr>
</thead>
<tbody>
<?php
if ($travels->ExportAll && $travels->Export <> "") {
	$travels_list->StopRec = $travels_list->TotalRecs;
} else {

	// Set the last record to display
	if ($travels_list->TotalRecs > $travels_list->StartRec + $travels_list->DisplayRecs - 1)
		$travels_list->StopRec = $travels_list->StartRec + $travels_list->DisplayRecs - 1;
	else
		$travels_list->StopRec = $travels_list->TotalRecs;
}
$travels_list->RecCnt = $travels_list->StartRec - 1;
if ($travels_list->Recordset && !$travels_list->Recordset->EOF) {
	$travels_list->Recordset->MoveFirst();
	if (!$bSelectLimit && $travels_list->StartRec > 1)
		$travels_list->Recordset->Move($travels_list->StartRec - 1);
} elseif (!$travels->AllowAddDeleteRow && $travels_list->StopRec == 0) {
	$travels_list->StopRec = $travels->GridAddRowCount;
}

// Initialize aggregate
$travels->RowType = EW_ROWTYPE_AGGREGATEINIT;
$travels->ResetAttrs();
$travels_list->RenderRow();
while ($travels_list->RecCnt < $travels_list->StopRec) {
	$travels_list->RecCnt++;
	if (intval($travels_list->RecCnt) >= intval($travels_list->StartRec)) {
		$travels_list->RowCnt++;

		// Set up key count
		$travels_list->KeyCount = $travels_list->RowIndex;

		// Init row class and style
		$travels->ResetAttrs();
		$travels->CssClass = "";
		if ($travels->CurrentAction == "gridadd") {
		} else {
			$travels_list->LoadRowValues($travels_list->Recordset); // Load row values
		}
		$travels->RowType = EW_ROWTYPE_VIEW; // Render view

		// Set up row id / data-rowindex
		$travels->RowAttrs = array_merge($travels->RowAttrs, array('data-rowindex'=>$travels_list->RowCnt, 'id'=>'r' . $travels_list->RowCnt . '_travels', 'data-rowtype'=>$travels->RowType));

		// Render row
		$travels_list->RenderRow();

		// Render list options
		$travels_list->RenderListOptions();
?>
	<tr<?php echo $travels->RowAttributes() ?>>
<?php

// Render list options (body, left)
$travels_list->ListOptions->Render("body", "left", $travels_list->RowCnt);
?>
	<?php if ($travels->id->Visible) { // id ?>
		<td<?php echo $travels->id->CellAttributes() ?>>
<span<?php echo $travels->id->ViewAttributes() ?>>
<?php echo $travels->id->ListViewValue() ?></span>
<a id="<?php echo $travels_list->PageObjName . "_row_" . $travels_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($travels->user_id->Visible) { // user_id ?>
		<td<?php echo $travels->user_id->CellAttributes() ?>>
<span<?php echo $travels->user_id->ViewAttributes() ?>>
<?php echo $travels->user_id->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->travel_name->Visible) { // travel_name ?>
		<td<?php echo $travels->travel_name->CellAttributes() ?>>
<span<?php echo $travels->travel_name->ViewAttributes() ?>>
<?php echo $travels->travel_name->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->start_point->Visible) { // start_point ?>
		<td<?php echo $travels->start_point->CellAttributes() ?>>
<span<?php echo $travels->start_point->ViewAttributes() ?>>
<?php echo $travels->start_point->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->end_point->Visible) { // end_point ?>
		<td<?php echo $travels->end_point->CellAttributes() ?>>
<span<?php echo $travels->end_point->ViewAttributes() ?>>
<?php echo $travels->end_point->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->capacity->Visible) { // capacity ?>
		<td<?php echo $travels->capacity->CellAttributes() ?>>
<span<?php echo $travels->capacity->ViewAttributes() ?>>
<?php echo $travels->capacity->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->start_time->Visible) { // start_time ?>
		<td<?php echo $travels->start_time->CellAttributes() ?>>
<span<?php echo $travels->start_time->ViewAttributes() ?>>
<?php echo $travels->start_time->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->passenger_gender->Visible) { // passenger_gender ?>
		<td<?php echo $travels->passenger_gender->CellAttributes() ?>>
<span<?php echo $travels->passenger_gender->ViewAttributes() ?>>
<?php echo $travels->passenger_gender->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->status->Visible) { // status ?>
		<td<?php echo $travels->status->CellAttributes() ?>>
<span<?php echo $travels->status->ViewAttributes() ?>>
<?php echo $travels->status->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->created_at->Visible) { // created_at ?>
		<td<?php echo $travels->created_at->CellAttributes() ?>>
<span<?php echo $travels->created_at->ViewAttributes() ?>>
<?php echo $travels->created_at->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($travels->updated_at->Visible) { // updated_at ?>
		<td<?php echo $travels->updated_at->CellAttributes() ?>>
<span<?php echo $travels->updated_at->ViewAttributes() ?>>
<?php echo $travels->updated_at->ListViewValue() ?></span>
</td>
	<?php } ?>
<?php

// Render list options (body, right)
$travels_list->ListOptions->Render("body", "right", $travels_list->RowCnt);
?>
	</tr>
<?php
	}
	if ($travels->CurrentAction <> "gridadd")
		$travels_list->Recordset->MoveNext();
}
?>
</tbody>
</table>
<?php } ?>
<?php if ($travels->CurrentAction == "") { ?>
<input type="hidden" name="a_list" id="a_list" value="">
<?php } ?>
</div>
</form>
<?php

// Close recordset
if ($travels_list->Recordset)
	$travels_list->Recordset->Close();
?>
<div class="ewGridLowerPanel">
<?php if ($travels->CurrentAction <> "gridadd" && $travels->CurrentAction <> "gridedit") { ?>
<form name="ewPagerForm" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>">
<table class="ewPager">
<tr><td>
<?php if (!isset($travels_list->Pager)) $travels_list->Pager = new cPrevNextPager($travels_list->StartRec, $travels_list->DisplayRecs, $travels_list->TotalRecs) ?>
<?php if ($travels_list->Pager->RecordCount > 0) { ?>
<table class="ewStdTable"><tbody><tr><td>
	<?php echo $Language->Phrase("Page") ?>&nbsp;
<div class="input-prepend input-append">
<!--first page button-->
	<?php if ($travels_list->Pager->FirstButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $travels_list->PageUrl() ?>start=<?php echo $travels_list->Pager->FirstButton->Start ?>"><i class="icon-step-backward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-step-backward"></i></a>
	<?php } ?>
<!--previous page button-->
	<?php if ($travels_list->Pager->PrevButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $travels_list->PageUrl() ?>start=<?php echo $travels_list->Pager->PrevButton->Start ?>"><i class="icon-prev"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-prev"></i></a>
	<?php } ?>
<!--current page number-->
	<input class="input-mini" type="text" name="<?php echo EW_TABLE_PAGE_NO ?>" value="<?php echo $travels_list->Pager->CurrentPage ?>">
<!--next page button-->
	<?php if ($travels_list->Pager->NextButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $travels_list->PageUrl() ?>start=<?php echo $travels_list->Pager->NextButton->Start ?>"><i class="icon-play"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-play"></i></a>
	<?php } ?>
<!--last page button-->
	<?php if ($travels_list->Pager->LastButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $travels_list->PageUrl() ?>start=<?php echo $travels_list->Pager->LastButton->Start ?>"><i class="icon-step-forward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-step-forward"></i></a>
	<?php } ?>
</div>
	&nbsp;<?php echo $Language->Phrase("of") ?>&nbsp;<?php echo $travels_list->Pager->PageCount ?>
</td>
<td>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $Language->Phrase("Record") ?>&nbsp;<?php echo $travels_list->Pager->FromIndex ?>&nbsp;<?php echo $Language->Phrase("To") ?>&nbsp;<?php echo $travels_list->Pager->ToIndex ?>&nbsp;<?php echo $Language->Phrase("Of") ?>&nbsp;<?php echo $travels_list->Pager->RecordCount ?>
</td>
</tr></tbody></table>
<?php } else { ?>
	<?php if ($travels_list->SearchWhere == "0=101") { ?>
	<p><?php echo $Language->Phrase("EnterSearchCriteria") ?></p>
	<?php } else { ?>
	<p><?php echo $Language->Phrase("NoRecord") ?></p>
	<?php } ?>
<?php } ?>
</td>
</tr></table>
</form>
<?php } ?>
<div class="ewListOtherOptions">
<?php
	foreach ($travels_list->OtherOptions as &$option)
		$option->Render("body", "bottom");
?>
</div>
</div>
</td></tr></table>
<script type="text/javascript">
ftravelslistsrch.Init();
ftravelslist.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$travels_list->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$travels_list->Page_Terminate();
?>
