<?php
function getWordstat($method, $params = false)
{
	# формирование запроса    
	$request = array(
		'token'=> 'AgAAAAAFSkYoAAbL25qUQK6olENcsMoX17FbUi0', 
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

# Получаем данные с фронта
$valuesToArray = $_POST['inputInfo'];
$arrayWithWords = explode(PHP_EOL, $valuesToArray);

# Коцаем запросы на чанки по 3 - самое оптимальное значение
$splicedArray = array_chunk($arrayWithWords, 3);

$regionGot = $_POST['regionID']; 

# Отправляем запросы кусочками , вывод можно посмотреть в файле , выводится последовательно, каждые 6 секунд
for ($j = 0 ; $j < count($splicedArray); $j++){
	$request = getWordstat('CreateNewWordstatReport', array(
							'Phrases' => $splicedArray[$j],
							'GeoID' => array((int)$regionGot))
							);		

	# Отчет формируется, ждем, чем больше запросов, тем выше ставим время
	sleep(5);

	$result = getWordstat('GetWordstatReport', $request->data);

	getWordstat('DeleteWordstatReport', $request->data);

	// # Записываем информацию в файл
	$fp = fopen('lidn.csv', 'a');

	for ($i = 0; $i <= count($result->{data}) - 1; $i++) {
		$arrayToBeWritten = array(($result->data[$i])->Phrase , ((($result->data[$i])->SearchedWith)[0]->Shows));
		fputcsv($fp , $arrayToBeWritten);
	}

}

fclose($fp);


?>