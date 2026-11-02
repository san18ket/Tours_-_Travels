<?php
/**
 * VoyageGo Tours & Travels — List Companies & Installation Profile Overview
 * Matches customers/index.php design patterns (desktop table + mobile card layout)
 */
?>

<div class="bookings-page-view">
    <!-- 1. DESKTOP ONLY: Unified Page Header -->
    <div class="desktop-header-wrapper">
        <?= render_page_header([
            'title'       => __('company.management', [], 'Company Management'),
            'description' => __('company.management_desc', [], 'Active travel company profile, official branding, and business credentials for this installation.'),
            'image'       => 'header-customers.jpg',
            'badge'       => 'Installation Profile',
            'breadcrumbs' => [
                ['label' => __('sidebar.dashboard', [], 'Dashboard'), 'url' => url('/office/dashboard')],
                ['label' => __('company.management', [], 'Company Management'), 'url' => ''],
            ],
            'actions'     => !empty($primaryCompany) 
                ? '<a href="' . url('/office/company/' . $primaryCompany['id'] . '/edit') . '" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Company Profile</span></a>'
                : '<a href="' . url('/office/company/create') . '" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <span>Add Company Profile</span></a>',
        ]) ?>
    </div>

    <!-- 2. DESKTOP ONLY: Compact Search & Filter Bar -->
    <form method="GET" action="<?= url('/office/company') ?>" class="filter-bar-compact" id="companyFilterForm" data-no-ajax="true" style="margin-bottom: 1.5rem;">
        <div class="fbc-search" id="fbcSearchWrapper" style="flex: 1; max-width: 450px;">
            <i class="fa-solid fa-magnifying-glass fbc-search-icon"></i>
            <input type="text" name="search" id="searchInput" placeholder="Search company by name, email, phone, website..." value="<?= e($search ?? '') ?>" autocomplete="off">
            <?php if (!empty($search)): ?>
                <a href="<?= url('/office/company') ?>" class="fbc-search-clear" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>

        <div class="fbc-actions">
            <button type="submit" class="fbc-btn" title="Search"><i class="fa-solid fa-filter"></i> <span><?= e(__('filter.apply', [], 'Search')) ?></span></button>
            <a href="<?= url('/office/company') ?>" class="fbc-btn" title="Refresh"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>

    <!-- 3. MOBILE ONLY: Top App Bar & Search -->
    <div class="mv-bookings-header">
        <div class="mv-top-bar">
            <div class="mv-top-bar-left">
                <a href="<?= url('/office/dashboard') ?>" class="mv-back-btn" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mv-page-title">Company Profile</h2>
            </div>
            <?php if (!empty($primaryCompany)): ?>
                <a href="<?= url('/office/company/' . $primaryCompany['id'] . '/edit') ?>" class="mv-add-btn">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Edit</span>
                </a>
            <?php else: ?>
                <a href="<?= url('/office/company/create') ?>" class="mv-add-btn">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Table Container / Empty State -->
    <?php if (empty($companies)): ?>
        <div class="modern-card" style="padding: 3.5rem 2rem; text-align: center; background: #FFFFFF; border-radius: 14px; border: 1px solid #E2E8F0; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <div style="font-size: 3.5rem; color: #94A3B8; margin-bottom: 1rem;">
                <i class="fa-solid fa-building-circle-arrow-right"></i>
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">No Company Profile Configured</h3>
            <p style="color: #64748B; max-width: 500px; margin: 0 auto 1.5rem auto; font-size: 0.95rem; line-height: 1.5;">
                This installation currently uses environment defaults. Initialize your official company profile to configure your custom logo, contact channels, and dynamic email branding.
            </p>
            <a href="<?= url('/office/company/create') ?>" class="btn btn-primary" style="padding: 0.75rem 1.85rem; font-weight: 700; background: #176B5B; border-color: #176B5B;">
                <i class="fa-solid fa-plus"></i> Initialize Company Profile
            </a>
        </div>
    <?php else: ?>
        <!-- Modern Table for Large Screens (Strict 7-Column Executive Layout) -->
        <div class="modern-table-container">
            <table class="modern-table" id="companiesTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Logo</th>
                        <th style="min-width: 220px;">Company Name &amp; Tagline</th>
                        <th style="min-width: 200px;">Official Email</th>
                        <th style="min-width: 150px;">Phone</th>
                        <th style="min-width: 180px;">Website</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $c): 
                        $logoUrl = !empty($c['logo']) ? storage_url($c['logo']) : null;
                        $initial = strtoupper(mb_substr($c['company_name'] ?? 'C', 0, 1));
                        $isActive = ($c['status'] === 'active');
                    ?>
                    <tr>
                        <!-- 1. Company Logo -->
                        <td class="text-center">
                            <div style="width: 52px; height: 52px; border-radius: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px;">
                                <?php if (!empty($logoUrl)): ?>
                                    <img src="<?= e($logoUrl) ?>" alt="<?= e($c['company_name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; font-size: 1.25rem; font-weight: 800; color: #176B5B;"><?= e($initial) ?></div>
                                <?php else: ?>
                                    <div style="font-size: 1.25rem; font-weight: 800; color: #176B5B;"><?= e($initial) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 2. Company Name & Tagline -->
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <a href="<?= url('/office/company/' . $c['id']) ?>" style="font-size: 0.98rem; font-weight: 700; color: #0F172A; text-decoration: none;">
                                        <?= e($c['company_name']) ?>
                                    </a>
                                    <?php if (!empty($c['is_primary'])): ?>
                                        <span class="badge" style="background: #E0E7FF; color: #4338CA; font-size: 0.72rem; font-weight: 700; padding: 2px 7px; border-radius: 6px;">Primary</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($c['tagline'])): ?>
                                    <span style="font-size: 0.82rem; color: #64748B; margin-top: 2px;"><?= e($c['tagline']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Official Email -->
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <i class="fa-regular fa-envelope" style="color: #64748B; font-size: 0.85rem;"></i>
                                <a href="mailto:<?= e($c['email']) ?>" style="color: #2563EB; font-size: 0.9rem; text-decoration: none; font-weight: 500;">
                                    <?= e($c['email']) ?>
                                </a>
                            </div>
                        </td>

                        <!-- 4. Phone -->
                        <td>
                            <?php if (!empty($c['phone'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <i class="fa-solid fa-phone" style="color: #64748B; font-size: 0.82rem;"></i>
                                    <a href="tel:<?= e($c['phone']) ?>" style="color: #0F172A; font-size: 0.9rem; text-decoration: none; font-weight: 500;">
                                        <?= e($c['phone']) ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span style="color: #94A3B8; font-size: 0.85rem;">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- 5. Website -->
                        <td>
                            <?php if (!empty($c['website'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.45rem;">
                                    <i class="fa-solid fa-globe" style="color: #64748B; font-size: 0.82rem;"></i>
                                    <a href="<?= e($c['website']) ?>" target="_blank" rel="noopener noreferrer" style="color: #2563EB; font-size: 0.88rem; text-decoration: none;">
                                        <?= e(preg_replace('#^https?://#', '', $c['website'])) ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span style="color: #94A3B8; font-size: 0.85rem;">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- 6. Status -->
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="status-badge status-badge-completed" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                    <i class="fa-solid fa-circle-check" style="margin-right: 3px;"></i> Active
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-badge-cancelled" style="background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                                    <i class="fa-solid fa-circle-pause" style="margin-right: 3px;"></i> Inactive
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- 7. Actions -->
                        <td class="text-center">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <!-- View Details -->
                                <a href="<?= url('/office/company/' . $c['id']) ?>" class="btn btn-sm btn-outline" title="View Company Profile" style="padding: 0.35rem 0.65rem; font-size: 0.85rem;">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <!-- Edit Profile -->
                                <a href="<?= url('/office/company/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit Company Profile" style="padding: 0.35rem 0.65rem; font-size: 0.85rem;">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <!-- Toggle Status -->
                                <form action="<?= url('/office/company/' . $c['id'] . '/toggle-status') ?>" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to change the status of this company profile?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $isActive ? 'Deactivate Profile' : 'Activate Profile' ?>" style="padding: 0.35rem 0.65rem; font-size: 0.85rem;">
                                        <i class="fa-solid <?= $isActive ? 'fa-ban' : 'fa-check' ?>"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Layout for Small Screens (<= 768px) -->
        <div class="mobile-cards-view" style="display: none;">
            <?php foreach ($companies as $c): 
                $logoUrl = !empty($c['logo']) ? storage_url($c['logo']) : null;
                $initial = strtoupper(mb_substr($c['company_name'] ?? 'C', 0, 1));
                $isActive = ($c['status'] === 'active');
            ?>
            <div class="booking-mobile-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 1.15rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 0.85rem;">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; flex-shrink: 0;">
                        <?php if (!empty($logoUrl)): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($c['company_name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <?php else: ?>
                            <div style="font-size: 1.25rem; font-weight: 800; color: #176B5B;"><?= e($initial) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e($c['company_name']) ?>
                        </h4>
                        <?php if (!empty($c['tagline'])): ?>
                            <div style="font-size: 0.82rem; color: #64748B; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($c['tagline']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status-badge" style="background: <?= $isActive ? '#ECFDF5' : '#FEF2F2' ?>; color: <?= $isActive ? '#047857' : '#B91C1C' ?>; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 12px;">
                            <?= $isActive ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>

                <div style="border-top: 1px solid #F1F5F9; padding-top: 0.65rem; font-size: 0.88rem; color: #334155;">
                    <div style="margin-bottom: 0.35rem;"><i class="fa-regular fa-envelope" style="width: 18px; color: #64748B;"></i> <?= e($c['email']) ?></div>
                    <?php if (!empty($c['phone'])): ?>
                        <div style="margin-bottom: 0.35rem;"><i class="fa-solid fa-phone" style="width: 18px; color: #64748B;"></i> <?= e($c['phone']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($c['website'])): ?>
                        <div><i class="fa-solid fa-globe" style="width: 18px; color: #64748B;"></i> <?= e($c['website']) ?></div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 0.85rem; border-top: 1px solid #F1F5F9; padding-top: 0.75rem;">
                    <a href="<?= url('/office/company/' . $c['id']) ?>" class="btn btn-sm btn-outline" style="flex: 1; text-align: center;">View Details</a>
                    <a href="<?= url('/office/company/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-primary" style="flex: 1; text-align: center; background: #176B5B; border-color: #176B5B;">Edit Profile</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center;">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= ($currentPage == $p) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/office/company?page=' . $p . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    @media (max-width: 768px) {
        .modern-table-container {
            display: none !important;
        }
        .mobile-cards-view {
            display: block !important;
        }
    }
</style>
