$filePath = Join-Path (Get-Location) "start.bat"
if (Test-Path $filePath) {
    $content = [System.IO.File]::ReadAllText($filePath)
    # Normalize double carriage returns
    $content = $content -replace "\r\r\n", "`r`n"
    $content = $content -replace "\r\n", "`n"
    $content = $content -replace "\r", "`n"
    $content = $content -replace "\n", "`r`n"
    [System.IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
    Write-Host "SUCCESS: start.bat has been sanitized with standard CRLF line endings!"
} else {
    Write-Host "ERROR: start.bat not found!"
}
