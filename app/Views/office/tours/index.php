<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Tour Packages Catalog</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Manage luxury tour packages, itineraries, inclusions, and fixed rate pricing.</p>
    </div>
    <?php if (auth_role() === 'admin'): ?>
    <div>
        <a href="<?= url('/office/tours/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Create New Tour</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="vg-card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="<?= url('/office/tours') ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%;">
            <div class="filter-input-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" class="form-control" placeholder="Search tour title, destination, city..." value="<?= e($search ?? '') ?>">
            </div>
            <select name="category" class="form-select" style="min-width: 160px; max-width: 190px;">
                <option value="">All Categories</option>
                <option value="heritage" <?= ($selectedCategory ?? '') === 'heritage' ? 'selected' : '' ?>>Heritage</option>
                <option value="hill_station" <?= ($selectedCategory ?? '') === 'hill_station' ? 'selected' : '' ?>>Hill Station</option>
                <option value="pilgrimage" <?= ($selectedCategory ?? '') === 'pilgrimage' ? 'selected' : '' ?>>Pilgrimage</option>
                <option value="wildlife" <?= ($selectedCategory ?? '') === 'wildlife' ? 'selected' : '' ?>>Wildlife Safari</option>
                <option value="beach" <?= ($selectedCategory ?? '') === 'beach' ? 'selected' : '' ?>>Beach & Coastal</option>
            </select>
            <select name="status" class="form-select" style="max-width: 140px;">
                <option value="">All Statuses</option>
                <option value="published" <?= ($selectedStatus ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($selectedStatus ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= ($selectedStatus ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <select name="per_page" class="form-select" style="max-width: 120px;" onchange="this.form.submit()">
                <option value="10" <?= ($perPage ?? 10) == 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="25" <?= ($perPage ?? 10) == 25 ? 'selected' : '' ?>>25 / page</option>
                <option value="50" <?= ($perPage ?? 10) == 50 ? 'selected' : '' ?>>50 / page</option>
            </select>
            <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($selectedCategory) || !empty($selectedStatus)): ?>
                <a href="<?= url('/office/tours') ?>" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($tours)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="empty-state-title">No Tour Packages Found</div>
                <p class="empty-state-desc">Create your first curated luxury itinerary to offer standard packages to guests.</p>
                <?php if (auth_role() === 'admin'): ?>
                    <a href="<?= url('/office/tours/create') ?>" class="btn btn-primary btn-sm">Create Tour Package</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Data Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tour Package</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Itinerary Route</th>
                            <th>Base Fare</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tours as $t): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= e($t['title']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--vg-text-muted);">Slug: <code><?= e($t['slug']) ?></code></div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= e(ucfirst(str_replace('_', ' ', $t['category']))) ?></span>
                                </td>
                                <td><?= (int)$t['duration_days'] ?>D / <?= (int)$t['duration_nights'] ?>N</td>
                                <td>
                                    <div style="font-weight: 500; color: var(--vg-navy-900);"><?= e($t['pickup_city']) ?> &rarr; <?= e($t['destination_city']) ?></div>
                                </td>
                                <td>
                                    <strong style="color:var(--vg-navy-900);"><?= format_currency((float)$t['base_price']) ?></strong>
                                </td>
                                <td>
                                    <?= render_status_badge($t['status'] === 'published' ? 'active' : 'inactive') ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.5rem; justify-content: flex-end;">
                                        <a href="<?= url('/office/bookings/create?tour_id=' . $t['id']) ?>" class="btn btn-primary btn-sm" title="Book this Tour">
                                            Book
                                        </a>
                                        <?php if (auth_role() === 'admin'): ?>
                                            <a href="<?= url('/office/tours/' . $t['id'] . '/edit') ?>" class="btn btn-outline btn-sm">
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Record Cards View -->
            <div class="vg-mobile-cards" style="padding: 1rem;">
                <?php foreach ($tours as $t): ?>
                    <div class="vg-record-card">
                        <div class="vg-card-row-header">
                            <div>
                                <span class="vg-card-id"><?= e($t['title']) ?></span>
                                <div style="font-size: 0.72rem; color: var(--vg-text-muted);"><?= (int)$t['duration_days'] ?> Days / <?= (int)$t['duration_nights'] ?> Nights</div>
                            </div>
                            <div>
                                <?= render_status_badge($t['status'] === 'published' ? 'active' : 'inactive') ?>
                            </div>
                        </div>
                        <div class="vg-card-grid">
                            <div class="vg-card-field">
                                <span class="vg-card-label">Category</span>
                                <span class="vg-card-value"><?= e(ucfirst(str_replace('_', ' ', $t['category']))) ?></span>
                            </div>
                            <div class="vg-card-field">
                                <span class="vg-card-label">Base Fare</span>
                                <span class="vg-card-value" style="color:var(--vg-navy-900);"><?= format_currency((float)$t['base_price']) ?></span>
                            </div>
                            <div class="vg-card-field" style="grid-column: span 2;">
                                <span class="vg-card-label">Route</span>
                                <span class="vg-card-value"><?= e($t['pickup_city']) ?> &rarr; <?= e($t['destination_city']) ?></span>
                            </div>
                        </div>
                        <div class="vg-card-actions">
                            <a href="<?= url('/office/bookings/create?tour_id=' . $t['id']) ?>" class="btn btn-primary btn-sm">Book</a>
                            <?php if (auth_role() === 'admin'): ?>
                                <a href="<?= url('/office/tours/' . $t['id'] . '/edit') ?>" class="btn btn-outline btn-sm">Edit</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Server-Side Pagination Controls -->
            <?php
                $queryParams = [];
                if (!empty($search)) $queryParams['q'] = $search;
                if (!empty($selectedCategory)) $queryParams['category'] = $selectedCategory;
                if (!empty($selectedStatus)) $queryParams['status'] = $selectedStatus;
                echo render_pagination($currentPage ?? 1, $totalPages ?? 1, $totalRecords ?? count($tours), $perPage ?? 10, url('/office/tours'), $queryParams);
            ?>
        <?php endif; ?>
    </div>
</div>
