<?php
declare(strict_types=1);


interface SpamCheckAdapter
{
    public function checkEmail(string $email): ?bool;
}