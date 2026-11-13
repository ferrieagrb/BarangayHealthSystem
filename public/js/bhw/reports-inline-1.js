document.addEventListener('DOMContentLoaded', function () {
    const reportForm = document.getElementById('reportBuilder');
    const exportButtons = reportForm ? Array.from(reportForm.querySelectorAll('button[name="export_format"]')) : [];
    const exportFeedback = document.getElementById('reportExportFeedback');
    const exportTypes = {
        xlsx: { label: 'Excel', mime: 'spreadsheetml', fallback: 'custom-report.xlsx' },
        pdf: { label: 'PDF', mime: 'pdf', fallback: 'custom-report.pdf' }
    };
    let exportInProgress = false;

    reportForm?.addEventListener('submit', async function (event) {
        const excelButton = event.submitter;
        const type = exportTypes[excelButton?.value];
        if (!type || !exportButtons.includes(excelButton) || exportInProgress) {
            if (type) event.preventDefault();
            return;
        }

        event.preventDefault();
        exportInProgress = true;
        exportFeedback.hidden = true;
        exportFeedback.textContent = '';
        exportButtons.forEach((button) => { button.disabled = true; });
        excelButton.setAttribute('aria-busy', 'true');
        const originalLabel = excelButton.textContent;
        excelButton.textContent = 'Preparing ' + type.label + '...';
        if (window.showLoader) window.showLoader();

        try {
            const requestUrl = new URL(reportForm.action, window.location.href);
            const query = new URLSearchParams(new FormData(reportForm));
            query.set('export_format', excelButton.value);
            requestUrl.search = query.toString();

            const response = await fetch(requestUrl, {
                credentials: 'same-origin',
                headers: { Accept: '*/*' }
            });
            const contentType = response.headers.get('Content-Type') || '';
            if (!response.ok || !contentType.includes(type.mime)) {
                const responseText = await response.text();
                const errorDocument = new DOMParser().parseFromString(responseText, 'text/html');
                const validationMessage = errorDocument.querySelector('.report-validation-errors, .alert-danger, .errors')?.textContent.trim();
                throw new Error(response.redirected
                    ? 'Your session may have expired. Reload the page and sign in before exporting.'
                    : validationMessage || 'The server did not return a ' + type.label + ' file. Check the report filters and try again.');
            }

            const filenameMatch = response.headers.get('Content-Disposition')?.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
            const filename = filenameMatch
                ? decodeURIComponent(filenameMatch[1] || filenameMatch[2])
                : type.fallback;
            const fileUrl = URL.createObjectURL(await response.blob());
            const downloadLink = document.createElement('a');
            downloadLink.href = fileUrl;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
            window.setTimeout(() => URL.revokeObjectURL(fileUrl), 60000);
        } catch (error) {
            exportFeedback.textContent = error instanceof Error ? error.message : type.label + ' export failed. Please try again.';
            exportFeedback.hidden = false;
        } finally {
            exportInProgress = false;
            exportButtons.forEach((button) => { button.disabled = false; });
            excelButton.removeAttribute('aria-busy');
            excelButton.textContent = originalLabel;
            if (window.hideLoader) window.hideLoader();
        }
    });

});
