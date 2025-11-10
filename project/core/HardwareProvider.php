<?php
declare(strict_types=1);

use ZxArt\Hardware\HardwareGroup;
use ZxArt\Hardware\HardwareItem;

trait HardwareProvider
{
    public function getHardwareList(): array
    {
        return [
            HardwareGroup::COMPUTERS->value => [
                HardwareItem::ZX48->value,
                HardwareItem::ZX16->value,
                HardwareItem::ZX128->value,
                HardwareItem::ZX128_P2->value,
                HardwareItem::ZX128_P2B->value,
                HardwareItem::ZX128_P3->value,
                HardwareItem::SINCLAIRQL->value,
                HardwareItem::TIMEX2048->value,
                HardwareItem::TIMEX2068->value,
                HardwareItem::ATM->value,
                HardwareItem::ATM2->value,
                HardwareItem::PENTAGON128->value,
                HardwareItem::PENTAGON512->value,
                HardwareItem::PENTAGON1024->value,
                HardwareItem::PENTAGON2666->value,
                HardwareItem::PROFI->value,
                HardwareItem::SCORPION->value,
                HardwareItem::SCORPION1024->value,
                HardwareItem::BYTE->value,
                HardwareItem::ZXMPHOENIX->value,
                HardwareItem::BASECONF->value,
                HardwareItem::TSCONF->value,
                HardwareItem::ZXNEXT->value,
                HardwareItem::ELEMENTZXMB->value,
                HardwareItem::ZXUNO->value,
                HardwareItem::SAMCOUPE->value,
                HardwareItem::ZX80->value,
                HardwareItem::ZX8116->value,
                HardwareItem::ZX811->value,
                HardwareItem::ZX812->value,
                HardwareItem::ZX8132->value,
                HardwareItem::ZX8164->value,
                HardwareItem::LAMBDA8300->value,
                HardwareItem::SPRINTER->value,
                HardwareItem::ALF->value,
                HardwareItem::DIDAKTIK80->value,
                HardwareItem::TK9X->value,
            ],
            HardwareGroup::STORAGE->value => [
                HardwareItem::TAPE->value,
                HardwareItem::THREE_DOS_DISK->value,
                HardwareItem::BETADISK->value,
                HardwareItem::CD->value,
                HardwareItem::PROFIIDE->value,
                HardwareItem::NEMOIDE->value,
                HardwareItem::ZCONTROLLER->value,
                HardwareItem::ATMIDE->value,
                HardwareItem::DIVIDE->value,
                HardwareItem::DIVMMC->value,
                HardwareItem::SMUC->value,
                HardwareItem::SDDIVMMC->value,
                HardwareItem::SDZ->value,
                HardwareItem::SDNEOGS->value,
                HardwareItem::ATOM->value,
                HardwareItem::ATOM_LITE->value,
                HardwareItem::MICRODRIVE->value,
                HardwareItem::I2CARTRIDGE->value,
                HardwareItem::DANDANATOR->value,
                HardwareItem::TIMEX_CARTRIDGE->value,
            ],
            HardwareGroup::DOS->value => [
                HardwareItem::TRDOS4X->value,
                HardwareItem::TRDOS->value,
                HardwareItem::ISDOS->value,
                HardwareItem::TASIS->value,
                HardwareItem::CPM->value,
                HardwareItem::ESXDOS->value,
                HardwareItem::MDOS->value,
                HardwareItem::THREE_DOS->value,
                HardwareItem::NEDOOS->value,
                HardwareItem::OPD->value,
                HardwareItem::DISCIPLE->value,
                HardwareItem::TOS->value,
                HardwareItem::BSDOS->value,
            ],
            HardwareGroup::SOUND->value => [
                HardwareItem::AY->value,
                HardwareItem::BEEPER->value,
                HardwareItem::TS->value,
                HardwareItem::TSFM->value,
                HardwareItem::AYNEXT->value,
                HardwareItem::NEXTDAC->value,
                HardwareItem::GS->value,
                HardwareItem::NGS->value,
                HardwareItem::COVOXFB->value,
                HardwareItem::COVOXDD->value,
                HardwareItem::COVOXGS->value,
                HardwareItem::SOUNDRIVE->value,
                HardwareItem::SPECDRUM->value,
                HardwareItem::CHEETAH->value,
                HardwareItem::DMAUSC->value,
                HardwareItem::SAA->value,
                HardwareItem::ZXM->value,
                HardwareItem::SID->value,
                HardwareItem::SIDELZX->value,
                HardwareItem::QUAZAR_SURROUND->value,
                HardwareItem::MIDI->value,
            ],
            HardwareGroup::CONTROLS->value => [
                HardwareItem::CURSOR->value,
                HardwareItem::KEMPSTON->value,
                HardwareItem::KEMPSTON8B->value,
                HardwareItem::INT2_1->value,
                HardwareItem::INT2_2->value,
                HardwareItem::KEMPSTONMOUSE->value,
                HardwareItem::AYMOUSE->value,
                HardwareItem::AMIGAMOUSE->value,
                HardwareItem::ATARIMOUSE->value,
                HardwareItem::AMXMOUSE->value,
                HardwareItem::SAMMOUSE->value,
                HardwareItem::GUNSTICK->value,
                HardwareItem::MAGNUMLIGHT->value,
                HardwareItem::NOVINA->value,
                HardwareItem::LIGHTPEN->value,
                HardwareItem::DEFENDER->value,
            ],
            HardwareGroup::EXPANSION->value => [
                HardwareItem::ZXPAND->value,
                HardwareItem::CACHE->value,
                HardwareItem::GMX->value,
                HardwareItem::FLASHCOLOR->value,
                HardwareItem::ULAPLUS->value,
                HardwareItem::RADASTAN->value,
                HardwareItem::DMA->value,
                HardwareItem::CHROMA81->value,
                HardwareItem::TRINITY->value,
                HardwareItem::HIRES->value,
                HardwareItem::HGFX->value,
            ],
        ];
    }

    public function getHardwareType(string $item): ?string
    {
        foreach ($this->getHardwareList() as $type => $list) {
            if (in_array($item, $list, true)) {
                return $type;
            }
        }
        return null;
    }
}