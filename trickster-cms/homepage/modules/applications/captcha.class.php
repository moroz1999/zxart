<?php

use App\Users\CurrentUserService;

class captchaApplication extends controllerApplication
{
    protected $applicationName = 'captcha';
    // configuration
    public $font = 'trickster/homepage/fonts/agustinasans.ttf';
    public $backgroundImage = 'captcha.png';
    public $width = 200;
    public $height = 50;
    public $captchaColor = [231, 0, 120];

    public function initialize()
    {
        $this->startSession('public', $this->getService(ConfigManager::class)->get('main.publicSessionLifeTime'));
        $this->createRenderer('fileReader');

        ini_set('memory_limit', '128M');
    }

    public function execute($controller)
    {
        $text = $this->makeString();
        $currentUserService = $this->getService(CurrentUserService::class);
        $currentUserService->getCurrentUser()->setStorageAttribute('last_captcha', $text);

        $backgroundImage = imagecreatefrompng($this->getBackgroundImageFile());

        $bgWidth = imagesx($backgroundImage);
        $bgHeight = imagesy($backgroundImage);

        $randomXStart = rand(0, $bgWidth - $this->width);
        $randomYStart = rand(0, $bgHeight - $this->height);
        $randomContrast = rand(192, 255);
        imagefilter($backgroundImage, IMG_FILTER_CONTRAST, $randomContrast);
        imagefilter($backgroundImage, IMG_FILTER_COLORIZE, $this->captchaColor[0], $this->captchaColor[1], $this->captchaColor[2]);
        $captcha = imagecreatetruecolor($this->width, $this->height);
        imagecopy($captcha, $backgroundImage, 0, 0, $randomXStart, $randomYStart, $bgWidth, $bgHeight);
        $textcolor = imagecolorallocate($captcha, $this->captchaColor[0], $this->captchaColor[1], $this->captchaColor[2]);
        $charOffset = 0;
        $charPosition = 5;

        while ($charOffset < strlen($text)) {
            $char = substr($text, $charOffset, 1);

            $randomAngle = rand(-25, 25);
            $randomSize = rand(18, 24);

            $imageOffset = imagettftext($captcha, $randomSize, $randomAngle, $charPosition, 40, $textcolor, $this->font, $char);
            if ($imageOffset[2] > $imageOffset[4]) {
                $offsetLeft = $imageOffset[2];
            } else {
                $offsetLeft = $imageOffset[4];
            }
            $charPosition = $offsetLeft - 2;
            $charOffset++;
        }

        $captchaResult = imagecreatetruecolor($charPosition + 7, $this->height);
        imagecopy($captchaResult, $captcha, 0, 0, 0, 0, $charPosition + 7, $this->height);

        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-disposition: inline; filename=captcha.png\n");
        header('Content-type: image/png');

        imagepng($captchaResult);
    }

    protected function makeString($length = 5)
    {
        $result = '';
        $valid1 = "aeiouy";
        $valid2 = "bcdfghjklmnprstvwxz";

        for ($a = 0; $a < $length; $a++) {
            if ($a % 2) {
                $charList = $valid1;
            } else {
                $charList = $valid2;
            }
            $b = rand(0, strlen($charList) - 1);
            $result .= $charList[$b];
        }
        return $result;
    }

    protected function getBackgroundImageFile()
    {
        $controller = controller::getInstance();
        $filePath = $controller->getProjectPath() . 'images/' . $this->backgroundImage;
        if (!is_file($filePath)) {
            $pathsManager = $this->pathsManager;
            $filePath = $pathsManager->getIncludeFilePath('images/public/' . $this->backgroundImage);
        }
        return $filePath;
    }
}





