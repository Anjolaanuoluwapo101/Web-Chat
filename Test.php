<?php
$newArr =[];

//$a = [["Joe","Anjola","http://localhost:8000/programming%20project/Chat/View/channel.php?sender=Joe&receiver=Anjola&channel_type=private","private","lol",1,1664571281],["Joe","TestGC","http://localhost:8000/programming%20project/Chat/View/channel.php?sender=Joe&receiver=TestGC&channel_type=public","public","nznsns",0,1664492114]];
//$a =["anjola"=>[1,2,3,45],"ade"=>[5,6,6,7]];
$data=<<<HTML
<a href=''><b>ggggg</b></a> <bHTML;
HTML;

preg_match('/<b>(.*?)<\/b>/s',$data,$matches);
print_r($matches);
/*foreach ($a as $value) {
  // code...
  $newArr[$value[1]] = $value;
}
echo serialize($newArr);
//print_r($newArr)
*/
?>