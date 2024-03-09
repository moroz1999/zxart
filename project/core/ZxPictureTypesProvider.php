<?php

trait ZxPictureTypesProvider
{
    public function getZxPictureTypeTranslation(string $code): string
    {
        $types = $this->getZxPictureTypes();
        return !empty($types[$code]) ? $types[$code] : '';
    }

    /**
     * @return string[]
     *
     * @psalm-return array{standard: 'field.format_standard', flash: 'field.format_flash', monochrome: 'field.format_monochrome', tricolor: 'field.format_tricolor', multicolor: 'field.format_multicolor', multicolor4: 'field.format_multicolor4', timex81: 'field.format_timex81', timexhr: 'field.format_timexhr', timexhrg: 'field.format_timexhrg', sam4: 'field.format_sam4', bsc: 'field.format_bsc', bsp: 'field.format_bsp', bmc4: 'field.format_bmc4', mlt: 'field.format_mlt', mc: 'field.format_mc', gigascreen: 'field.format_gigascreen', mg1: 'field.format_mg1', mg2: 'field.format_mg2', mg4: 'field.format_mg4', mg8: 'field.format_mg8', attributes: 'field.format_attributes', lowresgs: 'field.format_lowresgs', stellar: 'field.format_stellar', 'chr$': 'field.format_chr', ulaplus: 'field.format_ulaplus', zxevo: 'field.format_zxevo', sxg: 'field.format_sxg', grf: 'field.format_grf', nxi: 'field.format_nxi', sl2: 'field.format_sl2'}
     */
    public function getZxPictureTypes(): array
    {
        return [
            'standard' => 'field.format_standard',
            'flash' => 'field.format_flash',
            'monochrome' => 'field.format_monochrome',
            'tricolor' => 'field.format_tricolor',
            'multicolor' => 'field.format_multicolor',
            'multicolor4' => 'field.format_multicolor4',
            'timex81' => 'field.format_timex81',
            'timexhr' => 'field.format_timexhr',
            'timexhrg' => 'field.format_timexhrg',
            'sam4' => 'field.format_sam4',
            'bsc' => 'field.format_bsc',
            'bsp' => 'field.format_bsp',
            'bmc4' => 'field.format_bmc4',
            'mlt' => 'field.format_mlt',
            'mc' => 'field.format_mc',
            'gigascreen' => 'field.format_gigascreen',
            'mg1' => 'field.format_mg1',
            'mg2' => 'field.format_mg2',
            'mg4' => 'field.format_mg4',
            'mg8' => 'field.format_mg8',
            'attributes' => 'field.format_attributes',
            'lowresgs' => 'field.format_lowresgs',
            'stellar' => 'field.format_stellar',
            'chr$' => 'field.format_chr',
            'ulaplus' => 'field.format_ulaplus',
            'zxevo' => 'field.format_zxevo',
            'sxg' => 'field.format_sxg',
            'grf' => 'field.format_grf',
            'nxi' => 'field.format_nxi',
            'sl2' => 'field.format_sl2',
        ];
    }

}