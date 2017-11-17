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

$users_list = NULL; // Initialize page object first

class cusers_list extends cusers {

	// Page ID
	var $PageID = 'list';

	// Project ID
	var $ProjectID = "{13D25F72-8F7B-4FA3-9328-7C3143C406A5}";

	// Table name
	var $TableName = 'users';

	// Page object name
	var $PageObjName = 'users_list';

	// Grid form hidden field names
	var $FormName = 'fuserslist';
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

		// Table object (users)
		if (!isset($GLOBALS["users"]) || get_class($GLOBALS["users"]) == "cusers") {
			$GLOBALS["users"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["users"];
		}

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportHtmlUrl = $this->PageUrl() . "export=html";
		$this->ExportXmlUrl = $this->PageUrl() . "export=xml";
		$this->ExportCsvUrl = $this->PageUrl() . "export=csv";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";
		$this->AddUrl = "usersadd.php";
		$this->InlineAddUrl = $this->PageUrl() . "a=add";
		$this->GridAddUrl = $this->PageUrl() . "a=gridadd";
		$this->GridEditUrl = $this->PageUrl() . "a=gridedit";
		$this->MultiDeleteUrl = "usersdelete.php";
		$this->MultiUpdateUrl = "usersupdate.php";

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'list', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'users', TRUE);

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
		$this->BuildSearchSql($sWhere, $this->username, FALSE); // username
		$this->BuildSearchSql($sWhere, $this->phone, FALSE); // phone
		$this->BuildSearchSql($sWhere, $this->password, FALSE); // password
		$this->BuildSearchSql($sWhere, $this->gender, FALSE); // gender
		$this->BuildSearchSql($sWhere, $this->have_car, FALSE); // have_car
		$this->BuildSearchSql($sWhere, $this->car_number, FALSE); // car_number
		$this->BuildSearchSql($sWhere, $this->car_model, FALSE); // car_model
		$this->BuildSearchSql($sWhere, $this->car_color, FALSE); // car_color
		$this->BuildSearchSql($sWhere, $this->type, FALSE); // type
		$this->BuildSearchSql($sWhere, $this->img, FALSE); // img
		$this->BuildSearchSql($sWhere, $this->remember_token, FALSE); // remember_token
		$this->BuildSearchSql($sWhere, $this->created_at, FALSE); // created_at
		$this->BuildSearchSql($sWhere, $this->updated_at, FALSE); // updated_at

