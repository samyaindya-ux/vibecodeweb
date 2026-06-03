Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("i:\xampp\htdocs\vibecodeweb\images\5logo.jpg")

$cols = 3
$rows = 2
$w = [Math]::Floor($img.Width / $cols)
$h = [Math]::Floor($img.Height / $rows)

for ($r = 0; $r -lt $rows; $r++) {
    for ($c = 0; $c -lt $cols; $c++) {
        $idx = $r * $cols + $c
        $rect = New-Object System.Drawing.Rectangle($c*$w, $r*$h, $w, $h)
        $bmp = New-Object System.Drawing.Bitmap($w, $h)
        $g = [System.Drawing.Graphics]::FromImage($bmp)
        $g.DrawImage($img, (New-Object System.Drawing.Rectangle(0,0,$w,$h)), $rect, [System.Drawing.GraphicsUnit]::Pixel)
        $g.Dispose()
        $bmp.Save("i:\xampp\htdocs\vibecodeweb\images\logo_$idx.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)
        $bmp.Dispose()
    }
}
$img.Dispose()
Write-Host "Cropped into 6 images."
