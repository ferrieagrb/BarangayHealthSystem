param([switch]$SampleResults)
$ErrorActionPreference = 'Stop'
$baseName = 'BHEAMS-Website-Test-Cases-2026-11-21'
if ($SampleResults) { $baseName += '-Sample-Results' }
$documentPath = Join-Path (Split-Path $PSScriptRoot -Parent) ($baseName + '.docx')
$pdfPath = [IO.Path]::ChangeExtension($documentPath, '.pdf')
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($documentPath, $false, $true, $false)
    $document.Repaginate()
    $pages = $document.ComputeStatistics(2)
    $document.ExportAsFixedFormat($pdfPath, 17)
    $result = [ordered]@{
        Renderer = 'Microsoft Word'
        Pages = $pages
        Tables = $document.Tables.Count
        Words = $document.ComputeStatistics(0)
        Document = $documentPath
        PDF = $pdfPath
    }
    $validationName = if ($SampleResults) { 'sample-render-validation.json' } else { 'render-validation.json' }
    $result | ConvertTo-Json | Set-Content -LiteralPath (Join-Path $PSScriptRoot $validationName) -Encoding UTF8
    $result | ConvertTo-Json
} finally {
    if ($document) {
        $document.Close(0)
        [void][Runtime.InteropServices.Marshal]::ReleaseComObject($document)
    }
    if ($word) {
        $word.Quit()
        [void][Runtime.InteropServices.Marshal]::ReleaseComObject($word)
    }
}
