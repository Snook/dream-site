<?php
require_once 'includes/payment/PayPalProcess.php';
require_once 'includes/CLog.inc';
require_once 'page/admin/order_details_view_all.php';

/**
 * buildFinishingTouchSuggestions() test case.
 */
class buildFinishingTouchSuggestionsTest extends PHPUnit_Framework_TestCase
{

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated buildFinishingTouchSuggestionsTest::setUp()
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated buildFinishingTouchSuggestionsTest::tearDown()
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
     * Tests buildFinishingTouchSuggestions()
     */
    public function testBuildFinishingTouchSuggestions()
    {
        // TODO Auto-generated buildFinishingTouchSuggestionsTest->testBuildFinishingTouchSuggestions
       // $this->markTestIncomplete("buildFinishingTouchSuggestions() test not implemented");


        $order = DAO_CFactory::create('orders');
        $order->id = 2741493;
        $order->find(true);


        $User =  DAO_CFactory::create('user');
        $User->id = 400252;
        $User->find(true);

        $order_info = COrders::buildOrderDetailArrays($User, $order);


        $result = page_admin_order_details_view_all::buildFinishingTouchSuggestions(172, 244, $order_info['menuInfo']);
    }
}

