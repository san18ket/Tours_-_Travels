<?php
/**
 * VoyageGo — Customer Profile & Travel Ledger View
 */
?>

<?php
$customerName = (!empty($customer['title']) ? e($customer['title']) . '. ' : '') . e($customer['name']);
$headerActions = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;">'
    . '<a href="' . url('/office/customers/' . $customer['id'] . '/edit') . '" class="btn btn-navy btn-sm">Edit Profile</a>'
    . '<a href="' . url('/office/bookings/create?customer_id=' . $customer['id']) . '" class="btn btn-primary btn-sm">+ New Booking</a>'
    . '<a href="' . url('/office/customers') . '" class="btn btn-outline btn-sm">Back to Directory</a>'
    . '</div>';
?>
<?= render_page_header([
    'title'       => $customerName,
    'subtitle'    => 'Customer Reference: ' . e($customer['customer_code']) . ' • Registered on ' . format_date($customer['created_at']),
    'breadcrumbs' => [
        ['label' => 'Customers', 'url' => url('/office/customers')],
        ['label' => e($customer['customer_code']), 'url' => ''],
    ],
    'actions'     => $headerActions,
    'image'       => 'header-customers.jpg',
    'badge'       => ucfirst($customer['status'] ?? 'active'),
]) ?>

<!-- Profile Grid -->
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;" class="vg-split-2">
    <!-- Left Column: Customer Profile Info -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Profile Identity Hero Card -->
        <?php
            $avatarUrl = customer_avatar_url($customer['profile_photo'] ?? $customer['avatar'] ?? null);
            $initial = strtoupper(mb_substr($customer['name'] ?? 'C', 0, 1));
            $docUrl = customer_document_url($customer['id_document_file'] ?? null);
            $isDocPdf = !empty($customer['id_document_file']) && str_ends_with(strtolower($customer['id_document_file']), '.pdf');
        ?>
        <div class="vg-card" style="text-align: center; padding: 1.5rem 1.25rem;">
            <div style="width: 88px; height: 88px; margin: 0 auto 1rem auto; position: relative;">
                <?php if (!empty($avatarUrl)): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="<?= e($customer['name']) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #176B5B; box-shadow: 0 4px 14px rgba(23, 107, 91, 0.2);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div style="display: none; width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%); color: #FFF; font-size: 2rem; font-weight: 800; align-items: center; justify-content: center; border: 3px solid #FFF; box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);">
                        <?= e($initial) ?>
                    </div>
                <?php else: ?>
                    <div style="width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, #176B5B 0%, #0F493E 100%); color: #FFF; font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 3px solid #FFF; box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);">
                        <?= e($initial) ?>
                    </div>
                <?php endif; ?>
            </div>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #0C2B52; margin: 0 0 0.35rem 0;">
                <?= $customerName ?>
            </h3>
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center; margin-bottom: 0.5rem;">
                <span style="background: #E8F4F0; color: #176B5B; border: 1px solid #B2D8CF; border-radius: 6px; padding: 2px 8px; font-family: monospace; font-size: 0.8rem; font-weight: 700;">
                    <?= e($customer['customer_code']) ?>
                </span>
                <span class="status-badge bg-<?= ($customer['status'] ?? 'active') === 'active' ? 'completed' : 'cancelled' ?>" style="font-size: 0.76rem;">
                    <?= ucfirst($customer['status'] ?? 'active') ?>
                </span>
            </div>
        </div>

        <!-- Contact Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Contact Information</span>
            </div>
            <div class="card-body" style="font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Primary Mobile</span>
                    <strong style="color: var(--vg-navy-900); font-size: 1rem;"><a href="tel:<?= e($customer['phone']) ?>" style="color: inherit; text-decoration: none;"><?= e($customer['phone']) ?></a></strong>
                </div>

                <?php if (!empty($customer['alternate_phone'])): ?>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Alternate Phone</span>
                    <span><a href="tel:<?= e($customer['alternate_phone']) ?>" style="color: inherit; text-decoration: none;"><?= e($customer['alternate_phone']) ?></a></span>
                </div>
                <?php endif; ?>

                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Email Address</span>
                    <span><?= !empty($customer['email']) ? '<a href="mailto:' . e($customer['email']) . '">' . e($customer['email']) . '</a>' : '<em style="color:var(--vg-text-muted);">Not provided</em>' ?></span>
                </div>

                <?php if (!empty($customer['gender']) || !empty($customer['date_of_birth'])): ?>
                <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <?php if (!empty($customer['gender'])): ?>
                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Gender</span>
                        <span style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $customer['gender'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($customer['date_of_birth'])): ?>
                    <div>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Birth Date</span>
                        <span><?= format_date($customer['date_of_birth']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Address Card -->
        <div class="vg-card">
            <div class="card-header">
                <span class="card-title">Billing &amp; Registered Address</span>
            </div>
            <div class="card-body" style="font-size: 0.9rem;">
                <?php if (!empty($customer['address_line']) || !empty($customer['city'])): ?>
                    <p style="margin: 0; line-height: 1.5; color: var(--vg-navy-900);">
                        <?= !empty($customer['address_line']) ? e($customer['address_line']) . '<br>' : '' ?>
                        <?= !empty($customer['city']) ? e($customer['city']) : '' ?><?= !empty($customer['state']) ? ', ' . e($customer['state']) : '' ?><?= !empty($customer['postal_code']) ? ' - ' . e($customer['postal_code']) : '' ?><br>
                        <strong><?= e($customer['country']) ?></strong>
                    </p>
                <?php else: ?>
                    <p style="margin: 0; color: var(--vg-text-muted); font-style: italic;">No physical address recorded on file.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Verification Documents & Scans Card -->
        <div class="vg-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title">Identity &amp; Documents</span>
                <span style="font-size: 0.75rem; background: #E8F4F0; color: #176B5B; padding: 2px 8px; border-radius: 4px; font-weight: 700;">Verified ID</span>
            </div>
            <div class="card-body" style="font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Document Type</span>
                    <strong style="color: var(--vg-navy-900); text-transform: capitalize;"><?= !empty($customer['id_type']) ? e(str_replace('_', ' ', $customer['id_type'])) : 'Aadhaar / National ID' ?></strong>
                </div>

                <?php if (!empty($customer['id_number'])): ?>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Document Number</span>
                    <code style="font-size: 0.92rem; background: #F1F5F9; padding: 2px 6px; border-radius: 4px; color: #0C2B52; font-weight: 600;"><?= e($customer['id_number']) ?></code>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer['id_expiry'])): ?>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block;">Expiry Date</span>
                    <span><?= format_date($customer['id_expiry']) ?></span>
                </div>
                <?php endif; ?>

                <!-- Document Attachment Preview & Download -->
                <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block; margin-bottom: 0.4rem;">Attached Scan / File</span>
                    <?php if (!empty($docUrl)): ?>
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem; min-width: 0;">
                                <?php if ($isDocPdf): ?>
                                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= e($docUrl) ?>" alt="Doc thumbnail" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; border: 1px solid #CBD5E1; flex-shrink: 0;">
                                <?php endif; ?>
                                <div style="min-width: 0;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= e(basename($customer['id_document_file'])) ?>
                                    </div>
                                    <span style="font-size: 0.72rem; color: #10B981; font-weight: 600;">
                                        <i class="fa-solid fa-circle-check"></i> File on Record
                                    </span>
                                </div>
                            </div>
                            <a href="<?= e($docUrl) ?>" target="_blank" download class="btn btn-sm btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem; font-weight: 700; flex-shrink: 0;">
                                <i class="fa-solid fa-arrow-down-to-bracket"></i> View / Download
                            </a>
                        </div>
                    <?php else: ?>
                        <em style="color: var(--vg-text-muted); font-size: 0.85rem;">No document scan uploaded yet.</em>
                    <?php endif; ?>
                </div>

                <?php if (!empty($customer['notes'])): ?>
                <div style="border-top: 1px solid var(--vg-pearl-200); padding-top: 0.75rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--vg-text-muted); display: block; margin-bottom: 0.2rem;">Internal Notes</span>
                    <p style="margin: 0; font-size: 0.85rem; color: #475569; background: #F8FAFC; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px dashed #CBD5E1;">
                        <?= nl2br(e($customer['notes'])) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Booking History Ledger -->
    <div class="vg-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="card-title">Travel Bookings &amp; Reservations Ledger</span>
                <span style="font-size: 0.82rem; color: var(--vg-text-muted); display: block;">Total lifetime reservations: <?= count($bookings) ?></span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($bookings)): ?>
                <div style="text-align: center; padding: 3rem 1.5rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🧳</div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--vg-navy-900); margin-bottom: 0.35rem;">No Bookings Recorded Yet</h4>
                    <p style="color: var(--vg-text-secondary); font-size: 0.85rem; margin-bottom: 1.25rem;">This customer profile does not have any active or past travel bookings.</p>
                    <a href="<?= url('/office/bookings/create?customer_id=' . $customer['id']) ?>" class="btn btn-navy btn-sm">+ Create First Booking</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Tour / Route</th>
                                <th>Dates</th>
                                <th>Guests</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/office/bookings/' . $b['id']) ?>" style="font-weight: 700; color: var(--vg-blue-500); text-decoration: none;">
                                            <code><?= e($b['booking_code']) ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--vg-navy-900);">
                                            <?= !empty($b['tour_title']) ? e($b['tour_title']) : 'Custom Chauffeur Fleet Service' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; color: var(--vg-navy-900);">
                                            <?= format_date($b['start_date']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= (int)$b['passenger_count'] ?>
                                    </td>
                                    <td>
                                        <strong style="color: var(--vg-navy-900);"><?= format_currency((float)$b['total_amount']) ?></strong>
                                    </td>
                                    <td>
                                        <?= render_status_badge($b['status']) ?>
                                    </td>
                                    <td>
                                        <a href="<?= url('/office/bookings/' . $b['id']) ?>" class="btn btn-outline btn-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
