<?php
$page_title = "Collector Contact & Inquiry | Antique Artifacts";
$page_desc = "Get in touch with our curatorial team to inquire about specific 1-of-1 artifacts, private viewings, or consignment.";

require_once __DIR__ . '/includes/header.php';

$submitted = false;
$name = $email = $artifact_ref = $message = "";

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $artifact_ref = trim($_POST['artifact_ref'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        $submitted = true;
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm p-4 p-md-5 bg-white">
                <h1 class="h3 header-brand text-center mb-2">Collector Contact & Inquiry</h1>
                <p class="text-muted text-center mb-4">Direct communication channel with our lead artifact curators</p>

                <?php if ($submitted): ?>
                    <div class="alert alert-success p-4 text-center">
                        <h4 class="h5 alert-heading">Inquiry Received</h4>
                        <p class="mb-0">Thank you, <strong><?= htmlspecialchars($name) ?></strong>. A curator will review your request regarding <em><?= htmlspecialchars($artifact_ref ?: 'our collection') ?></em> and reply to <strong><?= htmlspecialchars($email) ?></strong> within 24 hours.</p>
                    </div>
                <?php else: ?>
                    <form action="contact.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Lord / Lady / Dr. Name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="collector@domain.com">
                            </div>
                            <div class="col-md-12">
                                <label for="artifact_ref" class="form-label fw-bold">Artifact Reference / Title (Optional)</label>
                                <input type="text" class="form-control" id="artifact_ref" name="artifact_ref" placeholder="e.g. 18th Century Bronze Dynasty Vessel">
                            </div>
                            <div class="col-md-12">
                                <label for="message" class="form-label fw-bold">Inquiry Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Please provide details regarding acquisition, private viewing, or provenance verification..."></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-dark btn-lg px-5">Submit Private Inquiry</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
