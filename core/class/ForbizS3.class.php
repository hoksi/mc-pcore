<?php

/**
 * AWS S3 파일 관리
 *
 * @author hoksi
 * @property \League\Flysystem\Filesystem $s3Client S3
 */
class ForbizS3
{
    protected $s3Client         = false;
    protected $path             = false;
    protected $key              = false;
    protected $secret           = false;
    protected $region           = false;
    protected $bucket           = false;

    protected $cloudFrontClient = false;
    protected $distributionId = false;

    public function __construct($key, $secret, $region, $bucket, $distributionId = false)
    {
        $this->key    = $key;
        $this->secret = $secret;
        $this->region = $region;
        $this->bucket = $bucket;
        $this->distributionId = ($distributionId === false && defined('S3_DISTRIBUTIONID') ? S3_DISTRIBUTIONID : $distributionId);

        $this->getClient();
    }

    public function getClient()
    {
        if ($this->s3Client === false) {
            $this->s3Client = new \League\Flysystem\Filesystem(
                new \League\Flysystem\AwsS3v3\AwsS3Adapter(
                    new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region' => $this->region,
                        'credentials' => [
                            'key' => $this->key,
                            'secret' => $this->secret,
                        ]
                        ]), $this->bucket
                )
            );
        }

        return $this->s3Client;
    }

    public function getCloudFrontClient()
    {
        if ($this->distributionId !== false && $this->cloudFrontClient === false) {
            $this->cloudFrontClient = new Aws\CloudFront\CloudFrontClient([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => $this->key,
                    'secret' => $this->secret,
                ]
            ]);
        }

        return $this->cloudFrontClient;
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
            $ret = $this->s3Client->put($uploadPathName, file_get_contents($source));
            if ($ret === true) {
                // 원본 파일 삭제
                if($delete === true) {
                    unlink($source);
                }

                // CDN 이미지 퍼지
                if($this->distributionId !== false) {
                    $this->purgeImage($uploadPathName);
                }
            }
        }

        return ($ret !== false ? $uploadPathName : false);
    }

    public function delete($fileName)
    {
        $uploadPathName = $this->getUploadPath($fileName);

        $ret = $this->s3Client->delete($uploadPathName);

        if($ret === true) {
            // CDN 이미지 퍼지
            if($this->distributionId !== false) {
                $this->purgeImage($uploadPathName);
            }
        }
    }

    public function exists($fileName)
    {
        $uploadPathName = $this->getUploadPath($fileName);

        return $this->s3Client->has($uploadPathName);
    }

    public function list($path)
    {
        try {
            return $this->s3Client->listContents($path);
        } catch (FilesystemError $exception) {
            return $exception;
        }
    }

    public function read($path)
    {
        return $this->s3Client->read($path);
    }

    public function getUploadPath($fileName)
    {
        return ($this->path !== false ? ltrim(str_replace('//', '/', $this->path.'/'.$fileName), '/') : ltrim(str_replace('//', '/', $fileName), '/'));
    }

    public function purgeImage($paths)
    {
        $paths = (is_array($paths) ? $paths : [$paths]);

        foreach($paths as $key => $path) {
            $paths[$key] = '/' . ltrim($path, '/');
        }

        try {
            $result = $this->getCloudFrontClient()->createInvalidation([
                'DistributionId' => $this->distributionId,
                'InvalidationBatch' => [
                    'CallerReference' => microtime(),
                    'Paths' => [
                        'Items' => $paths,
                        'Quantity' => count($paths),
                    ],
                ]
            ]);
        } catch (\Aws\Exception\AwsException $e) {
            fb_sys_log('cloudFront', [
                'paths' => $paths,
                's3Error' => $e->getAwsErrorMessage(),
            ]);

            return false;
        }

        return ($result['Location'] ?? false);
    }
}