<?php

class UpdatesApi
{
    protected $apiUrl = '';
    protected $licenceKey = '';
    protected $licenceName = '';
    protected $workspaceDir = '';

    public function __construct()
    {
    }

    public function getDeployments(array $types = [])
    {
        $result = [];
        $data = $this->queryApi([
            'action' => 'list',
            'types' => implode(',', $types),
        ]);
        if ($data !== false) {
            $datas = json_decode($data, true);
            $error = '';
            if ($datas === false) {
                $error = 'Error parsing Updates API response. ' . json_last_error_msg();
            } elseif (!isset($datas['responseStatus']) || $datas['responseStatus'] !== 'success'
                || !isset($datas['responseData'])
            ) {
                $error = 'Invalid Updates API response';
                if (!empty($datas['responseData'])) {
                    $error = (string)$datas['responseData'];
                }
            }
            if ($error !== '') {
                throw new Exception($error);
            }
            $datas = $datas['responseData'];
            foreach ($datas as $data) {
                $deployment = new UpdatesApiDeploymentInfo();
                $deployment->id = $data['id'];
                $deployment->type = $data['type'];
                $deployment->version = $data['version'];
                $deployment->description = $data['description'];
                $deployment->requirements = $data['requirements'];
                $deployment->downloadLink = $data['downloadLink'];
                $result[] = $deployment;
            }
        }
        return $result;
    }

    public function downloadDeployment($id)
    {
        $data = $this->queryApi([
            'action' => 'download',
            'deployment' => $id,
        ]);
        $writeSuccess = @file_put_contents($this->workspaceDir . $id, $data);
        if ($writeSuccess === false) {
            throw new Exception('Failed writing file ' . $this->workspaceDir . $id);
        }
        return $this->workspaceDir . $id;
    }

    public function updateNotify()
    {
        $this->queryApi([
            'action' => 'updateNotify',
        ]);
    }

    protected function queryApi(array $parameters)
    {
        $parameters += ['licence' => $this->licenceKey];
        $parameters = array_filter($parameters);
        $url = $this->apiUrl;
        foreach ($parameters as $key => $value) {
            $url .= urlencode($key) . ':' . urlencode($value) . '/';
        }
        $result = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'max_redirects' => '0',
                'ignore_errors' => '1',
            ],
        ]));
        if ($result === false) {
            $error = error_get_last();
            throw new Exception('Error querying updates API! ' . $error['message']);
        } elseif (!$result) {
            throw new Exception('Error querying updates API! Empty response.');
        }
        return $result;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        if (substr($apiUrl, -1) !== '/') {
            $apiUrl .= '/';
        }
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $licenceKey
     */
    public function setLicenceKey($licenceKey)
    {
        $this->licenceKey = $licenceKey;
    }

    /**
     * @param string $licenceName
     */
    public function setLicenceName($licenceName)
    {
        $this->licenceName = $licenceName;
    }

    /**
     * @param string $dir
     */
    public function setWorkspaceDir($dir)
    {
        $this->workspaceDir = $dir;
    }
}

class UpdatesApiDeploymentInfo
{
    public $type;
    public $version;
    public $description;
    public $downloadLink;
}