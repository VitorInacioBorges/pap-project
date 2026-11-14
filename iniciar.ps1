$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$phpPath = Join-Path $PSScriptRoot 'storage\app\private\runtime\php\php.exe'
$env:Path = "$(Split-Path -Parent $phpPath);$env:Path"

$queueProcess = Start-Process -FilePath $phpPath `
    -ArgumentList 'artisan', 'queue:work', '--sleep=1', '--tries=3', '--no-interaction' `
    -WorkingDirectory $PSScriptRoot -WindowStyle Hidden -PassThru `
    -RedirectStandardOutput (Join-Path $PSScriptRoot 'storage\logs\queue.log') `
    -RedirectStandardError (Join-Path $PSScriptRoot 'storage\logs\queue-error.log')

try {
    & $phpPath artisan serve --host=127.0.0.1 --port=8000 --tries=1 --no-interaction
} finally {
    if (-not $queueProcess.HasExited) {
        Stop-Process -Id $queueProcess.Id
    }
}
