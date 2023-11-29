<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/payment/PayPalProcess.php';
require_once 'includes/CLog.inc';

/**
 * PayPalProcess test case.
 */
class PayPalProcessTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var PayPalProcess
     */
    private $payPalProcess;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated PayPalProcessTest::setUp()

        $this->payPalProcess = new PayPalProcess(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated PayPalProcessTest::tearDown()
        $this->payPalProcess = null;

		parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests PayPalProcess->__construct()
     */
    public function test__construct()
    {
        // TODO Auto-generated PayPalProcessTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");

		$this->payPalProcess->__construct(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getResult()
     */
    public function testGetResult()
    {
        // TODO Auto-generated PayPalProcessTest->testGetResult()
        $this->markTestIncomplete("getResult test not implemented");

		$this->payPalProcess->getResult(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getRequest()
     */
    public function testGetRequest()
    {
        // TODO Auto-generated PayPalProcessTest->testGetRequest()
        $this->markTestIncomplete("getRequest test not implemented");

		$this->payPalProcess->getRequest(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getResponseMessage()
     */
    public function testGetResponseMessage()
    {
        // TODO Auto-generated PayPalProcessTest->testGetResponseMessage()
        $this->markTestIncomplete("getResponseMessage test not implemented");

		$this->payPalProcess->getResponseMessage(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getUsersExplanation()
     */
    public function testGetUsersExplanation()
    {
        // TODO Auto-generated PayPalProcessTest->testGetUsersExplanation()
        $this->markTestIncomplete("getUsersExplanation test not implemented");

		$this->payPalProcess->getUsersExplanation(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getMerchantAccountId()
     */
    public function testGetMerchantAccountId()
    {
        // TODO Auto-generated PayPalProcessTest->testGetMerchantAccountId()
        $this->markTestIncomplete("getMerchantAccountId test not implemented");

		$this->payPalProcess->getMerchantAccountId(/* parameters */);
    }

    /**
     * Tests PayPalProcess->getSystemName()
     */
    public function testGetSystemName()
    {
        // TODO Auto-generated PayPalProcessTest->testGetSystemName()
        $this->markTestIncomplete("getSystemName test not implemented");

		$this->payPalProcess->getSystemName(/* parameters */);
    }

    /**
     * Tests PayPalProcess::getFullErrorDescription()
     */
    public function testGetFullErrorDescription()
	{
        $this->markTestIncomplete("getFullErrorDescription test not implemented");
		/*
		for ($x = 0; $x < 106; $x++)
		{
			$result = array('RESPMSG' => "placeholder", 'RESULT' => $x);
			PayPalProcess::getFullErrorDescription($result, 244);
		}
		*/
    }

    /**
     * Tests PayPalProcess->getPNRef()
     */
    public function testGetPNRef()
    {
        // TODO Auto-generated PayPalProcessTest->testGetPNRef()
        $this->markTestIncomplete("getPNRef test not implemented");

		$this->payPalProcess->getPNRef(/* parameters */);
    }

    /**
     * Tests PayPalProcess::scrubUserData()
     */
    public function testScrubUserData()
    {
        // TODO Auto-generated PayPalProcessTest::testScrubUserData()
        $this->markTestIncomplete("scrubUserData test not implemented");

		PayPalProcess::scrubUserData(/* parameters */);
    }

    /**
     * Tests PayPalProcess::encodeCommentPayload()
     */
    public function testEncodeCommentPayload()
    {
        // TODO Auto-generated PayPalProcessTest::testEncodeCommentPayload()
        $this->markTestIncomplete("encodeCommentPayload test not implemented");

		PayPalProcess::encodeCommentPayload(/* parameters */);
    }

    /**
     * Tests PayPalProcess::decodeCommentPayload()
     */
    public function testDecodeCommentPayload()
    {
        // TODO Auto-generated PayPalProcessTest::testDecodeCommentPayload()
        $this->markTestIncomplete("decodeCommentPayload test not implemented");

		PayPalProcess::decodeCommentPayload(/* parameters */);
    }

    /**
     * Tests PayPalProcess::removeOffendingCharacters()
     */
    public function testRemoveOffendingCharacters()
    {
        // TODO Auto-generated PayPalProcessTest::testRemoveOffendingCharacters()
        $this->markTestIncomplete("removeOffendingCharacters test not implemented");

		PayPalProcess::removeOffendingCharacters(/* parameters */);
    }

    /**
     * Tests PayPalProcess::getSecureToken()
     */
    public function testGetSecureToken()
    {
        $result = PayPalProcess::getSecureToken(244, "Carl E Samuelson", "98021", 1.0);
    }

    /**
     * Tests PayPalProcess->processPayment()
     */
    public function testProcessPayment()
    {
        // TODO Auto-generated PayPalProcessTest->testProcessPayment()
        $this->markTestIncomplete("processPayment test not implemented");

		$this->payPalProcess->processPayment(/* parameters */);
    }

    /**
     * Tests PayPalProcess->processGiftCardOrder()
     */
    public function testProcessGiftCardOrder()
    {
        // TODO Auto-generated PayPalProcessTest->testProcessGiftCardOrder()
        $this->markTestIncomplete("processGiftCardOrder test not implemented");

		$this->payPalProcess->processGiftCardOrder(/* parameters */);
    }

    /**
     * Tests PayPalProcess->payFlowTest()
     */
    public function testPayFlowTest()
    {
        // TODO Auto-generated PayPalProcessTest->testPayFlowTest()
        $this->markTestIncomplete("payFlowTest test not implemented");

		$this->payPalProcess->payFlowTest(/* parameters */);
    }
}