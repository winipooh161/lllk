<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class YandexDiskService
{
    /**
     * @var string API URL
     */
    protected $apiUrl = 'https://cloud-api.yandex.net/v1/disk';
    
    /**
     * @var Client Guzzle HTTP client
     */
    protected $client;
    
    /**
     * @var string OAuth token
     */
    protected $token;
    
    /**
     * @var string Таймаут для запросов (в секундах)
     */
    protected $timeout = 15000; // Оставляем значение 15000

    /**
     * YandexDiskService constructor.
     */
    public function __construct()
    {
        $this->token = config('services.yandex_disk.token');
        
        // Инициализируем таймаут из конфигурации или используем значение по умолчанию
        $this->timeout = config('services.yandex_disk.timeout', $this->timeout);
        
        if (empty($this->token)) {
            Log::error('Токен для Яндекс.Диск не настроен в конфигурации');
        }
        
        $this->initClient();
    }
    
    /**
     * Инициализирует HTTP клиент с текущими настройками
     */
    protected function initClient()
    {
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'OAuth ' . $this->token,
                'Accept' => 'application/json',
            ],
            'timeout' => $this->timeout,
            'connect_timeout' => 10, // Таймаут для соединения
        ]);
    }
    
    /**
     * Устанавливает таймаут для HTTP-запросов.
     *
     * @param int $seconds
     */
    public function setTimeout(int $seconds)
    {
        $this->timeout = $seconds;
        $this->initClient();
    }
    
    /**
     * Проверяет работоспособность токена и доступность API
     * 
     * @return bool Возвращает true, если авторизация работает корректно
     */
    public function checkAuth()
    {
        try {
            $response = $this->client->get($this->apiUrl);
            return $response->getStatusCode() == 200;
        } catch (ClientException $e) {
            if ($e->getCode() == 401) {
                Log::error('Ошибка авторизации в Яндекс.Диск. Токен недействителен или истек.', [
                    'token_prefix' => substr($this->token, 0, 5) . '...'
                ]);
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке токена Яндекс.Диск', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Create directory on Yandex Disk, ensuring all parent directories exist
     *
     * @param string $path Path to directory
     * @return bool Whether directory was created successfully
     */
    public function createDirectory($path)
    {
        try {
            if (empty($path)) {
                Log::error('Пустой путь для создания директории');
                return false;
            }
            
            // Проверяем, работает ли авторизация
            if (!$this->checkAuth()) {
                Log::error('Ошибка авторизации при создании директории', [
                    'path' => $path
                ]);
                return false;
            }
            
            // Проверяем существование директории
            $checkResponse = $this->client->get($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            // Если директория уже существует, возвращаем true
            if ($checkResponse->getStatusCode() == 200) {
                Log::info("Директория уже существует: {$path}");
                return true;
            }
            
            // Если ошибка не 404 (Not Found), что-то пошло не так
            if ($checkResponse->getStatusCode() != 404) {
                Log::error("Неожиданная ошибка при проверке директории: {$path}", [
                    'status' => $checkResponse->getStatusCode(),
                    'body' => (string) $checkResponse->getBody()
                ]);
                return false;
            }
            
            // Создаем родительские директории рекурсивно
            $parentDir = dirname($path);
            // Если путь содержит родительскую директорию и это не корень
            if ($parentDir && $parentDir !== '.' && $parentDir !== '/') {
                $parentCreated = $this->createDirectory($parentDir);
                if (!$parentCreated) {
                    Log::error("Не удалось создать родительскую директорию: {$parentDir}");
                    return false;
                }
            }
            
            // Создаем директорию
            Log::info("Создание директории: {$path}");
            $response = $this->client->put($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            // 201 - Created, 409 - Already exists
            $success = $response->getStatusCode() == 201 || $response->getStatusCode() == 409;
            
            if (!$success) {
                Log::error("Не удалось создать директорию: {$path}", [
                    'status' => $response->getStatusCode(),
                    'body' => (string) $response->getBody()
                ]);
            } else {
                Log::info("Директория успешно создана: {$path}");
            }
            
            return $success;
        } catch (ClientException $e) {
            // Обработка ошибок авторизации
            if ($e->getCode() == 401) {
                Log::error('Ошибка авторизации Яндекс.Диск при создании директории', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            } else {
                Log::error('Ошибка Яндекс.Диск при создании директории', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Исключение при создании директории на Яндекс.Диск', [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Upload file to Yandex Disk
     *
     * @param UploadedFile $file File to upload
     * @param string $path Path on Yandex Disk
     * @return array Upload result
     */
    public function uploadFile(UploadedFile $file, $path)
    {
        try {
            // Проверяем валидность файла
            if (!$file->isValid()) {
                Log::error("Невалидный файл для загрузки", [
                    'error' => $file->getError(),
                    'path' => $path
                ]);
                return ['success' => false, 'message' => "Невалидный файл: " . $file->getErrorMessage()];
            }
            
            // Проверяем размер файла (максимальный размер 1.5ГБ)
            $maxSize = 1.5 * 1024 * 1024 * 1024; // 1.5GB в байтах
            if ($file->getSize() > $maxSize) {
                Log::error("Файл слишком большой для загрузки на Яндекс.Диск", [
                    'size' => $file->getSize(),
                    'max_size' => $maxSize,
                    'path' => $path
                ]);
                return ['success' => false, 'message' => "Файл превышает максимальный размер 1.5ГБ"];
            }
            
            // Проверяем авторизацию с более длительным таймаутом
            $this->client->getConfig('timeout', $this->timeout);
            if (!$this->checkAuth()) {
                Log::error('Ошибка авторизации при загрузке файла', [
                    'path' => $path
                ]);
                return ['success' => false, 'message' => "Ошибка авторизации на Яндекс.Диск"];
            }
            
            // Ensure directory exists with improved error handling
            $directory = dirname($path);
            Log::info("Создаем директорию для файла", ['directory' => $directory]);
            
            $directoryCreated = $this->createDirectory($directory);
            
            if (!$directoryCreated) {
                Log::error("Не удалось создать директорию для загрузки", ['directory' => $directory]);
                return ['success' => false, 'message' => "Не удалось создать директорию: {$directory}"];
            }
            
            // Get upload URL
            Log::info("Запрашиваем URL для загрузки", ['path' => $path, 'file_size' => $file->getSize()]);
            
            $response = $this->client->get($this->apiUrl . '/resources/upload', [
                'query' => [
                    'path' => $path,
                    'overwrite' => 'true',
                ],
                'timeout' => $this->timeout,
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() != 200) {
                $error = (string)$response->getBody();
                Log::error("Ошибка при получении URL для загрузки", [
                    'path' => $path,
                    'status' => $response->getStatusCode(),
                    'response' => $error
                ]);
                return ['success' => false, 'message' => "Ошибка получения URL для загрузки: HTTP {$response->getStatusCode()}"];
            }
            
            $result = json_decode($response->getBody(), true);
            
            if (!isset($result['href'])) {
                Log::error("В ответе отсутствует URL для загрузки", ['result' => $result]);
                return ['success' => false, 'message' => "В ответе отсутствует URL для загрузки"];
            }
            
            $uploadUrl = $result['href'];
            Log::info("Получен URL для загрузки", ['url' => $uploadUrl]);
            
            // Upload file to the URL with increased timeout
            $uploadClient = new Client([
                'timeout' => $this->timeout,
                'connect_timeout' => 30,
            ]);
            
            $fileContents = fopen($file->getRealPath(), 'r');
            
            if ($fileContents === false) {
                Log::error("Не удалось открыть файл для загрузки", ['path' => $file->getRealPath()]);
                return ['success' => false, 'message' => "Не удалось открыть файл для загрузки"];
            }
            
            Log::info("Загружаем файл по полученному URL", [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'timeout' => $this->timeout
            ]);
            
            $uploadResponse = $uploadClient->put($uploadUrl, [
                'body' => $fileContents,
                'headers' => [
                    'Content-Type' => $file->getMimeType(),
                ],
                'timeout' => $this->timeout,
                'http_errors' => false
            ]);
            
            // Закрываем файловый ресурс
            if (is_resource($fileContents)) {
                fclose($fileContents);
            }
            
            if ($uploadResponse->getStatusCode() == 201 || $uploadResponse->getStatusCode() == 200) {
                // File uploaded successfully
                Log::info("Файл успешно загружен", ['path' => $path]);
                
                // Publish file to get public link
                $publicLink = $this->publishFile($path);
                
                return [
                    'success' => true,
                    'path' => $path,
                    'url' => $publicLink,
                    'original_name' => $file->getClientOriginalName(),
                ];
            } else {
                $errorBody = (string)$uploadResponse->getBody();
                Log::error("Ошибка при загрузке файла", [
                    'status' => $uploadResponse->getStatusCode(),
                    'response' => $errorBody
                ]);
                return ['success' => false, 'message' => "Ошибка загрузки файла: HTTP {$uploadResponse->getStatusCode()}"];
            }
        } catch (ClientException $e) {
            // Обработка ошибки авторизации
            if ($e->getCode() == 401) {
                Log::error('Ошибка авторизации Яндекс.Диск при загрузке файла', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
                return ['success' => false, 'message' => "Ошибка авторизации: " . $e->getMessage()];
            } else {
                Log::error('Клиентская ошибка при загрузке файла на Яндекс.Диск', [
                    'path' => $path,
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
                return ['success' => false, 'message' => "Ошибка при загрузке: " . $e->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error("Исключение при загрузке файла на Яндекс.Диск", [
                'path' => $path,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => "Исключение: " . $e->getMessage()];
        }
    }
    
    /**
     * Upload multiple files to Yandex Disk
     *
     * @param array $files Array of UploadedFile
     * @param string $directory Directory on Yandex Disk
     * @return array Upload results
     */
    public function uploadFiles($files, $directory)
    {
        $results = [];
        
        // Ensure the directory exists
        $this->createDirectory($directory);
        
        foreach ($files as $file) {
            // Generate a unique filename to avoid collisions
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $directory . '/' . $filename;
            
            $results[] = $this->uploadFile($file, $path);
        }
        
        return $results;
    }
    
    /**
     * Publish file to get a public link
     *
     * @param string $path Path to the file
     * @return string|bool Public URL or false on failure
     */
    public function publishFile($path)
    {
        try {
            $response = $this->client->put($this->apiUrl . '/resources/publish', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            if ($response->getStatusCode() == 200) {
                // Get the public link of the published file
                $metaResponse = $this->client->get($this->apiUrl . '/resources', [
                    'query' => [
                        'path' => $path,
                    ],
                    'http_errors' => false
                ]);
                
                if ($metaResponse->getStatusCode() == 200) {
                    $meta = json_decode($metaResponse->getBody(), true);
                    
                    // Return the public URL if available
                    if (isset($meta['public_url'])) {
                        return $meta['public_url'];
                    }
                }
            }
            
            return false;
        } catch (ClientException $e) {
            if ($e->getCode() == 401) {
                Log::error('Ошибка авторизации Яндекс.Диск при публикации файла', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
            } else {
                Log::error('Ошибка Яндекс.Диск при публикации файла', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Исключение при публикации файла на Яндекс.Диск', [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Delete a file or directory from Yandex Disk
     *
     * @param string $path Path to the file or directory
     * @return bool Whether deletion was successful
     */
    public function deleteFile($path)
    {
        try {
            $response = $this->client->delete($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                    'permanently' => 'true',
                ],
                'http_errors' => false
            ]);
            
            return $response->getStatusCode() == 204 || $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            Log::error('Yandex Disk delete error: ' . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Check if file or directory exists on Yandex Disk
     *
     * @param string $path Path to check
     * @return bool Whether file or directory exists
     */
    public function exists($path)
    {
        try {
            $response = $this->client->get($this->apiUrl . '/resources', [
                'query' => [
                    'path' => $path,
                ],
                'http_errors' => false
            ]);
            
            return $response->getStatusCode() == 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
