Add-Type -AssemblyName System.Drawing

$width = 1080
$height = 1350
$outputPath = "docs/marketing/kasbook-brochure-social.png"

function New-RoundedRectPath {
    param(
        [System.Drawing.Rectangle]$Rect,
        [int]$Radius
    )
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $diameter = $Radius * 2
    $arc = New-Object System.Drawing.Rectangle($Rect.X, $Rect.Y, $diameter, $diameter)
    $path.AddArc($arc, 180, 90)
    $arc.X = $Rect.Right - $diameter
    $path.AddArc($arc, 270, 90)
    $arc.Y = $Rect.Bottom - $diameter
    $path.AddArc($arc, 0, 90)
    $arc.X = $Rect.Left
    $path.AddArc($arc, 90, 90)
    $path.CloseFigure()
    return $path
}

$bmp = New-Object System.Drawing.Bitmap($width, $height)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit

$rect = New-Object System.Drawing.Rectangle(0, 0, $width, $height)
$grad = New-Object System.Drawing.Drawing2D.LinearGradientBrush(
    $rect,
    [System.Drawing.Color]::FromArgb(13, 39, 82),
    [System.Drawing.Color]::FromArgb(30, 100, 210),
    90
)
$g.FillRectangle($grad, $rect)

$whiteBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$mutedBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(230, 241, 255))
$badgeBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(245, 248, 255))
$badgeTextBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(23, 63, 135))
$ctaBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(7, 26, 56))

$fontBrand = New-Object System.Drawing.Font("Segoe UI Semibold", 30, [System.Drawing.FontStyle]::Bold)
$fontTitle = New-Object System.Drawing.Font("Segoe UI", 64, [System.Drawing.FontStyle]::Bold)
$fontSubtitle = New-Object System.Drawing.Font("Segoe UI", 24, [System.Drawing.FontStyle]::Regular)
$fontFeature = New-Object System.Drawing.Font("Segoe UI", 28, [System.Drawing.FontStyle]::Bold)
$fontSmall = New-Object System.Drawing.Font("Segoe UI", 21, [System.Drawing.FontStyle]::Regular)
$fontCta = New-Object System.Drawing.Font("Segoe UI", 28, [System.Drawing.FontStyle]::Bold)

$paddingX = 80
$topY = 80

$g.DrawString("KASBOOK POS V4", $fontBrand, $mutedBrush, $paddingX, $topY)
$g.DrawString("Grow Faster", $fontTitle, $whiteBrush, $paddingX, $topY + 70)
$g.DrawString("With Smart POS + Inventory", $fontTitle, $whiteBrush, $paddingX, $topY + 160)
$g.DrawString("Billing, stock, purchases, suppliers, and reports in one system.", $fontSubtitle, $mutedBrush, $paddingX, $topY + 280)

$badgeRect = New-Object System.Drawing.Rectangle(80, 400, 920, 70)
$badgePath = New-RoundedRectPath -Rect $badgeRect -Radius 18
$g.FillPath($badgeBrush, $badgePath)
$badgePath.Dispose()
$g.DrawString("Perfect for retail shops, mini marts, wholesale stores", $fontSmall, $badgeTextBrush, 108, 418)

$features = @(
    "Fast POS Billing",
    "Inventory Control",
    "Barcode Workflow",
    "Customer/Supplier Ledger",
    "Sales & Purchase Reports",
    "Multi-Store Ready"
)

$fy = 520
foreach ($f in $features) {
    $g.FillEllipse($whiteBrush, 86, $fy + 9, 14, 14)
    $g.DrawString($f, $fontFeature, $whiteBrush, 120, $fy)
    $fy += 84
}

$ctaRect = New-Object System.Drawing.Rectangle(60, 1060, 960, 210)
$ctaPath = New-RoundedRectPath -Rect $ctaRect -Radius 24
$g.FillPath($ctaBrush, $ctaPath)
$ctaPath.Dispose()
$g.DrawString("Book Your Demo Today", $fontCta, $whiteBrush, 95, 1100)
$g.DrawString("WhatsApp/Call: +92 345 9079213", $fontSmall, $mutedBrush, 95, 1150)
$g.DrawString("khybersoft.com  |  sales@khybersoft.com", $fontSmall, $mutedBrush, 95, 1188)

$dir = Split-Path -Parent $outputPath
if (-not (Test-Path $dir)) {
    New-Item -ItemType Directory -Path $dir | Out-Null
}

$bmp.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Png)

$g.Dispose()
$bmp.Dispose()
$grad.Dispose()
$whiteBrush.Dispose()
$mutedBrush.Dispose()
$badgeBrush.Dispose()
$badgeTextBrush.Dispose()
$ctaBrush.Dispose()
$fontBrand.Dispose()
$fontTitle.Dispose()
$fontSubtitle.Dispose()
$fontFeature.Dispose()
$fontSmall.Dispose()
$fontCta.Dispose()

Write-Output "Created: $outputPath"
