<?php

namespace ZxArt\Groups;

trait GroupTypeProvider
{
    /**
     * @return list<string>
     */
    public function getGroupTypes(): array
    {
        return GroupTypes::LIST;
    }
}
