(function () {
    var exportStyleProperties = [
        'align-items', 'align-self', 'background', 'background-color', 'background-image',
        'background-position', 'background-repeat', 'background-size', 'border', 'border-bottom',
        'border-bottom-color', 'border-bottom-left-radius', 'border-bottom-right-radius',
        'border-bottom-style', 'border-bottom-width', 'border-collapse', 'border-left',
        'border-radius', 'border-right', 'border-spacing', 'border-top', 'border-top-color',
        'border-top-left-radius', 'border-top-right-radius', 'border-top-style', 'border-top-width',
        'box-sizing', 'color', 'display', 'flex', 'flex-basis', 'flex-direction', 'flex-grow',
        'flex-shrink', 'flex-wrap', 'font-family', 'font-size', 'font-style', 'font-weight',
        'gap', 'grid-template-columns', 'height', 'justify-content', 'letter-spacing',
        'line-height', 'margin', 'margin-bottom', 'margin-left', 'margin-right', 'margin-top',
        'object-fit', 'object-position', 'overflow', 'overflow-wrap', 'padding', 'padding-bottom',
        'padding-left', 'padding-right', 'padding-top', 'position', 'text-align', 'text-transform',
        'vertical-align', 'white-space', 'width'
    ];

    function copyComputedStyles(source, destination) {
        var computed = window.getComputedStyle(source);
        exportStyleProperties.forEach(function (property) {
            var value = computed.getPropertyValue(property);
            if (value) {
                destination.style.setProperty(property, value);
            }
        });

        Array.from(source.children).forEach(function (child, index) {
            if (destination.children[index]) {
                copyComputedStyles(child, destination.children[index]);
            }
        });
    }

    function imageToDataUrl(image) {
        if (image.src.indexOf('data:') === 0) {
            return Promise.resolve(image.src);
        }

        if (!image.complete || !image.naturalWidth) {
            return Promise.reject(new Error('An image is not ready yet. Please try the download again.'));
        }

        var canvas = document.createElement('canvas');
        canvas.width = image.naturalWidth;
        canvas.height = image.naturalHeight;
        var context = canvas.getContext('2d');
        if (!context) {
            return Promise.reject(new Error('PNG export is not supported by this browser.'));
        }
        context.drawImage(image, 0, 0);

        try {
            return Promise.resolve(canvas.toDataURL('image/png'));
        } catch (error) {
            return Promise.reject(new Error('An image could not be prepared for the download.'));
        }
    }

    function inlineImages(source, clone) {
        var sourceImages = source.querySelectorAll('img');
        return Promise.all(Array.from(clone.querySelectorAll('img')).map(function (image, index) {
            var original = sourceImages[index];
            var ready = original.decode ? original.decode() : Promise.resolve();
            return ready.then(function () {
                return imageToDataUrl(original);
            }).then(function (dataUrl) {
                image.src = dataUrl;
                image.removeAttribute('srcset');
            });
        }));
    }

    function addMobileTableLabels(clone) {
        clone.querySelectorAll('table.ecard-stack tbody tr').forEach(function (row) {
            row.querySelectorAll('td[data-label]').forEach(function (cell) {
                var label = document.createElement('span');
                label.textContent = cell.getAttribute('data-label');
                label.style.cssText = 'font-weight:800;color:#64748b;';
                cell.insertBefore(label, cell.firstChild);
            });
        });
    }

    function renderPng(element) {
        var rect = element.getBoundingClientRect();
        var zoom = parseFloat(window.getComputedStyle(element).zoom) || 1;
        var width = element.classList.contains('bheams-id-card')
            ? rect.width / zoom
            : Math.max(element.scrollWidth, rect.width);
        var height = element.classList.contains('bheams-id-card')
            ? rect.height / zoom
            : Math.max(element.scrollHeight, rect.height);
        if (!width || !height) {
            return Promise.reject(new Error('The selected section has no printable content.'));
        }
        var scale = Math.min(2, 8192 / width, 8192 / height, Math.sqrt(24000000 / (width * height)));
        var clone = element.cloneNode(true);

        copyComputedStyles(element, clone);
        clone.style.setProperty('zoom', '1');
        clone.style.setProperty('width', width + 'px');
        clone.style.setProperty('height', height + 'px');
        clone.style.setProperty('margin', '0');
        addMobileTableLabels(clone);

        return inlineImages(element, clone).then(function () {
            var markup = new XMLSerializer().serializeToString(clone);
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xhtml="http://www.w3.org/1999/xhtml" width="' +
                width * scale + '" height="' + height * scale + '" viewBox="0 0 ' + width + ' ' + height +
                '"><foreignObject width="100%" height="100%"><div xmlns="http://www.w3.org/1999/xhtml">' +
                markup + '</div></foreignObject></svg>';
            var image = new Image();
            // A self-contained data URL keeps foreignObject rendering origin-clean.
            // Blob-backed SVG images can taint the canvas and block PNG/PDF export.
            var svgUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);

            return new Promise(function (resolve, reject) {
                var renderTimeout = window.setTimeout(function () {
                    reject(new Error('PNG rendering took too long. Please try again.'));
                }, 10000);
                image.onload = function () {
                    window.clearTimeout(renderTimeout);
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.ceil(width * scale);
                    canvas.height = Math.ceil(height * scale);
                    var context = canvas.getContext('2d');
                    if (!context) {
                        reject(new Error('PNG export is not supported by this browser.'));
                        return;
                    }
                    try {
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                        context.drawImage(image, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(function (blob) {
                            if (!blob) {
                                reject(new Error('The PNG image could not be generated.'));
                                return;
                            }
                            resolve(blob);
                        }, 'image/png');
                    } catch (error) {
                        reject(error);
                    }
                };
                image.onerror = function () {
                    window.clearTimeout(renderTimeout);
                    reject(new Error('The selected section could not be rendered as a PNG.'));
                };
                image.src = svgUrl;
            });
        });
    }

    function toBytes(value) {
        return new TextEncoder().encode(value);
    }

    function joinBytes(chunks) {
        var size = chunks.reduce(function (total, chunk) { return total + chunk.length; }, 0);
        var result = new Uint8Array(size);
        var offset = 0;
        chunks.forEach(function (chunk) {
            result.set(chunk, offset);
            offset += chunk.length;
        });
        return result;
    }

    function jpegFromCanvas(canvas) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                if (!blob) {
                    reject(new Error('A PDF page could not be generated.'));
                    return;
                }
                blob.arrayBuffer().then(function (buffer) {
                    resolve({ bytes: new Uint8Array(buffer), width: canvas.width, height: canvas.height });
                }).catch(reject);
            }, 'image/jpeg', 0.92);
        });
    }

    function pdfFromPng(png, section) {
        var imageUrl = URL.createObjectURL(png);
        var image = new Image();

        return new Promise(function (resolve, reject) {
            image.onload = function () {
                URL.revokeObjectURL(imageUrl);
                try {
                var source = document.createElement('canvas');
                source.width = image.naturalWidth;
                source.height = image.naturalHeight;
                var sourceContext = source.getContext('2d');
                if (!sourceContext) {
                    reject(new Error('PDF export is not supported by this browser.'));
                    return;
                }
                sourceContext.drawImage(image, 0, 0);

                var pageWidthPoints = section === 'id' ? 85.6 / 25.4 * 72 : 297 / 25.4 * 72;
                var pageHeightPoints = section === 'id' ? 53.98 / 25.4 * 72 : 210 / 25.4 * 72;
                var pageImages;

                if (section === 'id') {
                    pageImages = [jpegFromCanvas(source)];
                } else {
                    var pagePixelWidth = 1200;
                    var pagePixelHeight = Math.round(pagePixelWidth * 210 / 297);
                    var sourceSliceHeight = Math.max(1, Math.floor(source.width * pagePixelHeight / pagePixelWidth));
                    pageImages = [];
                    for (var top = 0; top < source.height; top += sourceSliceHeight) {
                        var sliceHeight = Math.min(sourceSliceHeight, source.height - top);
                        var page = document.createElement('canvas');
                        page.width = pagePixelWidth;
                        page.height = pagePixelHeight;
                        var pageContext = page.getContext('2d');
                        if (!pageContext) {
                            reject(new Error('PDF export is not supported by this browser.'));
                            return;
                        }
                        pageContext.fillStyle = '#ffffff';
                        pageContext.fillRect(0, 0, page.width, page.height);
                        pageContext.drawImage(
                            source,
                            0, top, source.width, sliceHeight,
                            0, 0, pagePixelWidth, sliceHeight * pagePixelWidth / source.width
                        );
                        pageImages.push(jpegFromCanvas(page));
                    }
                }

                Promise.all(pageImages).then(function (pages) {
                    resolve(buildPdf(pages, pageWidthPoints, pageHeightPoints));
                }).catch(reject);
                } catch (error) {
                    reject(error);
                }
            };
            image.onerror = function () {
                URL.revokeObjectURL(imageUrl);
                reject(new Error('The PNG image could not be prepared as a PDF.'));
            };
            image.src = imageUrl;
        });
    }

    function buildPdf(pages, pageWidth, pageHeight) {
        var objectCount = 2 + pages.length * 3;
        var objects = new Array(objectCount + 1);
        var pageReferences = [];
        objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        pages.forEach(function (page, index) {
            var pageObject = 3 + index * 3;
            var contentObject = pageObject + 1;
            var imageObject = pageObject + 2;
            pageReferences.push(pageObject + ' 0 R');
            objects[pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' +
                pageWidth.toFixed(2) + ' ' + pageHeight.toFixed(2) +
                '] /Resources << /XObject << /Im0 ' + imageObject +
                ' 0 R >> >> /Contents ' + contentObject + ' 0 R >>';
            var content = toBytes('q\n' +
                pageWidth.toFixed(2) + ' 0 0 ' + pageHeight.toFixed(2) +
                ' 0 0 cm\n/Im0 Do\nQ\n');
            objects[contentObject] = joinBytes([
                toBytes('<< /Length ' + content.length + ' >>\nstream\n'),
                content,
                toBytes('endstream')
            ]);
            objects[imageObject] = joinBytes([
                toBytes('<< /Type /XObject /Subtype /Image /Width ' + page.width +
                    ' /Height ' + page.height +
                    ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' +
                    page.bytes.length + ' >>\nstream\n'),
                page.bytes,
                toBytes('\nendstream')
            ]);
        });
        objects[2] = '<< /Type /Pages /Kids [' + pageReferences.join(' ') +
            '] /Count ' + pages.length + ' >>';

        var header = new Uint8Array([37, 80, 68, 70, 45, 49, 46, 52, 10, 37, 226, 227, 207, 211, 10]);
        var chunks = [header];
        var offsets = new Array(objectCount + 1);
        var byteLength = header.length;

        for (var objectId = 1; objectId <= objectCount; objectId += 1) {
            var objectBytes = typeof objects[objectId] === 'string'
                ? toBytes(objects[objectId])
                : objects[objectId];
            var prefix = toBytes(objectId + ' 0 obj\n');
            var suffix = toBytes('\nendobj\n');
            offsets[objectId] = byteLength;
            chunks.push(prefix, objectBytes, suffix);
            byteLength += prefix.length + objectBytes.length + suffix.length;
        }

        var xrefOffset = byteLength;
        var xref = 'xref\n0 ' + (objectCount + 1) + '\n0000000000 65535 f \n';
        for (var entry = 1; entry <= objectCount; entry += 1) {
            xref += String(offsets[entry]).padStart(10, '0') + ' 00000 n \n';
        }
        xref += 'trailer\n<< /Size ' + (objectCount + 1) +
            ' /Root 1 0 R >>\nstartxref\n' + xrefOffset + '\n%%EOF';
        chunks.push(toBytes(xref));
        return new Blob([joinBytes(chunks)], { type: 'application/pdf' });
    }

    function downloadSection(section, format, button) {
        var element = section === 'id'
            ? document.querySelector('.bheams-id-card')
            : document.querySelector('.print-health-record');
        if (!element) {
            window.alert('The selected section is not available for download.');
            return;
        }

        button.disabled = true;
        var originalText = button.textContent;
        button.textContent = 'Preparing ' + format.toUpperCase() + '...';
        Promise.resolve().then(function () { return renderPng(element); })
            .then(function (png) {
                return format === 'pdf' ? pdfFromPng(png, section) : png;
            })
            .then(function (blob) {
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = section === 'id'
                    ? 'bheams-health-card.' + format
                    : 'bheams-health-information.' + format;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            })
            .catch(function (error) {
                window.alert(error.message || 'The ' + format.toUpperCase() + ' could not be downloaded.');
            })
            .finally(function () {
                button.disabled = false;
                button.textContent = originalText;
            });
    }

    document.querySelectorAll('[data-ecard-download][data-format]').forEach(function (button) {
        button.addEventListener('click', function () {
            downloadSection(
                button.getAttribute('data-ecard-download'),
                button.getAttribute('data-format'),
                button
            );
        });
    });
})();
