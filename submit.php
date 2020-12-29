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

# Номера регионов в хтмле 
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
# Отчет формируется, ждем, чем больше запросов, тем выше ставим время
sleep(15);

$result = getWordstat('GetWordstatReport', $request->data);

getWordstat('DeleteWordstatReport', $request->data);

# Обрабатываем отчет

print_r(($result->data[0])->SearchedWith);

# Записываем информацию в файл
$fp = fopen('lidn.csv', 'w');

for ($i = 0; $i <= count($result->{data}) - 1; $i++) {
	$arrayToBeWritten = array(($result->data[$i])->Phrase , ((($result->data[$i])->SearchedWith)[0]->Shows));
	
	 fputcsv($fp , $arrayToBeWritten);
	 print_r($arrayToBeWritten);
}



fclose($fp);


?>
