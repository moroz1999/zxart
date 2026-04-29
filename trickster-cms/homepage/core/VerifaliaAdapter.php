<?php
declare(strict_types=1);

use Verifalia\EmailValidations\ValidationEntryStatus;
use Verifalia\Security\BearerAuthenticationProvider;
use Verifalia\VerifaliaRestClient;
use Verifalia\VerifaliaRestClientOptions;

class VerifaliaAdapter implements SpamCheckAdapter
{
    protected ConfigManager $configManager;

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    public function checkEmail(string $email): ?bool
    {
        $apiUser = $this->configManager->getConfig('emails')->get('verifaliaUser');
        $apiKey = $this->configManager->getConfig('emails')->get('verifaliaPass');
        try {
            $verifalia = new VerifaliaRestClient([
                VerifaliaRestClientOptions::AUTHENTICATION_PROVIDER =>
                    new BearerAuthenticationProvider($apiUser, $apiKey)
            ]);

            $job = $verifalia->emailValidations->submit($email);
            $entry = $job->entries[0];

            return $entry->status === ValidationEntryStatus::SUCCESS;
        } catch (Exception $e){

        }
        return null;
    }
}