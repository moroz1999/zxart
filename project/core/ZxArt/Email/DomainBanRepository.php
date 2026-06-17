<?php

declare(strict_types=1);

namespace ZxArt\Email;

use Illuminate\Database\Connection;

/** DB repository for the email domain allow/ban list. No business logic. */
final readonly class DomainBanRepository
{
    private string $table;

    public function __construct(private Connection $db)
    {
        $this->table = 'domains';
    }

    /** Returns the stored allow/ban decision for a domain, or null when it is unknown. */
    public function findAllowed(string $domain): ?bool
    {
        $row = $this->db->table($this->table)->where('name', $domain)->first();
        if (!is_array($row)) {
            return null;
        }

        return (bool)$row['allowed'];
    }

    /** Stores the allow/ban decision so the domain does not need to be re-checked. */
    public function save(string $domain, bool $allowed): void
    {
        $this->db->table($this->table)->insert([
            'name' => $domain,
            'allowed' => $allowed ? 1 : 0,
        ]);
    }
}
