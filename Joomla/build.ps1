param(
    [string]$OutputDirectory = (Join-Path $PSScriptRoot 'build')
)
$ErrorActionPreference = 'Stop'
$source = Join-Path $PSScriptRoot 'src'
$staging = Join-Path ([System.IO.Path]::GetTempPath()) ('kaarbooking-build-' + [guid]::NewGuid().ToString('N'))
$packageStage = Join-Path $staging 'package'
New-Item -ItemType Directory -Path $OutputDirectory -Force | Out-Null
New-Item -ItemType Directory -Path $packageStage -Force | Out-Null

function New-ExtensionZip([string]$SourcePath, [string]$ZipName) {
    $destination = Join-Path $packageStage $ZipName
    Compress-Archive -Path (Join-Path $SourcePath '*') -DestinationPath $destination -CompressionLevel Optimal -Force
}

try {
    New-ExtensionZip (Join-Path $source 'component') 'com_kaarbooking.zip'
    New-ExtensionZip (Join-Path $source 'modules\mod_kaarbooking_booking_form') 'mod_kaarbooking_booking_form.zip'
    New-ExtensionZip (Join-Path $source 'modules\mod_kaarbooking_package_grid') 'mod_kaarbooking_package_grid.zip'
    New-ExtensionZip (Join-Path $source 'plugins\webservices\kaarbooking') 'plg_webservices_kaarbooking.zip'
    New-ExtensionZip (Join-Path $source 'plugins\api-authentication\kaarbooking') 'plg_api-authentication_kaarbooking.zip'
    New-ExtensionZip (Join-Path $source 'plugins\task\kaarbooking') 'plg_task_kaarbooking.zip'
    Copy-Item -LiteralPath (Join-Path $source 'pkg_kaarbooking.xml') -Destination (Join-Path $packageStage 'pkg_kaarbooking.xml')
    $packageZip = Join-Path $OutputDirectory 'pkg_kaarbooking_0.1.0.zip'
    Compress-Archive -Path (Join-Path $packageStage '*') -DestinationPath $packageZip -CompressionLevel Optimal -Force

    $wpZip = Join-Path $OutputDirectory 'plg_wordpress_kaar_booking_0.1.0.zip'
    Compress-Archive -Path (Join-Path (Split-Path $PSScriptRoot -Parent) 'WordPress\kaar-booking') -DestinationPath $wpZip -CompressionLevel Optimal -Force
    Get-Item -LiteralPath $packageZip, $wpZip | Select-Object FullName, Length, LastWriteTime
}
finally {
    if (Test-Path -LiteralPath $staging) {
        Remove-Item -LiteralPath $staging -Recurse -Force
    }
}
