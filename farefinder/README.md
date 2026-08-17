# Fare Finder - deploy bundle

Upload everything in this folder to your hosting account (public_html/farefinder
or similar). The page is plain static files.

    index.html         the search UI
    data.json          the fares it reads
    places.json        airport list the updater sweeps
    update-fares.php   cron entry - PHP, needs no SSH and no Python
    update-fares.sh    same job in Python, if your plan runs it

## Two things to set up (no SSH needed)

1. Open `update-fares.php` in cPanel File Manager -> Edit, and put your provider
   token in $CONFIG['token'].
2. cPanel -> Cron Jobs, nightly:

       /usr/local/bin/php /home/USERNAME/public_html/farefinder/update-fares.php

Everything the updater needs -- PHP 8 and curl -- is standard on cPanel hosting.
`update-fares.sh` does the same job in Python if you would rather use that.

If neither runs, generate `data.json` anywhere else and upload it. The page does
not care where the file came from.

## Checks before you trust it

- `data.json` regenerates without error and its `generatedAt` moves.
- The page footer shows that timestamp.
- `demo` is `false` in `data.json` -- if it is `true` you are looking at
  generated sample numbers, not real fares.
