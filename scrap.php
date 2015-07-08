<?php

// resolve file name
if(!is_dir('./out/')) mkdir('./out/');
$i=1; $outdir=opendir("./out");
while ($file = readdir($outdir)) if(strstr($file, '.csv') == TRUE) $i++; 
closedir($outdir); $outf = './out/res_'.$i.'.csv';

// functions
function get_str($start, $end, $str){
$str = substr($str,strpos($str, $start)+strlen($start),strlen($str));     
return trim(substr($str,0,strpos($str,$end)));}
function strp_tags($str){return trim(strip_tags($str));}
//

function str_clean($str){

$str = preg_replace('/[^(\x20-\x7F)]*/','', $str);

return trim($str);}

// some vars
$eol = PHP_EOL; $cs = ','; $col = 17;
for($v=1;$v<$col;$v++){$str[$v] = '';}

$out2 = fopen($outf,"a"); fwrite($out2, 'Lid, Address, City, State, Zip, Type, Status, Bed, Bath, Price, Financing, Link, Image, Latitude, Longitude, GMap '.$eol);

// array of states to scrape
//$state = array("AL","AK","AZ","AR","CA","CO","CT","DE","DC","FL","GA","GU","HI","ID","IL","IN","IA","KS","KY","LA","ME","MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR","PA","PR","RI","SC","SD","TN","TX","UT","VT","VA","WA","WV","WI","WY","VI");
//$state = array("AL","AK","AZ","AR","CA","CO","CT","DE","DC","FL","GA","GU","HI","ID","IL","IN","IA","KS","KY","LA");
$state = array("AL");

//shell_exec('curl -A "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36" --compressed -k -o all.txt "https://www.homepath.com/listing/search/ui/event?uri="');

$out2 = fopen($outf,"a");

for($i=0; $i<sizeof($state); $i++){

echo PHP_EOL."State: ".$state[$i].$eol;

$lp = 200000;
for($b=1; $b<$lp; $b++){

$link1 = 'https://www.homepath.com/listings/'.$state[$i].'_st/list_v/'.$b.'_p';

echo $link1.$eol;

@unlink('file1.txt');
shell_exec('curl -A "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36" --compressed -k -o file1.txt "'.$link1.'"');

$fct2 = file_get_contents('file1.txt');
$fct2 = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $fct2);

$map = get_str("var markersData = [",'</script>',$fct2);

//if(strstr($fct2,'disabled">Next<')) $lp = 0; 
if(strstr($fct2,'There are no properties found')) $lp = 0; 
if(strstr($fct2,'Page Cannot Be Displayed')) $lp = 0;

$line2 = explode("\n",$fct2);

for($a=0; $a<count($line2); $a++){
$linea = $line2[$a];

if(strstr($linea, '<div class="odd card-frame"')){
$str[1] = get_str('id="record_','"',$linea);
}

if(strstr($linea, '<div class="even card-frame"')){
$str[1] = get_str('id="record_','"',$linea);
}

if(strstr($linea, '<div class="address">')){
$str[2] = strp_tags($linea);
}

if(strstr($linea, '<span class="city">')){
$str[3] = get_str('<span class="city">',',',$linea);
$str[4] = get_str(', ','<',$linea);
$str[5] = get_str('</span> ','<',$linea);
}

if(strstr($linea, '<span class="propertyType" data-propertytype="')){
$str[6] = get_str('<span class="propertyType" data-propertytype="','"',$linea);
}

if(strstr($linea, '<span class="homeStatus" data-homestatus="')){
$str[7] = get_str('<span class="homeStatus" data-homestatus="','"',$linea);
}

if(strstr($linea, '<div class="label">Beds</div>')){
$str[8] = str_replace('-','',strp_tags($line2[$a-2]));
}

if(strstr($linea, '<div class="label">Baths</div>')){
$str[9] = str_replace('-','',strp_tags($line2[$a-2]));
}

if(strstr($linea, '<div class="price">')){
$str[10] = str_replace(',',' ',strp_tags($line2[$a+1]));
}

$str[12] = 'http://www.homepath.com/listing?listingid='.$str[1];

if(strstr($linea, '<div class="active item">')){
$str[13] = 'http:'.get_str('src="','"',$line2[$a+1]);
}

if(strstr($map, 'id:"'.$str[1].'"')){
$str[14] = get_str('id:"'.$str[1].'"','}',$map);
$str[15] = get_str('lat: ','#',$str[14].'#');
$str[14] = get_str('lon: ',',',$str[14]);
}

$str[16] = 'http://maps.google.pt/maps?hl=en-EN&q='.$str[14].'%2C'.$str[15];

if(strstr($linea, '<span class="savedListing')){
$cnt++;
for($v1=1;$v1<$col;$v1++){$strout1 .= $str[$v1].$cs.' ';}
fwrite($out2, $strout1.$eol);
for($v=1;$v<$col;$v++){$str[$v] = '';} $strout1 = '';
}


} // a

} // b

} // i

copy($outf,'fnma.csv');

?>
