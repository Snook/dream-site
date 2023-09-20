<?php
require_once 'includes/api/ApiManager.php';

class MockAvalaraTaxManager extends ApiManager
{
	// Hold the class instance for the singleton
	/**
	 * @var null
	 */
	private static $instance = null;

	// API connection information
	private $endpoint;
	private $apiKey;
	private $apiSecret;
	private $authorization;

	// methods available to handle
	private $methodsPaths;

	public function __construct()
	{
		parent::__construct("AvalaraTaxApi");

	}

	private function createMockJsonTaxRateResponse($avalaraTaxRateWrapper)
	{

		$jsonResponseTemplate = '{"id":0,"code":"b47bb5dc-b42d-49d8-8bf0-d1ebecda4dad","companyId":73555,"date":"'.date('Y-m-d').'","paymentDate":"'.date('Y-m-d').'","status":"Temporary","type":"SalesOrder","batchCode":"","currencyCode":"USD","exchangeRateCurrencyCode":"USD","customerUsageType":"","entityUseCode":"","customerVendorCode":"696969","customerCode":"696969","exemptNo":"","reconciled":false,"locationCode":"","reportingLocationCode":"","purchaseOrderNo":"","referenceCode":"","salespersonCode":"","totalAmount":99.0,"totalExempt":0.0,"totalDiscount":0.0,"totalTax":2.23,"totalTaxable":99.0,"totalTaxCalculated":2.23,"adjustmentReason":"NotAdjusted","locked":false,"version":1,"exchangeRateEffectiveDate":"'.date('Y-m-d').'","exchangeRate":1.0,"modifiedDate":"'.date('Y-m-d').'T19:06:26.5718369Z","modifiedUserId":71729,"taxDate":"'.date('Y-m-d').'","lines":[{"id":0,"transactionId":0,"lineNumber":"1","customerUsageType":"","entityUseCode":"","description":"Food Cost Order ID # ","discountAmount":0.0,"exemptAmount":0.0,"exemptCertId":0,"exemptNo":"","isItemTaxable":true,"itemCode":"","lineAmount":99.0,"quantity":1.0,"ref1":"","ref2":"","reportingDate":"2021-08-07","tax":2.23,"taxableAmount":99.0,"taxCalculated":2.23,"taxCode":"PF160022","taxCodeId":29044,"taxDate":"2021-08-07","taxIncluded":false,"details":[{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"17","jurisName":"ILLINOIS","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"STA","jurisdictionType":"State","nonTaxableAmount":0.0,"rate":0.010000,"tax":0.99,"taxableAmount":99.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL STATE TAX","taxAuthorityTypeId":45,"taxCalculated":0.99,"rateType":"Food","rateTypeCode":"F","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":99.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.99,"reportingTaxCalculated":0.99,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"031","jurisName":"COOK","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"CTY","jurisdictionType":"County","nonTaxableAmount":0.0,"rate":0.000000,"tax":0.0,"taxableAmount":99.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL COUNTY TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"Food","rateTypeCode":"F","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":99.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"ARAV0","jurisName":"CHICAGO","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"CIT","jurisdictionType":"City","nonTaxableAmount":0.0,"rate":0.000000,"tax":0.0,"taxableAmount":99.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL CITY TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"Food","rateTypeCode":"F","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":99.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"AQOF","jurisName":"REGIONAL TRANSPORT. AUTHORITY (RTA)","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"STJ","jurisdictionType":"Special","nonTaxableAmount":0.0,"rate":0.012500,"tax":1.24,"taxableAmount":99.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL SPECIAL TAX","taxAuthorityTypeId":45,"taxCalculated":1.24,"rateType":"Food","rateTypeCode":"F","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":99.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":1.24,"reportingTaxCalculated":1.24,"liabilityType":"Seller"}],"nonPassthroughDetails":[],"hsCode":"","costInsuranceFreight":0.0,"vatCode":"","vatNumberTypeId":0},{"id":0,"transactionId":0,"lineNumber":"2","customerUsageType":"","entityUseCode":"","description":"Delivery Fee Order ID # ","discountAmount":0.0,"exemptAmount":0.0,"exemptCertId":0,"exemptNo":"","isItemTaxable":false,"itemCode":"","lineAmount":0.0,"quantity":1.0,"ref1":"","ref2":"","reportingDate":"2021-08-07","tax":0.0,"taxableAmount":0.0,"taxCalculated":0.0,"taxCode":"FR000000","taxCodeId":4779,"taxDate":"2021-08-07","taxIncluded":false,"details":[{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"17","jurisName":"ILLINOIS","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"STA","jurisdictionType":"State","nonTaxableAmount":0.0,"rate":0.062500,"tax":0.0,"taxableAmount":0.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL STATE TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"General","rateTypeCode":"G","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":0.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"031","jurisName":"COOK","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"CTY","jurisdictionType":"County","nonTaxableAmount":0.0,"rate":0.017500,"tax":0.0,"taxableAmount":0.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL COUNTY TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"General","rateTypeCode":"G","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":0.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"ARAV0","jurisName":"CHICAGO","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"CIT","jurisdictionType":"City","nonTaxableAmount":0.0,"rate":0.012500,"tax":0.0,"taxableAmount":0.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL CITY TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"General","rateTypeCode":"G","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":0.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"},{"id":0,"transactionLineId":0,"transactionId":0,"country":"US","region":"IL","exemptAmount":0.0,"jurisCode":"AQOF","jurisName":"REGIONAL TRANSPORT. AUTHORITY (RTA)","stateAssignedNo":"016-0001-1 CHICAGO","jurisType":"STJ","jurisdictionType":"Special","nonTaxableAmount":0.0,"rate":0.010000,"tax":0.0,"taxableAmount":0.0,"taxType":"Sales","taxSubTypeId":"S","taxName":"IL SPECIAL TAX","taxAuthorityTypeId":45,"taxCalculated":0.0,"rateType":"General","rateTypeCode":"G","unitOfBasis":"PerCurrencyUnit","isNonPassThru":false,"isFee":false,"reportingTaxableUnits":0.0,"reportingNonTaxableUnits":0.0,"reportingExemptUnits":0.0,"reportingTax":0.0,"reportingTaxCalculated":0.0,"liabilityType":"Seller"}],"nonPassthroughDetails":[],"hsCode":"","costInsuranceFreight":0.0,"vatCode":"","vatNumberTypeId":0}],"addresses":[{"id":0,"transactionId":0,"boundaryLevel":"Address","line1":"1510 N. Wood St.","line2":"","line3":"","city":"Chicago","region":"IL","postalCode":"60622","country":"US","taxRegionId":4025922,"latitude":"41.909076","longitude":"-87.672638"}],"summary":[{"country":"US","region":"IL","jurisType":"State","jurisCode":"17","jurisName":"ILLINOIS","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL STATE TAX","rateType":"Food","taxable":99.0,"rate":0.010000,"tax":0.99,"taxCalculated":0.99,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"State","jurisCode":"17","jurisName":"ILLINOIS","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL STATE TAX","rateType":"General","taxable":0.0,"rate":0.062500,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"County","jurisCode":"031","jurisName":"COOK","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL COUNTY TAX","rateType":"Food","taxable":99.0,"rate":0.000000,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"County","jurisCode":"031","jurisName":"COOK","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL COUNTY TAX","rateType":"General","taxable":0.0,"rate":0.017500,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"City","jurisCode":"ARAV0","jurisName":"CHICAGO","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL CITY TAX","rateType":"Food","taxable":99.0,"rate":0.000000,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"City","jurisCode":"ARAV0","jurisName":"CHICAGO","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL CITY TAX","rateType":"General","taxable":0.0,"rate":0.012500,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"Special","jurisCode":"AQOF","jurisName":"REGIONAL TRANSPORT. AUTHORITY (RTA)","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL SPECIAL TAX","rateType":"General","taxable":0.0,"rate":0.010000,"tax":0.0,"taxCalculated":0.0,"nonTaxable":0.0,"exemption":0.0},{"country":"US","region":"IL","jurisType":"Special","jurisCode":"AQOF","jurisName":"REGIONAL TRANSPORT. AUTHORITY (RTA)","taxAuthorityType":45,"stateAssignedNo":"016-0001-1 CHICAGO","taxType":"Sales","taxSubType":"S","taxName":"IL SPECIAL TAX","rateType":"Food","taxable":99.0,"rate":0.012500,"tax":1.24,"taxCalculated":1.24,"nonTaxable":0.0,"exemption":0.0}]}';


		$jsonObj = $avalaraTaxRateWrapper->jsonDecoder($jsonResponseTemplate);

		$grandTotal = $avalaraTaxRateWrapper->getOrderTotal();
		$orderState = strtolower($avalaraTaxRateWrapper->getTaxAddress()->state_id);
		$orderState = 'aa';
		$f = (ord(substr($orderState, 0,1))-96) * 10;
		$s = (ord(substr($orderState, 1,1))-96) + $f;
		if($s < 100){
			$s = $s + 50;
		}
		$taxPercent = $s / 100 / 50;
		$totalTax = $grandTotal * $taxPercent;

		$totalTax = round($totalTax, 2);
		$jsonObj->totalAmount = $grandTotal;
		$jsonObj->totalTax = $totalTax;
		$jsonObj->totalTaxable = $grandTotal;
		$jsonObj->totalTaxCalculated = $totalTax;
		$jsonObj->lines[0]->tax = $totalTax;
		$jsonObj->lines[0]->taxableAmount = $grandTotal;
		$jsonObj->lines[0]->totalTaxCalculated = $totalTax;

		return json_encode($jsonObj);
	}


	//public function fetch_tax($price, $address1, $address2, $city, $state, $zipcode, $country, $taxType){
	public function getTaxRates($avalaraTaxRateWrapper)
	{

		if ($avalaraTaxRateWrapper->isCached())
		{
			return $avalaraTaxRateWrapper->restoreFromCache();
		}

		$this->cleanNulls($avalaraTaxRateWrapper);

		$jsonRateResults = $this->createMockJsonTaxRateResponse($avalaraTaxRateWrapper);

		if ($jsonRateResults != false)
		{
			$avalaraTaxRateWrapper->populateRateFromJson($jsonRateResults);
			$avalaraTaxRateWrapper->cacheData();
			return $avalaraTaxRateWrapper;
		}

		//there was an error
		return false;
	}

	public function resolveAddress($filters)
	{
		$this->enforceApiRateLimit();

		$this->cleanNulls($filters);

		$filters = http_build_query($filters, '', '&');

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['resolveAddress'] . '?' . $filters );

		return $this->processReply($response);
	}

	public function pingService()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['ping']);

		return $this->processReply($response);
	}

	//    function enforceApiRateLimit(){
	//        //API specific Impl
	//    }

	function getAuthorization()
	{
		return $this->authorization;
	}
}