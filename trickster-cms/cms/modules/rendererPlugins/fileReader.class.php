<?php

class fileReaderRendererPlugin extends rendererPlugin
{
    protected $exportOperation = null;
    protected $contentRead = false;
    protected $filePath = false;
    protected $fileSize = false;
    protected $fileDate = false;
    protected $fileName = false;
    protected $bytesToSend = false;
    protected $chunkSize = false;
    protected $startPoint = 0;
    public $contentDisposition = null;

    public function init()
    {
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->maxAge = 365 * 60 * 60 * 24;
        $this->chunkSize = 10 * 1024 * 1024;

        $this->preferredEncodings = ['identity'];
        $this->contentDisposition = 'attachment';
    }

    public function fetch()
    {
    }

    public function assign($attributeName, $value)
    {
        if ($attributeName == 'filePath') {
            if (file_exists($value)) {
                $this->filePath = $value;

                $this->fileSize = filesize($this->filePath);
                $this->fileDate = filemtime($this->filePath);
            }
        }
        if ($attributeName == 'fileName') {
            $this->fileName = $value;
        }
    }

    protected function getEtag()
    {
        $eTag = false;
        if ($this->filePath) {
            $fileString = $this->filePath;
            $fileString .= $this->fileSize;
            $fileString .= $this->fileDate;

            $eTag = md5($fileString);
        }
        return $eTag;
    }

    protected function getContentLength()
    {
        return $this->fileSize;
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();

        $preferredOrder = [$this->getMimeType($this->filePath)];

        $selectedType = $this->selectHTTPParameter($preferredOrder, $contentTypes, '*/*');

        return $selectedType;
    }

    public function getContentDisposition()
    {
        return $this->contentDisposition;
    }

    protected function renderContent()
    {
        $this->bytesToSend = $this->fileSize;
    }

    protected function getContentTextPart()
    {
        if ($this->bytesToSend > 0) {
            if ($this->chunkSize > $this->bytesToSend) {
                $this->chunkSize = $this->bytesToSend;
            }

            $currentPoint = $this->startPoint;
            $this->startPoint = $this->startPoint + $this->chunkSize;
            $this->bytesToSend = $this->bytesToSend - $this->chunkSize;

            return file_get_contents($this->filePath, false, null, $currentPoint, $this->chunkSize);
        }
        return false;
    }

    protected function compress($encoding)
    {
    }

    protected function getMimeType($filename)
    {
        if ($filename) {
            $mime_types = [
                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',
                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',
                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',
                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',
                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',
                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',
                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            ];
            $array = explode('.', $filename);
            $ext = strtolower(array_pop($array));
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            } elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            } else {
                return 'application/octet-stream';
            }
        }
        return false;
    }

    public function getFileName()
    {
        return $this->fileName;
    }
}
