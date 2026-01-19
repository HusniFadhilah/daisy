// ====== IMAGE HELPERS (hapus saat Save) ======
function extractImgSrcs(html) {
    const doc = new DOMParser().parseFromString(html || '', 'text/html');
    return new Set(
        [...doc.querySelectorAll('img')]
            .map(img => img.getAttribute('src'))
            .filter(Boolean)
            .filter(src => !src.startsWith('data:')) // skip base64
    );
}

function isSrcUsedInOtherEditors(editorInstances, src, exceptFieldId) {
    for (const k in editorInstances) {
        if (k === exceptFieldId) continue;
        const ed = editorInstances[k];
        if (!ed) continue;
        const html = ed.getContent({ format: 'html' });
        if (html && (html.includes(`src="${src}"`) || html.includes(`src='${src}'`))) {
            return true;
        }
    }
    return false;
}

async function deleteImageOnServer(url, csrf, src) {
    // ganti URL ini sesuai route kamu
    const res = await fetch(`${url}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ pengajuan_id: pengajuanId, src })
    });

    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        console.warn('Delete image failed:', src, data);
    }
}
