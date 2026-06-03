Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Bitmap]::FromFile("i:\xampp\htdocs\vibecodeweb\images\logo_5.jpg")
$bgColor = $img.GetPixel(0,0)
$img.MakeTransparent($bgColor)
$img.Save("i:\xampp\htdocs\vibecodeweb\images\logo_5_transparent.png", [System.Drawing.Imaging.ImageFormat]::Png)
$img.Dispose()
Write-Host "Success"
