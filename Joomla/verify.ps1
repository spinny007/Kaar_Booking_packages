param([string]$PhpPath = 'php', [switch]$SkipBuild)
$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot
$repo = Split-Path $root -Parent
$errors = [System.Collections.Generic.List[string]]::new()
$warnings = [System.Collections.Generic.List[string]]::new()
function Require-Path([string]$Path) { if (-not (Test-Path -LiteralPath $Path)) { $errors.Add("Missing required path: $Path") } }

$required = @(
    'src\pkg_kaarbooking.xml','src\component\kaarbooking.xml','src\component\administrator\sql\install.mysql.utf8.sql',
    'src\component\script.php','src\component\administrator\sql\updates\mysql\0.2.0.sql','src\component\administrator\sql\updates\mysql\0.3.0.sql','src\component\administrator\sql\updates\mysql\0.4.0.sql','src\language\en-GB\en-GB.pkg_kaarbooking.sys.ini',
    'src\modules\mod_kaarbooking_booking_form\mod_kaarbooking_booking_form.xml',
    'src\modules\mod_kaarbooking_package_grid\mod_kaarbooking_package_grid.xml',
    'src\plugins\api-authentication\kaarbooking\kaarbooking.xml','src\plugins\webservices\kaarbooking\kaarbooking.xml',
    'src\plugins\task\kaarbooking\kaarbooking.xml','API_SECURITY.md','PRODUCT_PLAN.md'
)
foreach ($item in $required) { Require-Path (Join-Path $root $item) }
foreach ($item in @('src\component\administrator\src\View\Livetrips\HtmlView.php','src\component\administrator\src\View\Inspections\HtmlView.php','src\component\administrator\src\Controller\DriversController.php','src\component\administrator\src\Controller\PackagesController.php')) { Require-Path (Join-Path $root $item) }

$xmlFiles = Get-ChildItem -LiteralPath (Join-Path $root 'src') -Recurse -File -Filter *.xml
foreach ($file in $xmlFiles) { try { [xml](Get-Content -LiteralPath $file.FullName -Raw) | Out-Null } catch { $errors.Add("Invalid XML $($file.FullName): $($_.Exception.Message)") } }

$sql = Get-Content -LiteralPath (Join-Path $root 'src\component\administrator\sql\install.mysql.utf8.sql') -Raw
foreach ($table in @('vehicle_types','companies','vehicles','drivers','customers','otp_challenges','api_sessions','document_types','documents','vehicle_inspections','media','packages','package_items','departures','quotes','bookings','booking_resources','booking_history','payments','rides','driver_locations','notifications','app_layouts','audit_log')) {
    if ($sql -notmatch [regex]::Escape("#__kaar_$table")) { $errors.Add("Schema is missing #__kaar_$table") }
}
foreach ($column in @('access_hash','refresh_hash','otp_hash')) { if ($sql -notmatch [regex]::Escape($column)) { $errors.Add("Schema is missing hashed secret column $column") } }
if ($sql -match '(?i)`(access_token|refresh_token|otp)`') { $errors.Add('Schema appears to persist a plaintext OTP or token column.') }

$routeFile = Get-Content -LiteralPath (Join-Path $root 'src\plugins\webservices\kaarbooking\src\Extension\KaarBooking.php') -Raw
foreach ($route in @('auth/challenge','auth/verify','auth/refresh','auth/logout','vehicle-types','packages','bookings','rides')) { if ($routeFile -notmatch [regex]::Escape($route)) { $errors.Add("API route missing: $route") } }
if ($routeFile -match '(?i)kyc[^\r\n]*(download|export)') { $errors.Add('A customer KYC download/export API route is forbidden.') }
$configFile=Get-Content -LiteralPath (Join-Path $root 'src\component\administrator\config.xml') -Raw
foreach($field in @('api_enabled','api_otp_login_enabled','api_booking_enabled','api_realtime_enabled','api_kyc_enabled','driver_location_interval_seconds','google_maps_browser_key')){if($configFile -notmatch ('name="'+[regex]::Escape($field)+'"')){$errors.Add("Component option missing: $field")}}

$phpFiles = @(Get-ChildItem -LiteralPath (Join-Path $root 'src') -Recurse -File -Filter *.php) + @(Get-ChildItem -LiteralPath (Join-Path $repo 'WordPress') -Recurse -File -Filter *.php)
$phpCommand = Get-Command $PhpPath -ErrorAction SilentlyContinue
if ($phpCommand) {
    foreach ($file in $phpFiles) { $output = & $phpCommand.Source -l $file.FullName 2>&1; if ($LASTEXITCODE -ne 0) { $errors.Add("PHP lint failed for $($file.FullName): $output") } }
} else { $warnings.Add("PHP executable '$PhpPath' was not found; PHP lint and PHPUnit were not run. Pass -PhpPath with its full path.") }

if (-not $SkipBuild -and $errors.Count -eq 0) { & (Join-Path $root 'build.ps1') | Out-Null }
$package = Join-Path $root 'build\pkg_kaarbooking_0.4.0.zip'
if (-not $SkipBuild) {
    Require-Path $package
    if (Test-Path -LiteralPath $package) {
        Add-Type -AssemblyName System.IO.Compression.FileSystem
        $archive = [IO.Compression.ZipFile]::OpenRead($package)
        try {
            $names = @($archive.Entries | ForEach-Object FullName)
            foreach ($entry in @('pkg_kaarbooking.xml','com_kaarbooking.zip','mod_kaarbooking_booking_form.zip','mod_kaarbooking_package_grid.zip','plg_api-authentication_kaarbooking.zip','plg_webservices_kaarbooking.zip','plg_task_kaarbooking.zip')) { if ($entry -notin $names) { $errors.Add("Package archive missing $entry") } }
        } finally { $archive.Dispose() }
    }
}

$vendorPhpunit = Join-Path $root 'vendor\bin\phpunit.bat'
if ($phpCommand -and (Test-Path -LiteralPath $vendorPhpunit)) { & $vendorPhpunit --configuration (Join-Path $root 'phpunit.xml.dist'); if ($LASTEXITCODE -ne 0) { $errors.Add('PHPUnit failed.') } }
elseif ($phpCommand) { $warnings.Add('PHPUnit dependencies are not installed; run composer install in Joomla/.') }

foreach ($warning in $warnings) { Write-Warning $warning }
if ($errors.Count -gt 0) { $errors | ForEach-Object { Write-Error $_ }; exit 1 }
Write-Host "Verification passed: $($xmlFiles.Count) XML files, $($phpFiles.Count) PHP files, schema/routes/privacy/package checks."
