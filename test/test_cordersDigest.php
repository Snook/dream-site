<?php

require_once("../includes/Config.inc");

require_once 'includes/DAO/BusinessObject/COrdersDigest.php';



//echo COrdersDigest::determineTotalBoxesOrdered(907086);


//echo COrdersDigest::getUserStateAtOrderTime(914732, 3700489, '2022-02-09 13:42:34', 220);



//echo COrdersDigest::recordCanceledOrder(3700486, 200, 246, 275.5);


echo COrdersDigest::recordRescheduledOrder(3700485, '2022-02-23 16:15:00', 200, 913848,  246,'STANDARD')


?>