import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';

const HW_ICON_MAP: Readonly<Record<string, string>> = {
  // Sound chips and audio hardware
  ay: 'hw-sound',
  beeper: 'hw-sound',
  ts: 'hw-sound',
  tsfm: 'hw-sound',
  gs: 'hw-sound',
  ngs: 'hw-sound',
  saa: 'hw-sound',
  covoxfb: 'hw-sound',
  covoxdd: 'hw-sound',
  covoxgs: 'hw-sound',
  soundrive: 'hw-sound',
  specdrum: 'hw-sound',
  cheetah: 'hw-sound',
  dmausc: 'hw-sound',
  zxm: 'hw-sound',
  smuc: 'hw-sound',
  sid: 'hw-sound',
  sidelzx: 'hw-sound',
  aynext: 'hw-sound',
  nextdac: 'hw-sound',
  quazar_surround: 'hw-sound',
  midi: 'hw-sound',

  // Disk and storage
  tape: 'hw-disk',
  betadisk: 'hw-disk',
  '3dosdisk': 'hw-disk',
  divide: 'hw-disk',
  divmmc: 'hw-disk',
  sddivmmc: 'hw-disk',
  sdneogs: 'hw-disk',
  sdz: 'hw-disk',
  microdrive: 'hw-disk',
  disciple: 'hw-disk',
  opd: 'hw-disk',
  atom: 'hw-disk',
  atom_lite: 'hw-disk',
  hdd: 'hw-disk',
  cd: 'hw-disk',
  zcontroller: 'hw-disk',
  nemoide: 'hw-disk',
  profiide: 'hw-disk',
  atmide: 'hw-disk',

  // Joystick and game controllers
  kempston: 'gamepad',
  int2_1: 'gamepad',
  int2_2: 'gamepad',
  cursor: 'gamepad',
  zxpand: 'gamepad',
  kempston8b: 'gamepad',
  tasis: 'gamepad',
  alf: 'gamepad',
  alf1: 'gamepad',
  i2cartridge: 'gamepad',
  dandanator: 'gamepad',

  // Mouse input devices
  kempstonmouse: 'hw-mouse',
  aymouse: 'hw-mouse',
  amigamouse: 'hw-mouse',
  atarimouse: 'hw-mouse',
  sammouse: 'hw-mouse',
  amxmouse: 'hw-mouse',

  // Graphics enhancements
  ulaplus: 'image',
  flashcolor: 'image',
  radastan: 'image',
  hires: 'image',
  hgfx: 'image',
  chroma81: 'image',

  // Operating systems and DOS
  trdos: 'hw-os',
  isdos: 'hw-os',
  cpm: 'hw-os',
  esxdos: 'hw-os',
  '3dos': 'hw-disk',
  mdos: 'hw-os',
  nedoos: 'hw-os',
  tos: 'hw-os',
  bsdos: 'hw-os',
  trdos4x: 'hw-os',

  // Light guns
  gunstick: 'hw-gun',
  magnumlight: 'hw-gun',
  novina: 'hw-gun',
  lightpen: 'hw-gun',
  defender: 'hw-gun',

  // Network
  trinity: 'globe',
};

const PRELOADED = new Set<string>();

@Component({
  selector: 'zx-hardware-icon',
  standalone: true,
  imports: [SvgIconComponent],
  templateUrl: './zx-hardware-icon.component.html',
  styleUrl: './zx-hardware-icon.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHardwareIconComponent implements OnInit {
  @Input({required: true}) id!: string;

  get iconName(): string {
    return HW_ICON_MAP[this.id] ?? 'hw-computer';
  }

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    const icon = this.iconName;
    if (!PRELOADED.has(icon)) {
      PRELOADED.add(icon);
      this.iconReg.loadSvg(`${environment.svgUrl}${icon}.svg`, icon)?.subscribe();
    }
  }
}
