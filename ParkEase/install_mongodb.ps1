$url = "https://windows.php.net/downloads/pecl/releases/mongodb/1.20.1/php_mongodb-1.20.1-8.4-ts-vs17-x64.zip"
$zipPath = "mongodb.zip"
$extPath = "C:\php-8.4.3\ext\"
$iniPath = "C:\php-8.4.3\php.ini"

Write-Host "Downloading $url"
Invoke-WebRequest -Uri $url -OutFile $zipPath

Write-Host "Extracting..."
Expand-Archive -Path $zipPath -DestinationPath "mongodb_extracted" -Force

Write-Host "Copying DLL..."
Copy-Item "mongodb_extracted\php_mongodb.dll" -Destination $extPath -Force

Write-Host "Updating php.ini..."
$iniContent = Get-Content $iniPath
if ($iniContent -notcontains "extension=mongodb") {
    Add-Content -Path $iniPath -Value "extension=mongodb"
}

Write-Host "Cleaning up..."
Remove-Item $zipPath
Remove-Item "mongodb_extracted" -Recurse -Force

Write-Host "Done!"
