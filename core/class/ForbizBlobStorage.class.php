<?php

/**
 * Azure Blob Storage 파일 관리
 *
 * @author hoksi
 * @property \League\Flysystem\Filesystem $bsClient Blob Storage
 */
class ForbizBlobStorage
{
    public $bsClient         = false;
    protected $path             = false;

    protected $key              = false;
    protected $account           = false;
    protected $container           = false;

    public function __construct($account, $key, $container)
    {
        $this->account = $account;
        $this->key    = $key;
        $this->container    = $container;

        $this->getConnect();
    }

    public function getConnect()
    {
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName='.$this->account.';AccountKey='.$this->key.';';
        $client = \MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService($connectionString);

        $adapter = new \League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter($client, $this->container);
        $this->bsClient = new \League\Flysystem\Filesystem($adapter);
    }

    public function setUploadPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function upload($fileName, $source, $delete = false)
    {
        $uploadPathName = $this->getUploadPath($fileName);

        $ret = false;
        if (is_file($source)) {
            $ret = $this->bsClient->put($uploadPathName, file_get_contents($source));
            if ($ret === true) {
                // 원본 파일 삭제
                if($delete === true) {
                    unlink($source);
                }
            }
        }

        return ($ret !== false ? $uploadPathName : false);
    }

    public function delete($fileName)
    {
        $uploadPathName = $this->getUploadPath($fileName);

        $ret = $this->bsClient->delete($uploadPathName);
    }

    public function exists($fileName)
    {
        $uploadPathName = $this->getUploadPath($fileName);

        return $this->bsClient->has($uploadPathName);
    }

    public function list($path)
    {
        try {
            return $this->bsClient->listContents($path);
        } catch (FilesystemError $exception) {
            return $exception;
        }
    }

    public function read($path)
    {
        return $this->bsClient->read($path);
    }

    public function getUploadPath($fileName)
    {
        return ($this->path !== false ? ltrim(str_replace('//', '/', $this->path.'/'.$fileName), '/') : ltrim(str_replace('//', '/', $fileName), '/'));
    }

}