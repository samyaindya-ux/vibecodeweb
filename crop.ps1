Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("i:\xampp\htdocs\vibecodeweb\images\5logo.jpg")

$cols = 3
$rows = 2
[int]$w = [Math]::Floor($img.Width / $cols)
[int]$h = [Math]::Floor($img.Height / $rows)

for ($r = 0; $r -lt $rows; $r++) {
    for ($c = 0; $c -lt $cols; $c++) {
        $idx = $r * $cols + $c
        [int]$x = $c * $w
        [int]$y = $r * $h
        $rect = New-Object System.Drawing.Rectangle($x, $y, $w, $h)
        $bmp = New-Object System.Drawing.Bitmap($w, $h)
        $g = [System.Drawing.Graphics]::FromImage($bmp)
        $destRect = New-Object System.Drawing.Rectangle(0, 0, $w, $h)
        $g.DrawImage($img, $destRect, $rect, [System.Drawing.GraphicsUnit]::Pixel)
        $g.Dispose()
        $bmp.Save("i:\xampp\htdocs\vibecodeweb\images\logo_$idx.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)
        $bmp.Dispose()
    }
}
$img.Dispose()
Write-Host "Cropped into 6 images."
