param(
    [string] $OutputDirectory = (Join-Path (Split-Path $PSScriptRoot -Parent) '..\releases')
)

$ErrorActionPreference = 'Stop'

$repository = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$commit = (git -C $repository rev-parse HEAD).Trim()
if ($LASTEXITCODE -ne 0 -or $commit -notmatch '^[0-9a-f]{40}$') {
    throw 'A committed Git revision is required to build a release.'
}

$composer = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composer) {
    $composerPath = Join-Path $env:LOCALAPPDATA 'ComposerSetup\bin\composer.bat'
    if (-not (Test-Path -LiteralPath $composerPath)) {
        throw 'Composer was not found in PATH or ComposerSetup.'
    }
    $composerCommand = $composerPath
} else {
    $composerCommand = $composer.Path
    if (-not $composerCommand) {
        $composerCommand = $composer.Source
    }
}

$releaseName = 'encuestaselectorales-' + $commit.Substring(0, 12)
$workingRoot = Join-Path ([IO.Path]::GetTempPath()) ($releaseName + '-' + [guid]::NewGuid().ToString('N'))
$sourceArchive = Join-Path $workingRoot 'source.zip'
$stage = Join-Path $workingRoot 'stage'
$outputRoot = [IO.Path]::GetFullPath($OutputDirectory)
$outputArchive = Join-Path $outputRoot ($releaseName + '.zip')

New-Item -ItemType Directory -Path $workingRoot, $stage, $outputRoot -Force | Out-Null

git -C $repository archive --format=zip --output=$sourceArchive HEAD
if ($LASTEXITCODE -ne 0) {
    throw 'git archive failed.'
}

Expand-Archive -LiteralPath $sourceArchive -DestinationPath $stage

& $composerCommand install `
    --working-dir=$stage `
    --no-dev `
    --no-interaction `
    --prefer-dist `
    --optimize-autoloader `
    --no-progress
if ($LASTEXITCODE -ne 0) {
    throw 'Composer production install failed.'
}

$releaseMetadata = [ordered]@{
    commit = $commit
    built_at_utc = [DateTimeOffset]::UtcNow.ToString('O')
    environment_file = '../.env'
    document_root = 'public/'
    health_endpoint = '/api/health'
}
$releaseMetadata | ConvertTo-Json | Set-Content -LiteralPath (Join-Path $stage 'RELEASE.json') -Encoding utf8

Compress-Archive -Path (Join-Path $stage '*') -DestinationPath $outputArchive -CompressionLevel Optimal -Force

Write-Output $outputArchive
