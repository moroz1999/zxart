<?php
declare(strict_types=1);

namespace ZxArt\Hardware;

/**
 * Enumerates all supported hardware items.
 */
enum HardwareItem: string
{
    // Computers
    case ZX48 = 'zx48';
    case ZX16 = 'zx16';
    case ZX128 = 'zx128';
    case ZX128_P2 = 'zx128+2';
    case ZX128_P2B = 'zx128+2b';
    case ZX128_P3 = 'zx128+3';
    case SINCLAIRQL = 'sinclairql';
    case TIMEX2048 = 'timex2048';
    case TIMEX2068 = 'timex2068';
    case ATM = 'atm';
    case ATM2 = 'atm2';
    case PENTAGON128 = 'pentagon128';
    case PENTAGON512 = 'pentagon512';
    case PENTAGON1024 = 'pentagon1024';
    case PENTAGON2666 = 'pentagon2666';
    case PROFI = 'profi';
    case SCORPION = 'scorpion';
    case SCORPION1024 = 'scorpion1024';
    case BYTE = 'byte';
    case ZXMPHOENIX = 'zxmphoenix';
    case BASECONF = 'baseconf';
    case TSCONF = 'tsconf';
    case ZXNEXT = 'zxnext';
    case ELEMENTZXMB = 'elementzxmb';
    case ZXUNO = 'zxuno';
    case SAMCOUPE = 'samcoupe';
    case ZX80 = 'zx80';
    case ZX8116 = 'zx8116';
    case ZX811 = 'zx811';
    case ZX812 = 'zx812';
    case ZX8132 = 'zx8132';
    case ZX8164 = 'zx8164';
    case LAMBDA8300 = 'lambda8300';
    case SPRINTER = 'sprinter';
    case ALF = 'alf';
    case DIDAKTIK80 = 'didaktik80';
    case TK9X = 'tk9x';

    // Storage
    case TAPE = 'tape';
    case THREE_DOS_DISK = '3dosdisk';
    case BETADISK = 'betadisk';
    case CD = 'cd';
    case PROFIIDE = 'profiide';
    case NEMOIDE = 'nemoide';
    case ZCONTROLLER = 'zcontroller';
    case ATMIDE = 'atmide';
    case DIVIDE = 'divide';
    case DIVMMC = 'divmmc';
    case SMUC = 'smuc';
    case SDDIVMMC = 'sddivmmc';
    case SDZ = 'sdz';
    case SDNEOGS = 'sdneogs';
    case ATOM = 'atom';
    case ATOM_LITE = 'atom_lite';
    case MICRODRIVE = 'microdrive';
    case I2CARTRIDGE = 'i2cartridge';
    case DANDANATOR = 'dandanator';
    case TIMEX_CARTRIDGE = 'timex_cartridge';

    // DOS
    case TRDOS4X = 'trdos4x';
    case TRDOS = 'trdos';
    case ISDOS = 'isdos';
    case TASIS = 'tasis';
    case CPM = 'cpm';
    case ESXDOS = 'esxdos';
    case MDOS = 'mdos';
    case THREE_DOS = '3dos';
    case NEDOOS = 'nedoos';
    case OPD = 'opd';
    case DISCIPLE = 'disciple';
    case TOS = 'tos';
    case BSDOS = 'bsdos';

    // Sound
    case AY = 'ay';
    case BEEPER = 'beeper';
    case TS = 'ts';
    case TSFM = 'tsfm';
    case AYNEXT = 'aynext';
    case NEXTDAC = 'nextdac';
    case GS = 'gs';
    case NGS = 'ngs';
    case COVOXFB = 'covoxfb';
    case COVOXDD = 'covoxdd';
    case COVOXGS = 'covoxgs';
    case SOUNDRIVE = 'soundrive';
    case SPECDRUM = 'specdrum';
    case CHEETAH = 'cheetah';
    case DMAUSC = 'dmausc';
    case SAA = 'saa';
    case ZXM = 'zxm';
    case SID = 'sid';
    case SIDELZX = 'sidelzx';
    case QUAZAR_SURROUND = 'quazar_surround';
    case MIDI = 'midi';

    // Controls
    case CURSOR = 'cursor';
    case KEMPSTON = 'kempston';
    case KEMPSTON8B = 'kempston8b';
    case INT2_1 = 'int2_1';
    case INT2_2 = 'int2_2';
    case KEMPSTONMOUSE = 'kempstonmouse';
    case AYMOUSE = 'aymouse';
    case AMIGAMOUSE = 'amigamouse';
    case ATARIMOUSE = 'atarimouse';
    case AMXMOUSE = 'amxmouse';
    case SAMMOUSE = 'sammouse';
    case GUNSTICK = 'gunstick';
    case MAGNUMLIGHT = 'magnumlight';
    case NOVINA = 'novina';
    case LIGHTPEN = 'lightpen';

    case DEFENDER = 'defender';

    // Expansion
    case ZXPAND = 'zxpand';
    case CACHE = 'cache';
    case GMX = 'gmx';
    case FLASHCOLOR = 'flashcolor';
    case ULAPLUS = 'ulaplus';
    case RADASTAN = 'radastan';
    case DMA = 'dma';
    case CHROMA81 = 'chroma81';
    case TRINITY = 'trinity';
    case HIRES = 'hires';
    case HGFX = 'hgfx';
}
