$filePath = Join-Path (Get-Location) "start.bat"
if (Test-Path $filePath) {
    $content = [System.IO.File]::ReadAllText($filePath)
    # Create UTF-8 encoding object without BOM
    $utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($filePath, $content, $utf8WithoutBom)
    Write-Host "SUCCESS: start.bat has been saved as UTF-8 WITHOUT BOM!"
} else {
    Write-Host "ERROR: start.bat not found!"
}
