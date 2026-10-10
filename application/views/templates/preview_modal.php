<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Modal Global Pratinjau Bukti Dukung (Survey / Brosur) Tanpa Download -->
<div class="modal fade" id="modalPreviewBukti" tabindex="-1" aria-labelledby="modalPreviewBuktiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 92vw;">
        <div class="modal-content border-0 shadow-lg" style="height: 90vh;">
            <!-- Header Modal -->
            <div class="modal-header bg-light border-bottom py-2.5 px-3 d-flex flex-column align-items-stretch gap-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <div class="rounded-3 p-1.5 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                        <div class="text-truncate">
                            <h6 class="modal-title fw-bold text-dark mb-0 text-truncate" id="modalPreviewBuktiLabel">
                                Pratinjau Bukti Dukung Survey Harga Pasar / Brosur Resmi
                            </h6>
                            <div class="small text-muted text-truncate" id="modalPreviewSubtitle">
                                <span class="badge bg-primary font-monospace me-1" id="badgePreviewKode">-</span>
                                <span class="fw-semibold text-secondary" id="textPreviewSkpd">-</span> &bull; 
                                <span id="textPreviewUraian" class="fst-italic">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <a href="#" id="btnPreviewNewTab" target="_blank" class="btn btn-sm btn-outline-secondary" title="Buka berkas di jendela / tab baru browser" data-bs-toggle="tooltip">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                        </a>
                        <a href="#" id="btnPreviewDownload" class="btn btn-sm btn-outline-primary" title="Unduh berkas ke komputer lokal" data-bs-toggle="tooltip">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-dismiss="modal" title="Tutup pratinjau">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Navigasi Cepat Tab Survey 1, Survey 2, Survey 3 -->
                <div class="d-flex align-items-center gap-2 pt-1 border-top" id="containerPreviewSlots">
                    <span class="small text-muted fw-semibold me-1"><i class="bi bi-collection me-1"></i>Pilih Berkas:</span>
                    <button type="button" class="btn btn-sm btn-outline-primary active btn-slot-switch" data-slot="1" id="btnSlot1">
                        <i class="bi bi-file-earmark-check me-1"></i> Bukti Survey 1 <span class="badge bg-light text-dark ms-1 size-badge">-</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-slot-switch" data-slot="2" id="btnSlot2">
                        <i class="bi bi-file-earmark-check me-1"></i> Bukti Survey 2 <span class="badge bg-light text-dark ms-1 size-badge">-</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-slot-switch" data-slot="3" id="btnSlot3">
                        <i class="bi bi-file-earmark-check me-1"></i> Bukti Survey 3 <span class="badge bg-light text-dark ms-1 size-badge">-</span>
                    </button>
                    <span class="ms-auto small text-muted text-truncate d-none d-md-inline" id="textNamaBerkasAktif"></span>
                </div>
            </div>

            <!-- Body Modal (Viewer Container) -->
            <div class="modal-body p-0 position-relative bg-dark-subtle d-flex flex-column align-items-center justify-content-center overflow-hidden" style="flex: 1 1 auto;">
                <!-- Loading Spinner -->
                <div id="previewLoader" class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Memuat berkas...</span>
                    </div>
                    <div class="mt-2 text-muted fw-semibold small">Menyiapkan tampilan dokumen...</div>
                </div>

                <!-- Frame PDF Viewer -->
                <iframe id="previewIframe" src="about:blank" class="w-100 h-100 border-0 d-none" style="min-height: 100%;"></iframe>

                <!-- Image Viewer Container -->
                <div id="previewImageWrapper" class="w-100 h-100 d-none justify-content-center align-items-center overflow-auto p-3 text-center" style="background: rgba(0,0,0,0.04);">
                    <img id="previewImage" src="" alt="Bukti Survey" class="img-fluid rounded shadow-sm" style="max-height: 80vh; max-width: 95%; object-fit: contain;">
                </div>

                <!-- Fallback untuk format Non-Previewable (Word / Excel) -->
                <div id="previewFallbackOffice" class="d-none text-center p-4 bg-white rounded-3 shadow-sm" style="max-width: 500px;">
                    <div class="text-warning display-4 mb-2"><i class="bi bi-file-earmark-zip"></i></div>
                    <h5 class="fw-bold text-dark">Berkas Dokumen Office</h5>
                    <p class="text-muted small mb-3">
                        Format berkas (<span class="font-monospace fw-bold" id="fallbackExt">.docx</span>) tidak dapat ditampilkan langsung di dalam peramban web. Silakan unduh dokumen untuk membukanya di aplikasi perangkat Anda.
                    </p>
                    <a href="#" id="btnFallbackDownload" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-download me-1"></i> Unduh Berkas Ini Sekarang
                    </a>
                </div>

                <!-- Error Container -->
                <div id="previewError" class="d-none text-center p-4 bg-white rounded-3 shadow-sm" style="max-width: 500px;">
                    <div class="text-danger display-4 mb-2"><i class="bi bi-exclamation-triangle"></i></div>
                    <h5 class="fw-bold text-dark">Berkas Tidak Ditemukan</h5>
                    <p class="text-muted small mb-0" id="previewErrorMessage">
                        Dokumen bukti survey belum diunggah atau tidak ditemukan di penyimpanan server.
                    </p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="modal-footer py-1.5 px-3 bg-light border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span>Verifikator dapat memeriksa keabsahan survey harga pasar langsung di layar tanpa mengunduh berkas.</span>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
