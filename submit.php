<?php
function getWordstat($method, $params = false)
{
	# формирование запроса    
	$request = array(
		'token'=> 'AgAAAAAQkWMBAAaDbvmPfAHRI0dcmci3iRTLw6s', 
		'method'=> $method,
		'param'=> is_array($params)? utf8($params):$params,
		'locale'=> 'ru',
	);
			 
	# преобразование в JSON-формат
	$request = json_encode($request);
	 
	# параметры запроса
	$opts = array(
		'http'=>array(
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n",	
			'method'=>"POST",
			'content'=>$request,
		)
	); 
	 
	# создание контекста потока
	$context = stream_context_create($opts); 
			 
	# отправляем запрос и получаем ответ от сервера
	$result = file_get_contents('https://api-sandbox.direct.yandex.ru/v4/json/', 0, $context);
	
	# Возвращаем ответ
	return json_decode($result);
}

# перекодировка строковых данных в UTF-8
function utf8($struct) {
	foreach ($struct as $key => $value) {
		if (is_array($value)) {
			$struct[$key] = utf8($value);
		}
		elseif (is_string($value)) {
			$struct[$key] = utf8_encode($value);
		}
	}
	return $struct;
}	

# Делаем запрос на получение статистики

# Nomera nujnih nam regionov
# 213 - Москва
# 215 - Дубна
# 225 - Russia

# getting input info

$valuesToArray = $_POST['inputInfo'];
$arrayWithWords = explode(PHP_EOL, $valuesToArray);

$regionGot = $_POST['regionID']; 

$request = getWordstat('CreateNewWordstatReport', array(
						'Phrases' => $arrayWithWords,
						'GeoID' => array((int)$regionGot))
						);		
# Отчет формируется
sleep(15);

# Получаем ответ в виде объекта
$result = getWordstat('GetWordstatReport', $request->data);

# Сразу удаляем этот отчет, дабы не забивать свой аккаунт. Т.к. максимум 5 отчетов можно.
getWordstat('DeleteWordstatReport', $request->data);

# Обрабатываем отчет как хотим. В данный момент просто выводим на экран.

print_r(($result->data[0])->SearchedWith);

$fp = fopen('lidn.txt', 'w');

for ($i = 0; $i <= count($result->{data}); $i++) {
  $strToBeWritten = ($result->data[$i])->Phrase . " " . ((($result->data[$i])->SearchedWith)[0]->Shows) . "\n";
  fwrite($fp , $strToBeWritten);
}



fclose($fp);
?>
