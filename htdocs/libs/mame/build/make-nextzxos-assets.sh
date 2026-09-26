#!/usr/bin/env bash
# Builds what the ZX Spectrum Next emulator boots from:
#
#   next/nextzxos.zip   the NextZXOS system tree, which the browser turns into
#                       an SD card around the release it is playing
#   software/next.img   a ready-made card holding that tree plus the releases
#                       in releases.txt, for the standalone pages beside it
#
# Needs docker (for mtools), python3 and curl. Everything else it writes lives
# in .work/ beside this script.
set -euo pipefail

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
lib="$(dirname "$here")"

# The pinned NextZXOS card the system tree is taken out of; see README.md.
card_zip="${1:-$here/tbblue.mmc.zip}"
releases="$here/releases.txt"
work="$here/.work"
system_zip="$lib/next/nextzxos.zip"
image="$lib/software/next.img"

# 64 MB: the geometry MAME guesses for it is exact (256/16/32) and the block
# count is a multiple of 1024, which is what its SD card device needs to
# present the image as SDHC.
sectors=131072
part_lba=2048
part_sectors=$((sectors - part_lba))
offset=$((part_lba * 512))
mtools_image="zxart-mtools"

mtools() {
    docker run --rm -u "$(id -u):$(id -g)" -v "$work":/work -w /work \
        -e MTOOLS_SKIP_CHECK=1 "$mtools_image" "$@"
}

if ! docker image inspect "$mtools_image" >/dev/null 2>&1; then
    echo "==> building the $mtools_image helper image"
    docker build -t "$mtools_image" - <<'DOCKERFILE'
FROM alpine:3.20
RUN apk add --no-cache mtools
DOCKERFILE
fi

mkdir -p "$work" "$(dirname "$system_zip")" "$(dirname "$image")"

# --- the NextZXOS system tree, taken out of the pinned card once -------------
if [ ! -d "$work/system" ]; then
    echo "==> unpacking the NextZXOS card"
    python3 - "$card_zip" "$work/tbblue.mmc" <<'PY'
import shutil, sys, zipfile
src, dst = sys.argv[1], sys.argv[2]
with zipfile.ZipFile(src) as z, z.open('tbblue.mmc') as s, open(dst, 'wb') as d:
    shutil.copyfileobj(s, d, 1 << 20)
PY
    mkdir -p "$work/system"
    mtools sh -c "cd /work/system && mcopy -s -p -Q -n -i /work/tbblue.mmc@@1048576 '::/*' ."
    rm -f "$work/tbblue.mmc"
fi

echo "==> writing $(basename "$system_zip")"
python3 - "$work/system" "$system_zip" <<'PY'
import pathlib, sys, zipfile
root, out = pathlib.Path(sys.argv[1]), sys.argv[2]
paths = sorted(p for p in root.rglob('*') if p.is_file())
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
    for path in paths:
        # A fixed timestamp keeps the asset byte-identical between rebuilds.
        info = zipfile.ZipInfo(str(path.relative_to(root)).replace('\\', '/'),
                               date_time=(1980, 1, 1, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        z.writestr(info, path.read_bytes())
print(f'{len(paths)} files')
PY

# --- the releases, for the standalone card ----------------------------------
mkdir -p "$work/downloads"
rm -rf "$work/releases"
mkdir -p "$work/releases"
while IFS=: read -r id name folder; do
    case "$id" in ''|\#*) continue ;; esac
    if [ ! -s "$work/downloads/$name" ]; then
        echo "==> fetching release $id ($name)"
        curl -fsS --max-time 300 -A "zxart-sd-image-builder" \
            -o "$work/downloads/$name" "https://zxart.ee/releasefile/id:$id/$name"
    fi
    python3 - "$work/downloads/$name" "$work/releases/$folder" <<'PY'
import pathlib, shutil, sys, zipfile
src, dst = pathlib.Path(sys.argv[1]), pathlib.Path(sys.argv[2])
dst.mkdir(parents=True, exist_ok=True)
if zipfile.is_zipfile(src):
    with zipfile.ZipFile(src) as z:
        z.extractall(dst)
    # A release packed as one top folder is flattened, so the card shows the
    # program itself rather than a folder holding one folder.
    entries = list(dst.iterdir())
    if len(entries) == 1 and entries[0].is_dir():
        inner = entries[0]
        for item in list(inner.iterdir()):
            shutil.move(str(item), str(dst / item.name))
        inner.rmdir()
else:
    shutil.copy2(src, dst / src.name)
PY
done < "$releases"

# --- the standalone card ----------------------------------------------------
echo "==> building $(basename "$image")"
rm -f "$work/next.img"
python3 - "$work/next.img" "$sectors" "$part_lba" "$part_sectors" <<'PY'
import struct, sys
path, sectors, lba, count = sys.argv[1], *map(int, sys.argv[2:5])
mbr = bytearray(512)
# One FAT32 (LBA) partition. The CHS fields are the geometry MAME derives from
# the image size; nothing reads them, they are filled in for tidiness.
mbr[446:446 + 16] = struct.pack('<B3sB3sII', 0x00, b'\x00\x02\x00', 0x0c, b'\xff\xff\xff', lba, count)
mbr[510:512] = b'\x55\xaa'
with open(path, 'wb') as f:
    f.write(mbr)
    f.truncate(sectors * 512)
PY

# 512-byte clusters: at 64 MB nothing larger reaches the 65525 clusters a FAT32
# volume must have.
mtools mformat -i "/work/next.img@@$offset" -F -c 1 \
    -T "$part_sectors" -H "$part_lba" -h 16 -s 32 -v NEXT ::
mtools sh -c "cd /work/system && mcopy -s -Q -i /work/next.img@@$offset ./* ::/"
mtools sh -c "cd /work/releases && mmd -i /work/next.img@@$offset ::/zxart && mcopy -s -Q -i /work/next.img@@$offset ./* ::/zxart/"
mtools mdir -i "/work/next.img@@$offset" ::/zxart

mv "$work/next.img" "$image"
ls -l "$system_zip" "$image"
