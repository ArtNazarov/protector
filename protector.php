<?php
// scripts ban user for 
// frequently requests
define("LOGGER", false);
define("HOST_DIR", __DIR__);
define("MAX_REQ", 3);
define("TIMEOUT_EXPIRE", 200);


function ipf($ip){
 return HOST_DIR . '/ips/'. $ip.'.txt';
}

function save_blacklist($ip){
   $f_blacklist = fopen(HOST_DIR . '/blacklist.txt', 'a+');
	 fwrite($f_blacklist, $ip . "\n\r");
	 fclose($f_blacklist);
}

function save_ip_stat($ip, $items){
  if (LOGGER){
  echo "сохраняем файл статистики<br>";
  };
  $data = implode('|', $items);
  $fh = fopen(ipf($ip), 'w');
  fwrite($fh, $data);
  fclose($fh);
}

function get_ip_stat($ip){
  if (LOGGER){
    echo "считываем файл статистики<br>";
  };
    $data = file_get_contents(  ipf( $ip ));
    $items = explode('|', $data);
    return $items;
}

function get_ms(){
$curTime = microtime(true);
return $curTime;  
}

function get_ms_diff($nowTime, $t){
return $nowTime - $t;
}

function get_ip(){

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $user_ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $user_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $user_ip_address = $_SERVER['REMOTE_ADDR'];
};
  return $user_ip_address;
}

function exists_ip($ip){
 return file_exists( ipf($ip) );
}

function init_stat($ip){
   	$items = [ $ip, 1, get_ms() ]; 
    save_ip_stat($ip, $items);
}

function main(){

$ip = get_ip();
if (LOGGER){
echo "Запрос с адреса " . $ip . "<br/>";
};
if (!exists_ip($ip)){
  init_stat($ip);
}

if (exists_ip( $ip ) ){
  if (LOGGER){
  echo "файл найден";
  };
  $items = get_ip_stat($ip);
  
  $counter = $items[1];

   
  $time = $items[2];
  $nowTime = get_ms();
  
  
 
  


  if (LOGGER){
  echo "Опрошено раз " . $counter . "<br>";
  echo "Время " . $time . "<br>";
  echo "Сейчас " . $nowTime . "<br>";
  };
  
  $isStatOld = false;
  $ms_delay = get_ms_diff($nowTime, $time);
  //echo $ms_delay;
  if ($ms_delay>TIMEOUT_EXPIRE){
   if (LOGGER) { echo "Разница больше " . TIMEOUT_EXPIRE . "<br/>";};
		$isStatOld = true;
	};


  


  if (($counter>MAX_REQ) && (!$isStatOld))        {
	 echo "Временно заблокирован за превышение запросов";
	 save_blacklist($ip);
   save_ip_stat($ip, [$ip,  $counter+1, $time]); 
   
	 exit(0);
      }
  else
      {
        
	      if ($isStatOld) {
          if (LOGGER) {echo "Статистика устарела"; };
          init_stat($ip);
      } else
            {
   save_ip_stat($ip, [$ip, $counter+1,     get_ms()]);
        };
      }
      
      }
}

main();
?>