		// Set up search parm
		if ($sWhere <> "") {
			$this->Command = "search";
		}
		if ($this->Command == "search") {
			$this->id->AdvancedSearch->Save(); // id
			$this->username->AdvancedSearch->Save(); // username
			$this->phone->AdvancedSearch->Save(); // phone
			$this->password->AdvancedSearch->Save(); // password
			$this->gender->AdvancedSearch->Save(); // gender
			$this->have_car->AdvancedSearch->Save(); // have_car
			$this->car_number->AdvancedSearch->Save(); // car_number
			$this->car_model->AdvancedSearch->Save(); // car_model
			$this->car_color->AdvancedSearch->Save(); // car_color
			$this->type->AdvancedSearch->Save(); // type
			$this->img->AdvancedSearch->Save(); // img
			$this->remember_token->AdvancedSearch->Save(); // remember_token
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
		$this->BuildBasicSearchSQL($sWhere, $this->username, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->phone, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->password, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->car_number, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->car_model, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->car_color, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->img, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->remember_token, $Keyword);
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
		if ($this->username->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->phone->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->password->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->gender->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->have_car->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->car_number->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->car_model->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->car_color->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->type->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->img->AdvancedSearch->IssetSession())
			return TRUE;
		if ($this->remember_token->AdvancedSearch->IssetSession())
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
		$this->username->AdvancedSearch->UnsetSession();
		$this->phone->AdvancedSearch->UnsetSession();
		$this->password->AdvancedSearch->UnsetSession();
		$this->gender->AdvancedSearch->UnsetSession();
		$this->have_car->AdvancedSearch->UnsetSession();
		$this->car_number->AdvancedSearch->UnsetSession();
		$this->car_model->AdvancedSearch->UnsetSession();
		$this->car_color->AdvancedSearch->UnsetSession();
		$this->type->AdvancedSearch->UnsetSession();
		$this->img->AdvancedSearch->UnsetSession();
		$this->remember_token->AdvancedSearch->UnsetSession();
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
		$this->username->AdvancedSearch->Load();
		$this->phone->AdvancedSearch->Load();
		$this->password->AdvancedSearch->Load();
		$this->gender->AdvancedSearch->Load();
		$this->have_car->AdvancedSearch->Load();
		$this->car_number->AdvancedSearch->Load();
		$this->car_model->AdvancedSearch->Load();
		$this->car_color->AdvancedSearch->Load();
		$this->type->AdvancedSearch->Load();
		$this->img->AdvancedSearch->Load();
		$this->remember_token->AdvancedSearch->Load();
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
			$this->UpdateSort($this->username); // username
			$this->UpdateSort($this->phone); // phone
			$this->UpdateSort($this->password); // password
			$this->UpdateSort($this->gender); // gender
			$this->UpdateSort($this->have_car); // have_car
			$this->UpdateSort($this->car_number); // car_number
			$this->UpdateSort($this->car_model); // car_model
			$this->UpdateSort($this->car_color); // car_color
			$this->UpdateSort($this->type); // type
			$this->UpdateSort($this->img); // img
			$this->UpdateSort($this->remember_token); // remember_token
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
				$this->username->setSort("");
				$this->phone->setSort("");
				$this->password->setSort("");
				$this->gender->setSort("");
				$this->have_car->setSort("");
				$this->car_number->setSort("");
				$this->car_model->setSort("");
				$this->car_color->setSort("");
				$this->type->setSort("");
				$this->img->setSort("");
				$this->remember_token->setSort("");
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
				$item->Body = "<a class=\"ewAction ewCustomAction\" href=\"\" onclick=\"ew_SubmitSelected(document.fuserslist, '" . ew_CurrentUrl() . "', null, '" . $action . "');return false;\">" . $name . "</a>";
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

		// username
		$this->username->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_username"]);
		if ($this->username->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->username->AdvancedSearch->SearchOperator = @$_GET["z_username"];

		// phone
		$this->phone->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_phone"]);
		if ($this->phone->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->phone->AdvancedSearch->SearchOperator = @$_GET["z_phone"];

		// password
		$this->password->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_password"]);
		if ($this->password->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->password->AdvancedSearch->SearchOperator = @$_GET["z_password"];

		// gender
		$this->gender->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_gender"]);
		if ($this->gender->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->gender->AdvancedSearch->SearchOperator = @$_GET["z_gender"];

		// have_car
		$this->have_car->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_have_car"]);
		if ($this->have_car->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->have_car->AdvancedSearch->SearchOperator = @$_GET["z_have_car"];

		// car_number
		$this->car_number->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_car_number"]);
		if ($this->car_number->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->car_number->AdvancedSearch->SearchOperator = @$_GET["z_car_number"];

		// car_model
		$this->car_model->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_car_model"]);
		if ($this->car_model->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->car_model->AdvancedSearch->SearchOperator = @$_GET["z_car_model"];

		// car_color
		$this->car_color->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_car_color"]);
		if ($this->car_color->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->car_color->AdvancedSearch->SearchOperator = @$_GET["z_car_color"];

		// type
		$this->type->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_type"]);
		if ($this->type->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->type->AdvancedSearch->SearchOperator = @$_GET["z_type"];

		// img
		$this->img->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_img"]);
		if ($this->img->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->img->AdvancedSearch->SearchOperator = @$_GET["z_img"];

		// remember_token
		$this->remember_token->AdvancedSearch->SearchValue = ew_StripSlashes(@$_GET["x_remember_token"]);
		if ($this->remember_token->AdvancedSearch->SearchValue <> "") $this->Command = "search";
		$this->remember_token->AdvancedSearch->SearchOperator = @$_GET["z_remember_token"];

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
		} elseif ($this->RowType == EW_ROWTYPE_SEARCH) { // Search row

			// id
			$this->id->EditCustomAttributes = "";
			$this->id->EditValue = ew_HtmlEncode($this->id->AdvancedSearch->SearchValue);
			$this->id->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->id->FldCaption()));

			// username
			$this->username->EditCustomAttributes = "";
			$this->username->EditValue = ew_HtmlEncode($this->username->AdvancedSearch->SearchValue);
			$this->username->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->username->FldCaption()));

			// phone
			$this->phone->EditCustomAttributes = "";
			$this->phone->EditValue = ew_HtmlEncode($this->phone->AdvancedSearch->SearchValue);
			$this->phone->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->phone->FldCaption()));

			// password
			$this->password->EditCustomAttributes = "";
			$this->password->EditValue = ew_HtmlEncode($this->password->AdvancedSearch->SearchValue);
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
			$this->car_number->EditValue = ew_HtmlEncode($this->car_number->AdvancedSearch->SearchValue);
			$this->car_number->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_number->FldCaption()));

			// car_model
			$this->car_model->EditCustomAttributes = "";
			$this->car_model->EditValue = ew_HtmlEncode($this->car_model->AdvancedSearch->SearchValue);
			$this->car_model->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_model->FldCaption()));

			// car_color
			$this->car_color->EditCustomAttributes = "";
			$this->car_color->EditValue = ew_HtmlEncode($this->car_color->AdvancedSearch->SearchValue);
			$this->car_color->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->car_color->FldCaption()));

			// type
			$this->type->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->type->FldTagValue(1), $this->type->FldTagCaption(1) <> "" ? $this->type->FldTagCaption(1) : $this->type->FldTagValue(1));
			$arwrk[] = array($this->type->FldTagValue(2), $this->type->FldTagCaption(2) <> "" ? $this->type->FldTagCaption(2) : $this->type->FldTagValue(2));
			$this->type->EditValue = $arwrk;

			// img
			$this->img->EditCustomAttributes = "";
			$this->img->EditValue = ew_HtmlEncode($this->img->AdvancedSearch->SearchValue);
			$this->img->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->img->FldCaption()));

			// remember_token
			$this->remember_token->EditCustomAttributes = "";
			$this->remember_token->EditValue = ew_HtmlEncode($this->remember_token->AdvancedSearch->SearchValue);
			$this->remember_token->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->remember_token->FldCaption()));

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
		$this->username->AdvancedSearch->Load();
		$this->phone->AdvancedSearch->Load();
		$this->password->AdvancedSearch->Load();
		$this->gender->AdvancedSearch->Load();
		$this->have_car->AdvancedSearch->Load();
		$this->car_number->AdvancedSearch->Load();
		$this->car_model->AdvancedSearch->Load();
		$this->car_color->AdvancedSearch->Load();
		$this->type->AdvancedSearch->Load();
		$this->img->AdvancedSearch->Load();
		$this->remember_token->AdvancedSearch->Load();
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
if (!isset($users_list)) $users_list = new cusers_list();

