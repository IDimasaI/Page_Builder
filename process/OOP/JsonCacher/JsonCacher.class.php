<?php
class JsonCacher
{
    private string $cacheFile; // Хешированное название json кеш БД файла
    private int $cacheTimeClient; // Время между проверками json клиента
    private int $cacheTimeBD; // Время между проверками БД сервера
    public $freshData; // Новые данные, если время обновления из БД пришло
    private $Type; // Возвращает тип для определения- использовать кеш или обновлять из БД 

    public function __construct(string $key,int $cacheTimeClient,int $cacheTimeBD)
    {
        $this->cacheFile = md5($key) . '.json'; // Создание хеш названия для json файла
        $this->cacheTimeClient = $cacheTimeClient;
        $this->cacheTimeBD = $cacheTimeBD;
    }

    private function CacheDataUpdate($cacheFile, $Data) // Перезаписывает кеш БД файла
    {
        $cacheContent = json_encode([
            'data' => $Data
        ]);
        file_put_contents($cacheFile, $cacheContent);
        return $cacheContent;
    }

    public function Type():string
    {
        return $this->Type;
    }

    private function create_json() // создание файла если такового нет. И потом надо ждать время обновления из БД
    {
        $jsonData = json_encode(['data' => "Данных еще нет, подождите $this->cacheTimeBD сек"]);
        file_put_contents($this->cacheFile, $jsonData);
        $this->Type = 'Non_create';
        return json_decode($jsonData, true);
    }

    public function loadJsonData()
    {
        if (file_exists($this->cacheFile)) {
            if ((time() - filemtime($this->cacheFile)) < $this->cacheTimeBD) { // Возвращает кеш файл, если время обновления БД еще не пришло.
                $jsonData = file_get_contents($this->cacheFile);
                $this->Type = 'Existing';
                return json_decode($jsonData, true);
            } else { // Использует БД для перезаписи кеш файла, если время пришло. И возвращает новые данные
                $this->Type = 'New';
                $Data = $this->freshData; // Задается в API $jsonCacher->freshData = {новые данные};
                $jsonData = $this->CacheDataUpdate($this->cacheFile, $Data);
                return json_decode($jsonData, true);
            }
        } else {
            $this->create_json();
        }
    }

    public function sendResponse(): array|string
    {
        $data = $this->loadJsonData();
        $lastModifiedTime = filemtime($this->cacheFile);
        $etag = md5(json_encode($data['data']));
        $cache_life = $this->cacheTimeClient;
        $expires = time() + $cache_life;
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModifiedTime) {
            // Кеш не изменился, возвращаем статус 304 Not Modified
            header('Cache-Control: public, max-age=' . $cache_life);
            header('HTTP/1.1 304 Not Modified');
            header('Update: No'); // Можно убрать если не используется
            exit;
        } else { // Возвращает новый json если время жизни клиентского кеша подошло к концу. И использование кеш БД для новых данных.
            header('Update: Yes'); // Можно убрать если не используется
            header('Expires: ' . gmdate('D, d M Y H:i:s', $expires) . ' GMT');
            header('Cache-Control: public, max-age=' . $cache_life);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT');
            header('Etag: ' . $etag);
            return $data;
        }
    }
}
