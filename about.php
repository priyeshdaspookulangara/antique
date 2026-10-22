<?php
$page_title = "About Gallery & Curatorial Provenance | Antique Artifacts";
$page_desc = "Learn about our curatorial methodology, 1-of-1 artifact authentication process, and commitment to historical preservation.";

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold header-brand mb-3">Our Curatorial Philosophy</h1>
                <p class="lead text-secondary max-w-700 mx-auto">Connecting private collectors and museums with genuine, non-replicated 1-of-1 historical relics.</p>
            </div>

            <div class="card shadow-sm p-4 p-md-5 mb-5 bg-white">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80" alt="Curator Inspection" class="img-fluid rounded shadow">
                    </div>
                    <div class="col-md-6">
                        <h2 class="h4 header-brand mb-3">The 1-of-1 Guarantee</h2>
                        <p class="text-secondary lh-lg">Every item in our gallery represents a unique piece of history. Unlike commercial art prints or manufactured antiquities, our repository strictly catalogs single-edition artifacts that cannot be duplicated or restocked.</p>
                        <p class="text-secondary lh-lg">When an artifact is acquired by a collector, its status is permanently locked as <strong>Sold</strong> in our public ledger, preserving its provenance for posterity.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4 bg-white border rounded shadow-sm h-100">
                        <div class="fs-1 mb-2">🔍</div>
                        <h3 class="h5 fw-bold mb-2">Patina & Material Verification</h3>
                        <p class="small text-muted mb-0">Rigorous metallurgical, radiocarbon, and spectroscopic analysis to confirm true age and origin.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white border rounded shadow-sm h-100">
                        <div class="fs-1 mb-2">📜</div>
                        <h3 class="h5 fw-bold mb-2">Documented Provenance</h3>
                        <p class="small text-muted mb-0">Complete paper trails including excavation records, historic gallery archives, and ownership history.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-white border rounded shadow-sm h-100">
                        <div class="fs-1 mb-2">🎥</div>
                        <h3 class="h5 fw-bold mb-2">360° Walkthrough Inspection</h3>
                        <p class="small text-muted mb-0">High-definition video inspections enabling remote collectors to examine every micro-detail and patina texture.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
