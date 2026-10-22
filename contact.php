<?php
$page_title = "Contact Curators | Patina Decor";
$page_desc = "Get in touch with Patina Decor's curatorial team to inquire about specific 1-of-1 artifacts, private viewings, or custom wall set orders.";

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

<div class="wrap my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm p-4 p-md-5 bg-white border-0">
                <h1 class="h3 text-center mb-2" style="font-family:var(--serif);">Patina Decor Inquiry</h1>
                <p class="text-muted text-center mb-4">Direct communication channel with our lead artifact curators</p>

                <?php if ($submitted): ?>
                    <div class="alert alert-success p-4 text-center">
                        <h4 class="h5 alert-heading" style="font-family:var(--serif);">Inquiry Received</h4>
                        <p class="mb-0">Thank you, <strong><?= htmlspecialchars($name) ?></strong>. A Patina Decor curator will review your request regarding <em><?= htmlspecialchars($artifact_ref ?: 'our collection') ?></em> and reply to <strong><?= htmlspecialchars($email) ?></strong> within 24 hours.</p>
                    </div>
                <?php else: ?>
                    <form action="contact.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold small">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Full Name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold small">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="collector@domain.com">
                            </div>
                            <div class="col-md-12">
                                <label for="artifact_ref" class="form-label fw-bold small">Artifact Reference / Title (Optional)</label>
                                <input type="text" class="form-control" id="artifact_ref" name="artifact_ref" placeholder="e.g. Ornate Hanging Bell or Kathakali Mask">
                            </div>
                            <div class="col-md-12">
                                <label for="message" class="form-label fw-bold small">Inquiry Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Please provide details regarding acquisition, custom wall sets, or condition details..."></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn-brass border-0 px-5">Submit Inquiry</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
