<?

//

$char = 14;                                                                // charactors in the pass
$possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; // allowed charactors


//

echo "<table style='border: 1px solid #449944' cellpadding='1'><tr><td bgcolor='#449944'>
 <Font color='White' size='3'>14 Random alpha-numeric characters:</font>
</td><td><Font color='Black' face='Courier New, monospace, Arial, Helvetica, sans-serif' size='3'>
<input name='randnum1' type='Text' style='margin:1px; height:20px; border:solid 0 #fff; background-color: #FFFFFF; font-family: monospace; font-size: 18px; color: #000000;' size='14' maxlength='14' value='";
 generatePassword($char, $possible);
echo "' readonly></font></td></tr></table>";

function generatePassword ($length, $possible)
{
 if ($length == "" || !is_numeric($length)){
  $length = 8;
 }

 srand(make_seed());

 $i = 0;
 $password = "";
 while ($i < $length) {
  $char = substr($possible, rand(0, strlen($possible)-1), 1);
  if (!strstr($password, $char)) {
   $password .= $char;
   $i++;
   }
  }
 echo $password;
}

function make_seed()
{
 list($usec, $sec) = explode(' ', microtime());
 return (float) $sec + ((float) $usec * 100000);
}

?>