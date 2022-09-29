<?php

$a =<<<HTML
a:2:{s:6:"Anjola";a:2:{i:0;a:6:{i:0;s:6:"Anjola";i:1;s:3:"Joe";i:2;s:47:"<b> <a href=''>Anjola</a>:</b> <br>Trying notif";i:3;s:8:"40064898";i:4;s:0:"";i:5;s:6:"unseen";}i:1;a:6:{i:0;s:6:"Anjola";i:1;s:3:"Joe";i:2;s:47:"<b> <a href=''>[Anjola]</a>:</b> <br>Who cares!";i:3;s:8:"72397704";i:4;s:59:"    <b> <a href="">Joe</a>:</b> <br>Joe sending message    ";i:5;s:6:"unseen";}}s:6:"TestGC";a:3:{i:0;a:6:{i:0;s:6:"TestGC";i:1;s:3:"Joe";i:2;s:53:"<b> <a href=''>Anjola</a>:</b> <br>Werey ni e mehn|||";i:3;s:8:"66070342";i:4;s:49:"    <b> <a href="">[Joe]</a>:</b> <br>Yahhhhh    ";i:5;s:6:"unseen";}i:1;a:6:{i:0;s:6:"TestGC";i:1;s:3:"Joe";i:2;s:49:"<b> <a href=''>[Anjola]</a>:</b> <br>Shut up abeg";i:3;s:8:"63268733";i:4;s:49:"    <b> <a href="">[Joe]</a>:</b> <br>Yahhhhh    ";i:5;s:6:"unseen";}i:2;a:6:{i:0;s:6:"TestGC";i:1;s:3:"Joe";i:2;s:49:"<b> <a href=''>[Anjola]</a>:</b> <br>I'm good tho";i:3;s:8:"53033296";i:4;s:49:"    <b> <a href="">[Joe]</a>:</b> <br>Yahhhhh    ";i:5;s:6:"unseen";}}}
HTML;
$a='a:1:{s:6:"TestGC";a:5:{i:0;s:3:"Joe";i:1;s:6:"TestGC";i:2;s:2:"bb";i:3;s:6:"public";i:4;N;}}';
print_r(unserialize($a));
/*
$array = ["a"=>[1,2],"b"=>[1,2],"a"=>[3,4],"c"=>[5,5]];
print_r(array_unique($array));*/
?>