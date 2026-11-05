import html2canvas from 'html2canvas';
import { jsPDF } from 'jspdf';

function targetNota() {
    return document.querySelector('.nota');
}

function namaBerkas(extensi) {
    const id = document.body.dataset.orderId || 'nota';

    return `nota-pesanan-${id}.${extensi}`;
}

async function tangkapNota() {
    const nota = targetNota();

    if (! nota) {
        return null;
    }

    return html2canvas(nota, { scale: 2, backgroundColor: '#ffffff', useCORS: true });
}

async function unduhPdf() {
    const tombol = document.getElementById('btn-pdf');
    const canvas = await tangkapNota();

    if (! canvas) {
        return;
    }

    const orientasi = canvas.width > canvas.height ? 'landscape' : 'portrait';
    const pdf = new jsPDF({ orientation: orientasi, unit: 'px', format: [canvas.width, canvas.height], hotfixes: ['px_scaling'] });

    pdf.addImage(canvas.toDataURL('image/jpeg', 0.94), 'JPEG', 0, 0, canvas.width, canvas.height);
    pdf.save(namaBerkas('pdf'));

    if (tombol) {
        tombol.disabled = false;
    }
}

async function unduhJpg() {
    const canvas = await tangkapNota();

    if (! canvas) {
        return;
    }

    const tautan = document.createElement('a');

    tautan.href = canvas.toDataURL('image/jpeg', 0.92);
    tautan.download = namaBerkas('jpg');
    tautan.click();
}

function pasang(id, handler) {
    const tombol = document.getElementById(id);

    if (! tombol) {
        return;
    }

    tombol.addEventListener('click', async function () {
        tombol.disabled = true;

        try {
            await handler();
        } finally {
            tombol.disabled = false;
        }
    });
}

pasang('btn-pdf', unduhPdf);
pasang('btn-jpg', unduhJpg);
