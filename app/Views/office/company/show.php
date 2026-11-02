<?php
/**
 * VoyageGo Tours & Travels — Company Profile Details View
 */
$logoUrl = !empty($company['logo']) ? storage_url($company['logo']) : null;
$initial = strtoupper(mb_substr($company['company_name'] ?? 'C', 0, 1));
$isActive = ($company['status'] === 'active');
?>

<div class="desktop-header-wrapper" style="margin-bottom: 1.5rem;">
    <?= render_page_header([
        'title'       => $company['company_name'],
        'description' => __('company.profile_overview', [], 'Executive company profile and business credentials overview.'),
        'image'       => 'header-customers.jpg',
        'badge'       => 'Company Profile',
        'breadcrumbs' => [
            ['label' => __('sidebar.dashboard', [], 'Dashboard'), 'url' => url('/office/dashboard')],
            ['label' => __('company.management', [], 'Company Management'), 'url' => url('/office/company')],
            ['label' => $company['company_name'], 'url' => ''],
        ],
        'actions'     => '<a href="' . url('/office/company/' . $company['id'] . '/edit') . '" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Profile</span></a>',
    ]) ?>
</div>

<div class="modern-card" style="max-width: 900px; margin: 0 auto; background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 2rem 2.25rem; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid #F1F5F9; padding-bottom: 1.5rem;">
        <div style="width: 80px; height: 80px; border-radius: 14px; background: #F8FAFC; border: 1.5px solid #E2E8F0; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px; flex-shrink: 0;">
            <?php if (!empty($logoUrl)): ?>
                <img src="<?= e($logoUrl) ?>" alt="<?= e($company['company_name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            <?php else: ?>
                <div style="font-size: 2.2rem; font-weight: 800; color: #176B5B;"><?= e($initial) ?></div>
            <?php endif; ?>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0F172A;"><?= e($company['company_name']) ?></h2>
                <span class="badge" style="background: <?= $isActive ? '#ECFDF5' : '#FEF2F2' ?>; color: <?= $isActive ? '#047857' : '#B91C1C' ?>; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                    <?= $isActive ? 'Active Profile' : 'Inactive' ?>
                </span>
            </div>
            <?php if (!empty($company['tagline'])): ?>
                <div style="font-size: 0.95rem; color: #64748B; margin-top: 4px;"><?= e($company['tagline']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Details Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Official Email</div>
            <div style="font-size: 1rem; color: #0F172A; font-weight: 600;">
                <a href="mailto:<?= e($company['email']) ?>" style="color: #2563EB; text-decoration: none;"><?= e($company['email']) ?></a>
            </div>
        </div>

        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Phone Number</div>
            <div style="font-size: 1rem; color: #0F172A; font-weight: 600;">
                <?= !empty($company['phone']) ? e($company['phone']) : '<span style="color: #94A3B8;">—</span>' ?>
            </div>
        </div>

        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Official Website</div>
            <div style="font-size: 1rem; color: #0F172A; font-weight: 600;">
                <?php if (!empty($company['website'])): ?>
                    <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener noreferrer" style="color: #2563EB; text-decoration: none;"><?= e($company['website']) ?></a>
                <?php else: ?>
                    <span style="color: #94A3B8;">—</span>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Installation Mode</div>
            <div style="font-size: 1rem; color: #0F172A; font-weight: 600;">
                Primary Dedicated Installation Profile
            </div>
        </div>
    </div>

    <!-- Address -->
    <div style="margin-bottom: 2rem; border-top: 1px solid #F1F5F9; padding-top: 1.25rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Business Address</div>
        <div style="font-size: 0.95rem; color: #334155; line-height: 1.5;">
            <?= !empty($company['address']) ? nl2br(e($company['address'])) : '<span style="color: #94A3B8;">No physical address configured.</span>' ?>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 0.85rem; border-top: 1px solid #E2E8F0; padding-top: 1.25rem;">
        <a href="<?= url('/office/company') ?>" class="btn btn-secondary">Back to List</a>
        <a href="<?= url('/office/company/' . $company['id'] . '/edit') ?>" class="btn btn-primary" style="background: #176B5B; border-color: #176B5B;">
            <i class="fa-solid fa-pen-to-square"></i> Edit Company Profile
        </a>
    </div>
</div>
