<?php

use Illuminate\Database\Connection;

class SpamChecker
{
    private Connection $db;
    private VerifyMailAdapter $verifyMailAdapter;
    private VerifaliaAdapter $verifaliaAdapter;

    public function setDb(Connection $db): void
    {
        $this->db = $db;
    }

    public function setVerifyMailAdapter(VerifyMailAdapter $verifyMailAdapter): void
    {
        $this->verifyMailAdapter = $verifyMailAdapter;
    }

    public function setVerifaliaAdapter(VerifaliaAdapter $verifaliaAdapter): void
    {
        $this->verifaliaAdapter = $verifaliaAdapter;
    }

    private function getServices(): array
    {
        $services = [
            $this->verifaliaAdapter,
            $this->verifyMailAdapter,
        ];
//        shuffle($services);
        return $services;
    }

    /**
     * @throws JsonException
     */
    public function checkEmail(string $email): bool
    {
        $email = trim($email);

        $address = explode('@', $email)[0];
        if (str_contains($address, '+') || substr_count($address, '.') > 2) {
            return false;
        }

        $domain = explode('@', $email)[1];

        $domainRecord = $this->db->table('domains')->where('name', $domain)->first();
        if ($domainRecord) {
            return (bool)$domainRecord['allowed'];
        }
        $services = $this->getServices();
        $allowed = true;
        while ($allowed && ($service = array_pop($services))) {
            $result = $service->checkEmail($email);
            $allowed = $result === false ? false : $allowed;
        }
        if ($allowed !== null) {
            $this->db->table('domains')->insert(['name' => $domain, 'allowed' => $allowed ? 1 : 0]);
        }

        return $allowed ?? true;
    }
}