// Page init
$users_list->Page_Init();

// Page main
$users_list->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$users_list->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var users_list = new ew_Page("users_list");
users_list.PageID = "list"; // Page ID
var EW_PAGE_ID = users_list.PageID; // For backward compatibility

// Form object
var fuserslist = new ew_Form("fuserslist");
fuserslist.FormKeyCountName = '<?php echo $users_list->FormKeyCountName ?>';

// Form_CustomValidate event
fuserslist.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fuserslist.ValidateRequired = true;
<?php } else { ?>
fuserslist.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

var fuserslistsrch = new ew_Form("fuserslistsrch");

// Validate function for search
fuserslistsrch.Validate = function(fobj) {
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
fuserslistsrch.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fuserslistsrch.ValidateRequired = true; // Use JavaScript validation
<?php } else { ?>
fuserslistsrch.ValidateRequired = false; // No JavaScript validation
<?php } ?>

// Dynamic selection lists
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php if ($users_list->ExportOptions->Visible()) { ?>
<div class="ewListExportOptions"><?php $users_list->ExportOptions->Render("body") ?></div>
<?php } ?>
<?php
	$bSelectLimit = EW_SELECT_LIMIT;
	if ($bSelectLimit) {
		$users_list->TotalRecs = $users->SelectRecordCount();
	} else {
		if ($users_list->Recordset = $users_list->LoadRecordset())
			$users_list->TotalRecs = $users_list->Recordset->RecordCount();
	}
	$users_list->StartRec = 1;
	if ($users_list->DisplayRecs <= 0 || ($users->Export <> "" && $users->ExportAll)) // Display all records
		$users_list->DisplayRecs = $users_list->TotalRecs;
	if (!($users->Export <> "" && $users->ExportAll))
		$users_list->SetUpStartRec(); // Set up start record position
	if ($bSelectLimit)
		$users_list->Recordset = $users_list->LoadRecordset($users_list->StartRec-1, $users_list->DisplayRecs);
$users_list->RenderOtherOptions();
?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($users->Export == "" && $users->CurrentAction == "") { ?>
<form name="fuserslistsrch" id="fuserslistsrch" class="ewForm form-inline" action="<?php echo ew_CurrentPage() ?>">
<div class="accordion ewDisplayTable ewSearchTable" id="fuserslistsrch_SearchGroup">
	<div class="accordion-group">
		<div class="accordion-heading">
<a class="accordion-toggle" data-toggle="collapse" data-parent="#fuserslistsrch_SearchGroup" href="#fuserslistsrch_SearchBody"><?php echo $Language->Phrase("Search") ?></a>
		</div>
		<div id="fuserslistsrch_SearchBody" class="accordion-body collapse in">
			<div class="accordion-inner">
<div id="fuserslistsrch_SearchPanel">
<input type="hidden" name="cmd" value="search">
<input type="hidden" name="t" value="users">
<div class="ewBasicSearch">
<?php
if ($gsSearchError == "")
	$users_list->LoadAdvancedSearch(); // Load advanced search

// Render for search
$users->RowType = EW_ROWTYPE_SEARCH;

// Render row
$users->ResetAttrs();
$users_list->RenderRow();
?>
<div id="xsr_1" class="ewRow">
<?php if ($users->gender->Visible) { // gender ?>
	<span id="xsc_gender" class="ewCell">
		<span class="ewSearchCaption"><?php echo $users->gender->FldCaption() ?></span>
		<span class="ewSearchOperator"><?php echo $Language->Phrase("=") ?><input type="hidden" name="z_gender" id="z_gender" value="="></span>
		<span class="control-group ewSearchField">
<div id="tp_x_gender" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_gender" id="x_gender" value="{value}"<?php echo $users->gender->EditAttributes() ?>></div>
<div id="dsl_x_gender" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->gender->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->gender->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
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
	</span>
<?php } ?>
</div>
<div id="xsr_2" class="ewRow">
<?php if ($users->have_car->Visible) { // have_car ?>
	<span id="xsc_have_car" class="ewCell">
		<span class="ewSearchCaption"><?php echo $users->have_car->FldCaption() ?></span>
		<span class="ewSearchOperator"><?php echo $Language->Phrase("=") ?><input type="hidden" name="z_have_car" id="z_have_car" value="="></span>
		<span class="control-group ewSearchField">
<div id="tp_x_have_car" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_have_car" id="x_have_car" value="{value}"<?php echo $users->have_car->EditAttributes() ?>></div>
<div id="dsl_x_have_car" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->have_car->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->have_car->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
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
	</span>
<?php } ?>
</div>
<div id="xsr_3" class="ewRow">
<?php if ($users->type->Visible) { // type ?>
	<span id="xsc_type" class="ewCell">
		<span class="ewSearchCaption"><?php echo $users->type->FldCaption() ?></span>
		<span class="ewSearchOperator"><?php echo $Language->Phrase("=") ?><input type="hidden" name="z_type" id="z_type" value="="></span>
		<span class="control-group ewSearchField">
<div id="tp_x_type" class="<?php echo EW_ITEM_TEMPLATE_CLASSNAME ?>"><input type="radio" name="x_type" id="x_type" value="{value}"<?php echo $users->type->EditAttributes() ?>></div>
<div id="dsl_x_type" data-repeatcolumn="5" class="ewItemList">
<?php
$arwrk = $users->type->EditValue;
if (is_array($arwrk)) {
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($users->type->AdvancedSearch->SearchValue) == strval($arwrk[$rowcntwrk][0])) ? " checked=\"checked\"" : "";
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
	</span>
<?php } ?>
</div>
<div id="xsr_4" class="ewRow">
	<div class="btn-group ewButtonGroup">
	<div class="input-append">
	<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" class="input-large" value="<?php echo ew_HtmlEncode($users_list->BasicSearch->getKeyword()) ?>" placeholder="<?php echo $Language->Phrase("Search") ?>">
	<button class="btn btn-primary ewButton" name="btnsubmit" id="btnsubmit" type="submit"><?php echo $Language->Phrase("QuickSearchBtn") ?></button>
	</div>
	</div>
	<div class="btn-group ewButtonGroup">
	<a class="btn ewShowAll" href="<?php echo $users_list->PageUrl() ?>cmd=reset"><?php echo $Language->Phrase("ShowAll") ?></a>
	</div>
</div>
<div id="xsr_5" class="ewRow">
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="="<?php if ($users_list->BasicSearch->getType() == "=") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("ExactPhrase") ?></label>
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND"<?php if ($users_list->BasicSearch->getType() == "AND") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AllWord") ?></label>
	<label class="inline radio ewRadio" style="white-space: nowrap;"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR"<?php if ($users_list->BasicSearch->getType() == "OR") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AnyWord") ?></label>
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
<?php $users_list->ShowPageHeader(); ?>
<?php
$users_list->ShowMessage();
?>
<table class="ewGrid"><tr><td class="ewGridContent">
<form name="fuserslist" id="fuserslist" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="users">
<div id="gmp_users" class="ewGridMiddlePanel">
<?php if ($users_list->TotalRecs > 0) { ?>
<table id="tbl_userslist" class="ewTable ewTableSeparate">
<?php echo $users->TableCustomInnerHtml ?>
<thead><!-- Table header -->
	<tr class="ewTableHeader">
<?php

// Render list options
$users_list->RenderListOptions();

// Render list options (header, left)
$users_list->ListOptions->Render("header", "left");
?>
<?php if ($users->id->Visible) { // id ?>
	<?php if ($users->SortUrl($users->id) == "") { ?>
		<td><div id="elh_users_id" class="users_id"><div class="ewTableHeaderCaption"><?php echo $users->id->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->id) ?>',1);"><div id="elh_users_id" class="users_id">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->id->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->id->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->id->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->username->Visible) { // username ?>
	<?php if ($users->SortUrl($users->username) == "") { ?>
		<td><div id="elh_users_username" class="users_username"><div class="ewTableHeaderCaption"><?php echo $users->username->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->username) ?>',1);"><div id="elh_users_username" class="users_username">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->username->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->username->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->username->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->phone->Visible) { // phone ?>
	<?php if ($users->SortUrl($users->phone) == "") { ?>
		<td><div id="elh_users_phone" class="users_phone"><div class="ewTableHeaderCaption"><?php echo $users->phone->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->phone) ?>',1);"><div id="elh_users_phone" class="users_phone">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->phone->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->phone->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->phone->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->password->Visible) { // password ?>
	<?php if ($users->SortUrl($users->password) == "") { ?>
		<td><div id="elh_users_password" class="users_password"><div class="ewTableHeaderCaption"><?php echo $users->password->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->password) ?>',1);"><div id="elh_users_password" class="users_password">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->password->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->password->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->password->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->gender->Visible) { // gender ?>
	<?php if ($users->SortUrl($users->gender) == "") { ?>
		<td><div id="elh_users_gender" class="users_gender"><div class="ewTableHeaderCaption"><?php echo $users->gender->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->gender) ?>',1);"><div id="elh_users_gender" class="users_gender">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->gender->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->gender->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->gender->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->have_car->Visible) { // have_car ?>
	<?php if ($users->SortUrl($users->have_car) == "") { ?>
		<td><div id="elh_users_have_car" class="users_have_car"><div class="ewTableHeaderCaption"><?php echo $users->have_car->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->have_car) ?>',1);"><div id="elh_users_have_car" class="users_have_car">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->have_car->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->have_car->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->have_car->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->car_number->Visible) { // car_number ?>
	<?php if ($users->SortUrl($users->car_number) == "") { ?>
		<td><div id="elh_users_car_number" class="users_car_number"><div class="ewTableHeaderCaption"><?php echo $users->car_number->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->car_number) ?>',1);"><div id="elh_users_car_number" class="users_car_number">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->car_number->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->car_number->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->car_number->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->car_model->Visible) { // car_model ?>
	<?php if ($users->SortUrl($users->car_model) == "") { ?>
		<td><div id="elh_users_car_model" class="users_car_model"><div class="ewTableHeaderCaption"><?php echo $users->car_model->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->car_model) ?>',1);"><div id="elh_users_car_model" class="users_car_model">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->car_model->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->car_model->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->car_model->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->car_color->Visible) { // car_color ?>
	<?php if ($users->SortUrl($users->car_color) == "") { ?>
		<td><div id="elh_users_car_color" class="users_car_color"><div class="ewTableHeaderCaption"><?php echo $users->car_color->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->car_color) ?>',1);"><div id="elh_users_car_color" class="users_car_color">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->car_color->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->car_color->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->car_color->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->type->Visible) { // type ?>
	<?php if ($users->SortUrl($users->type) == "") { ?>
		<td><div id="elh_users_type" class="users_type"><div class="ewTableHeaderCaption"><?php echo $users->type->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->type) ?>',1);"><div id="elh_users_type" class="users_type">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->type->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->type->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->type->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->img->Visible) { // img ?>
	<?php if ($users->SortUrl($users->img) == "") { ?>
		<td><div id="elh_users_img" class="users_img"><div class="ewTableHeaderCaption"><?php echo $users->img->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->img) ?>',1);"><div id="elh_users_img" class="users_img">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->img->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->img->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->img->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->remember_token->Visible) { // remember_token ?>
	<?php if ($users->SortUrl($users->remember_token) == "") { ?>
		<td><div id="elh_users_remember_token" class="users_remember_token"><div class="ewTableHeaderCaption"><?php echo $users->remember_token->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->remember_token) ?>',1);"><div id="elh_users_remember_token" class="users_remember_token">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->remember_token->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></span><span class="ewTableHeaderSort"><?php if ($users->remember_token->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->remember_token->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->created_at->Visible) { // created_at ?>
	<?php if ($users->SortUrl($users->created_at) == "") { ?>
		<td><div id="elh_users_created_at" class="users_created_at"><div class="ewTableHeaderCaption"><?php echo $users->created_at->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->created_at) ?>',1);"><div id="elh_users_created_at" class="users_created_at">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->created_at->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->created_at->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->created_at->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php if ($users->updated_at->Visible) { // updated_at ?>
	<?php if ($users->SortUrl($users->updated_at) == "") { ?>
		<td><div id="elh_users_updated_at" class="users_updated_at"><div class="ewTableHeaderCaption"><?php echo $users->updated_at->FldCaption() ?></div></div></td>
	<?php } else { ?>
		<td><div class="ewPointer" onclick="ew_Sort(event,'<?php echo $users->SortUrl($users->updated_at) ?>',1);"><div id="elh_users_updated_at" class="users_updated_at">
			<div class="ewTableHeaderBtn"><span class="ewTableHeaderCaption"><?php echo $users->updated_at->FldCaption() ?></span><span class="ewTableHeaderSort"><?php if ($users->updated_at->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($users->updated_at->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span></div>
        </div></div></td>
	<?php } ?>
<?php } ?>		
<?php

// Render list options (header, right)
$users_list->ListOptions->Render("header", "right");
?>
	</tr>
</thead>
<tbody>
<?php
if ($users->ExportAll && $users->Export <> "") {
	$users_list->StopRec = $users_list->TotalRecs;
} else {

	// Set the last record to display
	if ($users_list->TotalRecs > $users_list->StartRec + $users_list->DisplayRecs - 1)
		$users_list->StopRec = $users_list->StartRec + $users_list->DisplayRecs - 1;
	else
		$users_list->StopRec = $users_list->TotalRecs;
}
$users_list->RecCnt = $users_list->StartRec - 1;
if ($users_list->Recordset && !$users_list->Recordset->EOF) {
	$users_list->Recordset->MoveFirst();
	if (!$bSelectLimit && $users_list->StartRec > 1)
		$users_list->Recordset->Move($users_list->StartRec - 1);
} elseif (!$users->AllowAddDeleteRow && $users_list->StopRec == 0) {
	$users_list->StopRec = $users->GridAddRowCount;
}

// Initialize aggregate
$users->RowType = EW_ROWTYPE_AGGREGATEINIT;
$users->ResetAttrs();
$users_list->RenderRow();
while ($users_list->RecCnt < $users_list->StopRec) {
	$users_list->RecCnt++;
	if (intval($users_list->RecCnt) >= intval($users_list->StartRec)) {
		$users_list->RowCnt++;

		// Set up key count
		$users_list->KeyCount = $users_list->RowIndex;

		// Init row class and style
		$users->ResetAttrs();
		$users->CssClass = "";
		if ($users->CurrentAction == "gridadd") {
		} else {
			$users_list->LoadRowValues($users_list->Recordset); // Load row values
		}
		$users->RowType = EW_ROWTYPE_VIEW; // Render view

		// Set up row id / data-rowindex
		$users->RowAttrs = array_merge($users->RowAttrs, array('data-rowindex'=>$users_list->RowCnt, 'id'=>'r' . $users_list->RowCnt . '_users', 'data-rowtype'=>$users->RowType));

		// Render row
		$users_list->RenderRow();

		// Render list options
		$users_list->RenderListOptions();
?>
	<tr<?php echo $users->RowAttributes() ?>>
<?php

// Render list options (body, left)
$users_list->ListOptions->Render("body", "left", $users_list->RowCnt);
?>
	<?php if ($users->id->Visible) { // id ?>
		<td<?php echo $users->id->CellAttributes() ?>>
<span<?php echo $users->id->ViewAttributes() ?>>
<?php echo $users->id->ListViewValue() ?></span>
<a id="<?php echo $users_list->PageObjName . "_row_" . $users_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($users->username->Visible) { // username ?>
		<td<?php echo $users->username->CellAttributes() ?>>
<span<?php echo $users->username->ViewAttributes() ?>>
<?php echo $users->username->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->phone->Visible) { // phone ?>
		<td<?php echo $users->phone->CellAttributes() ?>>
<span<?php echo $users->phone->ViewAttributes() ?>>
<?php echo $users->phone->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->password->Visible) { // password ?>
		<td<?php echo $users->password->CellAttributes() ?>>
<span<?php echo $users->password->ViewAttributes() ?>>
<?php echo $users->password->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->gender->Visible) { // gender ?>
		<td<?php echo $users->gender->CellAttributes() ?>>
<span<?php echo $users->gender->ViewAttributes() ?>>
<?php echo $users->gender->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->have_car->Visible) { // have_car ?>
		<td<?php echo $users->have_car->CellAttributes() ?>>
<span<?php echo $users->have_car->ViewAttributes() ?>>
<?php echo $users->have_car->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->car_number->Visible) { // car_number ?>
		<td<?php echo $users->car_number->CellAttributes() ?>>
<span<?php echo $users->car_number->ViewAttributes() ?>>
<?php echo $users->car_number->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->car_model->Visible) { // car_model ?>
		<td<?php echo $users->car_model->CellAttributes() ?>>
<span<?php echo $users->car_model->ViewAttributes() ?>>
<?php echo $users->car_model->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->car_color->Visible) { // car_color ?>
		<td<?php echo $users->car_color->CellAttributes() ?>>
<span<?php echo $users->car_color->ViewAttributes() ?>>
<?php echo $users->car_color->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->type->Visible) { // type ?>
		<td<?php echo $users->type->CellAttributes() ?>>
<span<?php echo $users->type->ViewAttributes() ?>>
<?php echo $users->type->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->img->Visible) { // img ?>
		<td<?php echo $users->img->CellAttributes() ?>>
<span>
<?php if (!ew_EmptyStr($users->img->ListViewValue())) { ?><img src="<?php echo $users->img->ListViewValue() ?>" alt=""<?php echo $users->img->ViewAttributes() ?>><?php } ?></span>
</td>
	<?php } ?>
	<?php if ($users->remember_token->Visible) { // remember_token ?>
		<td<?php echo $users->remember_token->CellAttributes() ?>>
<span<?php echo $users->remember_token->ViewAttributes() ?>>
<?php echo $users->remember_token->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->created_at->Visible) { // created_at ?>
		<td<?php echo $users->created_at->CellAttributes() ?>>
<span<?php echo $users->created_at->ViewAttributes() ?>>
<?php echo $users->created_at->ListViewValue() ?></span>
</td>
	<?php } ?>
	<?php if ($users->updated_at->Visible) { // updated_at ?>
		<td<?php echo $users->updated_at->CellAttributes() ?>>
<span<?php echo $users->updated_at->ViewAttributes() ?>>
<?php echo $users->updated_at->ListViewValue() ?></span>
</td>
	<?php } ?>
<?php

// Render list options (body, right)
$users_list->ListOptions->Render("body", "right", $users_list->RowCnt);
?>
	</tr>
<?php
	}
	if ($users->CurrentAction <> "gridadd")
		$users_list->Recordset->MoveNext();
}
?>
</tbody>
</table>
<?php } ?>
<?php if ($users->CurrentAction == "") { ?>
<input type="hidden" name="a_list" id="a_list" value="">
<?php } ?>
</div>
</form>
<?php

// Close recordset
if ($users_list->Recordset)
	$users_list->Recordset->Close();
?>
<div class="ewGridLowerPanel">
<?php if ($users->CurrentAction <> "gridadd" && $users->CurrentAction <> "gridedit") { ?>
<form name="ewPagerForm" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>">
<table class="ewPager">
<tr><td>
<?php if (!isset($users_list->Pager)) $users_list->Pager = new cPrevNextPager($users_list->StartRec, $users_list->DisplayRecs, $users_list->TotalRecs) ?>
<?php if ($users_list->Pager->RecordCount > 0) { ?>
<table class="ewStdTable"><tbody><tr><td>
	<?php echo $Language->Phrase("Page") ?>&nbsp;
<div class="input-prepend input-append">
<!--first page button-->
	<?php if ($users_list->Pager->FirstButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $users_list->PageUrl() ?>start=<?php echo $users_list->Pager->FirstButton->Start ?>"><i class="icon-step-backward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-step-backward"></i></a>
	<?php } ?>
<!--previous page button-->
	<?php if ($users_list->Pager->PrevButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $users_list->PageUrl() ?>start=<?php echo $users_list->Pager->PrevButton->Start ?>"><i class="icon-prev"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-prev"></i></a>
	<?php } ?>
<!--current page number-->
	<input class="input-mini" type="text" name="<?php echo EW_TABLE_PAGE_NO ?>" value="<?php echo $users_list->Pager->CurrentPage ?>">
<!--next page button-->
	<?php if ($users_list->Pager->NextButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $users_list->PageUrl() ?>start=<?php echo $users_list->Pager->NextButton->Start ?>"><i class="icon-play"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-play"></i></a>
	<?php } ?>
<!--last page button-->
	<?php if ($users_list->Pager->LastButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $users_list->PageUrl() ?>start=<?php echo $users_list->Pager->LastButton->Start ?>"><i class="icon-step-forward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small disabled"><i class="icon-step-forward"></i></a>
	<?php } ?>
</div>
	&nbsp;<?php echo $Language->Phrase("of") ?>&nbsp;<?php echo $users_list->Pager->PageCount ?>
</td>
<td>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $Language->Phrase("Record") ?>&nbsp;<?php echo $users_list->Pager->FromIndex ?>&nbsp;<?php echo $Language->Phrase("To") ?>&nbsp;<?php echo $users_list->Pager->ToIndex ?>&nbsp;<?php echo $Language->Phrase("Of") ?>&nbsp;<?php echo $users_list->Pager->RecordCount ?>
</td>
</tr></tbody></table>
<?php } else { ?>
	<?php if ($users_list->SearchWhere == "0=101") { ?>
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
	foreach ($users_list->OtherOptions as &$option)
		$option->Render("body", "bottom");
?>
</div>
</div>
</td></tr></table>
<script type="text/javascript">
fuserslistsrch.Init();
fuserslist.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$users_list->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$users_list->Page_Terminate();
?>
